<?php

namespace App\Controllers\Api;

use App\Models\PisoModel;
use App\Models\MesaModel;

class MesasApi extends BaseApiController
{
    public function get_pisos_mesas()
    {
        $pisoModel = new PisoModel();
        $mesaModel = new MesaModel();

        $pisos = $pisoModel->orderBy('orden', 'ASC')->findAll();

        foreach ($pisos as &$piso) {
            $piso['mesas'] = $mesaModel->where('id_piso', $piso['id'])->findAll();
        }

        return $this->respond($pisos);
    }

    public function unir_mesas()
    {
        $json = $this->request->getJSON();

        if (!isset($json->id_principal) || !isset($json->ids_secundarias) || !is_array($json->ids_secundarias)) {
            return $this->failValidationError('Faltan parámetros');
        }

        $mesaModel    = new MesaModel();
        $idPrincipal  = $json->id_principal;
        $idsSecundarias = $json->ids_secundarias;

        if (!$mesaModel->find($idPrincipal)) {
            return $this->failNotFound('Mesa principal no encontrada');
        }

        foreach ($idsSecundarias as $id) {
            $mesa = $mesaModel->find($id);
            if (!$mesa) {
                return $this->failNotFound("Mesa $id no encontrada");
            }
            if ($mesa['estado'] !== 'libre') {
                return $this->failValidationError("La mesa {$mesa['nombre']} no está libre y no se puede unir.");
            }
        }

        $mesaModel->whereIn('id', $idsSecundarias)->set(['id_padre' => $idPrincipal])->update();

        return $this->respond(['success' => true, 'message' => 'Mesas unidas correctamente']);
    }

    public function separar_mesas()
    {
        $json = $this->request->getJSON();

        if (!isset($json->ids_mesas) || !is_array($json->ids_mesas)) {
            return $this->failValidationError('Faltan parámetros');
        }

        $mesaModel = new MesaModel();
        $mesaModel->whereIn('id', $json->ids_mesas)->set(['id_padre' => null])->update();

        return $this->respond(['success' => true, 'message' => 'Mesas separadas correctamente']);
    }

    public function update_mesas_positions()
    {
        $json = $this->request->getJSON();

        if (!isset($json->mesas) || !is_array($json->mesas)) {
            return $this->failValidationError('Datos incorrectos');
        }

        $mesaModel = new MesaModel();
        $data      = [];

        foreach ($json->mesas as $m) {
            $m = (object) $m;
            if (isset($m->id, $m->x, $m->y)) {
                $data[] = ['id' => $m->id, 'x' => $m->x, 'y' => $m->y];
            }
        }

        if (!empty($data)) {
            $mesaModel->updateBatch($data, 'id');
        }

        return $this->respond(['success' => true, 'message' => 'Posiciones actualizadas']);
    }
}

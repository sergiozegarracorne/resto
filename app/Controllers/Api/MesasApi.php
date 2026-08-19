<?php

namespace App\Controllers\Api;

use App\Models\PisoModel;
use App\Models\MesaModel;
use App\Models\PedidoOperacionesModel;

class MesasApi extends BaseApiController
{
    public function resumen_activo()
    {
        $mesaModel     = new MesaModel();
        $mesasAbiertas = $mesaModel->where('estado', 'ocupada')->countAllResults();

        $porUsuario = \Config\Database::connect('operaciones')
            ->query("
                SELECT id_usuario, COUNT(*) AS mesas_abiertas
                FROM pedidos
                WHERE estado = 'pendiente'
                GROUP BY id_usuario
            ")
            ->getResultArray();

        return $this->respond([
            'success'        => true,
            'mesas_abiertas' => $mesasAbiertas,
            'por_usuario'    => $porUsuario,
        ]);
    }

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

    public function set_pre_cuenta()
    {
        $json = $this->request->getJSON(true);
        if (empty($json['id_mesa'])) {
            return $this->fail('Falta id_mesa');
        }
        $mesaModel = new MesaModel();
        if (!$mesaModel->find($json['id_mesa'])) {
            return $this->failNotFound('Mesa no encontrada');
        }
        $mesaModel->update($json['id_mesa'], ['estado' => 'pre_cuenta']);
        return $this->respond(['success' => true]);
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

    // ── CRUD Pisos ────────────────────────────────────────────────────────────

    public function piso_save()
    {
        if (!$this->puedeAdmin()) {
            return $this->failForbidden('Sin permiso');
        }
        $json   = $this->request->getJSON(true);
        $nombre = trim($json['nombre'] ?? '');
        if ($nombre === '') {
            return $this->fail('El nombre del piso es requerido');
        }

        $pisoModel = new PisoModel();

        if (!empty($json['id'])) {
            $pisoModel->update($json['id'], ['nombre' => $nombre]);
            return $this->respond(['success' => true, 'message' => 'Piso actualizado']);
        }

        $maxOrden = (int) ($pisoModel->selectMax('orden')->first()['orden'] ?? 0);
        $id = $pisoModel->insert(['nombre' => $nombre, 'orden' => $maxOrden + 1]);
        return $this->respond(['success' => true, 'id' => $id, 'message' => 'Piso creado']);
    }

    public function piso_delete()
    {
        if (!$this->puedeAdmin()) {
            return $this->failForbidden('Sin permiso');
        }
        $json = $this->request->getJSON(true);
        if (empty($json['id'])) {
            return $this->fail('Falta id');
        }

        $mesaModel = new MesaModel();
        if ($mesaModel->where('id_piso', $json['id'])->countAllResults() > 0) {
            return $this->fail('No se puede eliminar: el piso tiene mesas. Elimínalas primero.');
        }

        (new PisoModel())->delete($json['id']);
        return $this->respond(['success' => true]);
    }

    // ── CRUD Mesas ────────────────────────────────────────────────────────────

    public function mesa_save()
    {
        if (!$this->puedeAdmin()) {
            return $this->failForbidden('Sin permiso');
        }
        $json    = $this->request->getJSON(true);
        $nombre  = trim($json['nombre'] ?? '');
        $id_piso = $json['id_piso'] ?? null;

        if ($nombre === '' || !$id_piso) {
            return $this->fail('Nombre e id_piso son requeridos');
        }

        $mesaModel = new MesaModel();

        if (!empty($json['id'])) {
            $mesaModel->update($json['id'], ['nombre' => $nombre, 'id_piso' => $id_piso]);
            return $this->respond(['success' => true, 'message' => 'Mesa actualizada']);
        }

        $id = $mesaModel->insert([
            'nombre'  => $nombre,
            'id_piso' => $id_piso,
            'estado'  => 'libre',
            'x'       => 20,
            'y'       => 20,
        ]);
        return $this->respond(['success' => true, 'id' => $id, 'message' => 'Mesa creada']);
    }

    public function mesa_delete()
    {
        if (!$this->puedeAdmin()) {
            return $this->failForbidden('Sin permiso');
        }
        $json = $this->request->getJSON(true);
        if (empty($json['id'])) {
            return $this->fail('Falta id');
        }

        $mesaModel = new MesaModel();
        $mesa = $mesaModel->find($json['id']);
        if (!$mesa) {
            return $this->failNotFound('Mesa no encontrada');
        }
        if ($mesa['estado'] !== 'libre') {
            return $this->fail('No se puede eliminar: la mesa tiene un pedido activo.');
        }

        $mesaModel->delete($json['id']);
        return $this->respond(['success' => true]);
    }
}

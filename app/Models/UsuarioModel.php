<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['nombre', 'clave', 'rol', 'imagen', 'estado'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    /**
     * Devuelve usuarios activos con solo los campos seguros (sin clave).
     */
    public function getActiveUsers()
    {
        return $this->select('id_usuario, nombre, rol, imagen')
            ->where('estado', 1)
            ->findAll();
    }
}

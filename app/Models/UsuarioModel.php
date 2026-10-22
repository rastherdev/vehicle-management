<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table           = 'usuarios';
    protected $primaryKey      = 'id';
    protected $returnType      = 'array';
    protected $useTimestamps   = false;
    protected $allowedFields   = ['email', 'password', 'nombre', 'apellido', 'activo'];
    protected $validationRules = [
        'email'    => 'required|valid_email|is_unique[usuarios.email]',
        'password' => 'required',
        'nombre'   => 'required|max_length[100]',
        'apellido' => 'required|max_length[100]',
        'activo'   => 'permit_empty|in_list[0,1]',
    ];
}

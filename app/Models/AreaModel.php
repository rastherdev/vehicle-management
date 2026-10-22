<?php

namespace App\Models;

use CodeIgniter\Model;

class AreaModel extends Model
{
    protected $table            = 'areas';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = ['nombre', 'descripcion', 'estado'];
    protected $validationRules  = [
        'nombre'      => 'required|max_length[100]',
        'descripcion' => 'permit_empty|max_length[500]',
        'estado'      => 'required|in_list[0,1]',
    ];
}

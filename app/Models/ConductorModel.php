<?php

namespace App\Models;

use CodeIgniter\Model;

class ConductorModel extends Model
{
    protected $table            = 'conductores';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = ['nombres', 'apellidos', 'telefono', 'estado', 'area_id'];
    protected $validationRules  = [
        'nombres'   => 'required|max_length[100]',
        'apellidos' => 'required|max_length[100]',
        'telefono'  => 'required|max_length[20]',
        'estado'    => 'required|in_list[0,1]',
        'area_id'   => 'permit_empty|is_not_unique[areas.id]',
    ];

    public function conArea(): self
    {
        return $this->select('conductores.*, areas.nombre AS area')
            ->join('areas', 'areas.id = conductores.area_id', 'left');
    }
}

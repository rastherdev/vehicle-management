<?php

namespace App\Models;

use CodeIgniter\Model;

class GastoModel extends Model
{
    protected $table           = 'registro_gasto';
    protected $primaryKey      = 'id';
    protected $returnType      = 'array';
    protected $useTimestamps   = false;
    protected $allowedFields   = [
        'vehiculo_id', 'categoria', 'concepto', 'fecha', 'monto', 'notas', 'foto_comprobante',
    ];
}

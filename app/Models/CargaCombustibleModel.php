<?php

namespace App\Models;

use CodeIgniter\Model;

class CargaCombustibleModel extends Model
{
    protected $table           = 'cargas_combustible';
    protected $primaryKey      = 'id';
    protected $returnType      = 'array';
    protected $useTimestamps   = false;
    protected $allowedFields   = [
        'vehiculo_id', 'fecha', 'odometro', 'litros', 'monto', 'tanque_lleno',
        'notas', 'foto_bomba_gasolineria', 'foto_odometro', 'foto_ticket_pago',
        'fecha_registro',
    ];
}

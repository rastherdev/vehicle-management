<?php

namespace App\Models;

use CodeIgniter\Model;

class MantenimientoModel extends Model
{
    protected $table           = 'mantenimiento_programado';
    protected $primaryKey      = 'id';
    protected $returnType      = 'array';
    protected $useTimestamps   = false;
    protected $allowedFields   = [
        'vehiculo_id', 'tipo', 'descripcion', 'prioridad',
        'fecha_programada', 'odometro_programado', 'intervalo_km', 'intervalo_dias',
        'estado', 'fecha_completado', 'odometro_completado', 'registro_gasto_id',
        'costo_estimado', 'notas',
    ];

    public function conVehiculo(): self
    {
        return $this->select('mantenimiento_programado.*, vehiculos.placa, vehiculos.marca, vehiculos.modelo')
            ->join('vehiculos', 'vehiculos.id = mantenimiento_programado.vehiculo_id', 'left');
    }

    /**
     * Marca como vencidos los pendientes cuya fecha programada ya pasó.
     */
    public function marcarVencidos(): void
    {
        $this->db->table('mantenimiento_programado')
            ->set('estado', 'vencido')
            ->where('estado', 'pendiente')
            ->where('fecha_programada IS NOT NULL')
            ->where('fecha_programada <', date('Y-m-d'))
            ->update();
    }
}

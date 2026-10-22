<?php

namespace App\Models;

use CodeIgniter\Model;

class VehiculoModel extends Model
{
    protected $table           = 'vehiculos';
    protected $primaryKey      = 'id';
    protected $returnType      = 'array';
    protected $useTimestamps   = false;
    protected $allowedFields   = [
        'placa', 'marca', 'modelo', 'color', 'estado', 'area_id',
        'tipo_combustible', 'tipo_vehiculo', 'anio', 'cilindraje', 'rendimiento',
        'rendimiento_hora',
    ];

    public function conArea(): self
    {
        return $this->select('vehiculos.*, areas.nombre AS area')
            ->join('areas', 'areas.id = vehiculos.area_id', 'left');
    }

    /**
     * @return int[]
     */
    public function conductorIds(int $vehiculoId): array
    {
        $rows = $this->db->table('conductor_vehiculo')
            ->select('conductor_id')
            ->where('vehiculo_id', $vehiculoId)
            ->get()->getResultArray();

        return array_map('intval', array_column($rows, 'conductor_id'));
    }

    public function conductores(int $vehiculoId): array
    {
        return $this->db->table('conductor_vehiculo')
            ->select('conductores.id, conductores.nombres, conductores.apellidos')
            ->join('conductores', 'conductores.id = conductor_vehiculo.conductor_id')
            ->where('vehiculo_id', $vehiculoId)
            ->get()->getResultArray();
    }

    /**
     * @param int[] $ids
     */
    public function setConductores(int $vehiculoId, array $ids): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        $this->db->table('conductor_vehiculo')->where('vehiculo_id', $vehiculoId)->delete();

        foreach ($ids as $conductorId) {
            $this->db->table('conductor_vehiculo')->insert([
                'vehiculo_id'  => $vehiculoId,
                'conductor_id' => $conductorId,
            ]);
        }
    }
}

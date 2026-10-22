<?php

namespace App\Controllers\Api;

use App\Models\AreaModel;
use App\Models\CargaCombustibleModel;
use App\Models\GastoModel;
use App\Models\MantenimientoModel;
use App\Models\VehiculoModel;
use CodeIgniter\HTTP\ResponseInterface;

class Reportes extends CrudBase
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function opciones(): ResponseInterface
    {
        return $this->response->setJSON([
            'areas'     => model(AreaModel::class)->where('estado', 1)->orderBy('nombre', 'ASC')->findAll(),
            'vehiculos' => model(VehiculoModel::class)->conArea()
                ->where('vehiculos.estado', 1)
                ->orderBy('vehiculos.placa', 'ASC')->findAll(),
        ]);
    }

    /**
     * Gastos con filtros: fechas, vehículo y categoría.
     */
    public function gastos(): ResponseInterface
    {
        $model = $this->db->table('registro_gasto')
            ->select('registro_gasto.*, vehiculos.placa, vehiculos.marca, vehiculos.modelo')
            ->join('vehiculos', 'vehiculos.id = registro_gasto.vehiculo_id', 'left');

        $desde = (string) ($this->request->getGet('desde') ?? '');
        $hasta = (string) ($this->request->getGet('hasta') ?? '');
        $vehiculoId = (int) ($this->request->getGet('vehiculo_id') ?? 0);
        $categoria = (string) ($this->request->getGet('categoria') ?? '');

        if ($desde !== '') {
            $model = $model->where('registro_gasto.fecha >=', $desde);
        }
        if ($hasta !== '') {
            $model = $model->where('registro_gasto.fecha <=', $hasta);
        }
        if ($vehiculoId > 0) {
            $model = $model->where('registro_gasto.vehiculo_id', $vehiculoId);
        }
        if ($categoria !== '') {
            $model = $model->where('registro_gasto.categoria', $categoria);
        }

        $model = $model->orderBy('registro_gasto.fecha', 'DESC')->orderBy('registro_gasto.id', 'DESC');

        return $this->response->setJSON(['data' => $model->get()->getResultArray()]);
    }

    /**
     * Cargas de combustible con filtros: fechas, área y vehículos (varios).
     */
    public function cargas(): ResponseInterface
    {
        $model = $this->db->table('cargas_combustible')
            ->select('cargas_combustible.*, vehiculos.placa, vehiculos.marca, vehiculos.modelo, areas.nombre AS area')
            ->join('vehiculos', 'vehiculos.id = cargas_combustible.vehiculo_id', 'left')
            ->join('areas', 'areas.id = vehiculos.area_id', 'left');

        $desde = (string) ($this->request->getGet('desde') ?? '');
        $hasta = (string) ($this->request->getGet('hasta') ?? '');
        $areaId = (int) ($this->request->getGet('area_id') ?? 0);
        $vehiculoIds = $this->request->getGet('vehiculo_ids') ?? [];

        if (! is_array($vehiculoIds)) {
            $vehiculoIds = $vehiculoIds === '' ? [] : explode(',', (string) $vehiculoIds);
        }
        $vehiculoIds = array_values(array_unique(array_map('intval', $vehiculoIds)));
        $vehiculoIds = array_filter($vehiculoIds, static fn ($v) => $v > 0);

        if ($desde !== '') {
            $model = $model->where('cargas_combustible.fecha >=', $desde);
        }
        if ($hasta !== '') {
            $model = $model->where('cargas_combustible.fecha <=', $hasta);
        }
        if ($areaId > 0) {
            $model = $model->where('vehiculos.area_id', $areaId);
        }
        if ($vehiculoIds !== []) {
            $model = $model->whereIn('cargas_combustible.vehiculo_id', $vehiculoIds);
        }

        $model = $model->orderBy('cargas_combustible.fecha', 'ASC')->orderBy('cargas_combustible.id', 'ASC');

        return $this->response->setJSON(['data' => $model->get()->getResultArray()]);
    }

    /**
     * Impacto de combustible por vehículo: rendimiento configurado vs real,
     * alertas y semáforo.
     */
    public function impacto(): ResponseInterface
    {
        return $this->response->setJSON(\App\Services\ImpactoCombustible::analizarFlota());
    }

    /**
     * Mantenimientos completados con filtros para gráficas.
     */
    public function mantenimientos(): ResponseInterface
    {
        $model = $this->db->table('mantenimiento_programado')
            ->select('mantenimiento_programado.*, vehiculos.placa, registro_gasto.monto AS gasto_monto')
            ->join('vehiculos', 'vehiculos.id = mantenimiento_programado.vehiculo_id', 'left')
            ->join('registro_gasto', 'registro_gasto.id = mantenimiento_programado.registro_gasto_id', 'left')
            ->where('mantenimiento_programado.estado', 'completado');

        $desde = (string) ($this->request->getGet('desde') ?? '');
        $hasta = (string) ($this->request->getGet('hasta') ?? '');
        $vehiculoId = (int) ($this->request->getGet('vehiculo_id') ?? 0);

        if ($desde !== '') {
            $model = $model->where('mantenimiento_programado.fecha_completado >=', $desde);
        }
        if ($hasta !== '') {
            $model = $model->where('mantenimiento_programado.fecha_completado <=', $hasta);
        }
        if ($vehiculoId > 0) {
            $model = $model->where('mantenimiento_programado.vehiculo_id', $vehiculoId);
        }

        $model = $model->orderBy('mantenimiento_programado.fecha_completado', 'ASC');

        return $this->response->setJSON(['data' => $model->get()->getResultArray()]);
    }

}
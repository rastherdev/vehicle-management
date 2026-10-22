<?php

namespace App\Controllers\Api;

use App\Models\ConductorModel;
use App\Models\VehiculoModel;
use CodeIgniter\HTTP\ResponseInterface;

class Vehiculos extends CrudBase
{
    private VehiculoModel $model;

    public function __construct()
    {
        $this->model = model(VehiculoModel::class);
    }

    public function index(): ResponseInterface
    {
        $model = $this->model->conArea()->orderBy('vehiculos.placa', 'ASC');

        if ($this->request->getGet('activos') === '1') {
            $model = $model->where('vehiculos.estado', 1);
        }

        return $this->response->setJSON([
            'data' => $model->findAll(),
        ]);
    }

    public function show(int $id): ResponseInterface
    {
        $vehiculo = $this->model->conArea()->find($id);

        if ($vehiculo === null) {
            return $this->noEncontrado('Vehículo no encontrado.');
        }

        $vehiculo['conductor_ids'] = $this->model->conductorIds($id);
        $vehiculo['conductores']   = $this->model->conductores($id);

        return $this->response->setJSON($vehiculo);
    }

    public function create(): ResponseInterface
    {
        $data = $this->normalizar($this->payload());

        if (! $this->valida($data, $this->reglas(null))) {
            return $this->falloValidacion();
        }

        if (! $this->validarConductores($data)) {
            return $this->falloValidacion();
        }

        $id = $this->model->insert($data);
        $this->model->setConductores($id, $data['conductor_ids'] ?? []);

        return $this->response->setStatusCode(201)->setJSON($this->show($id)->getJSON());
    }

    public function update(int $id): ResponseInterface
    {
        if ($this->model->find($id) === null) {
            return $this->noEncontrado('Vehículo no encontrado.');
        }

        $data = $this->normalizar($this->payload());

        if (! $this->valida($data, $this->reglas($id))) {
            return $this->falloValidacion();
        }

        if (! $this->validarConductores($data)) {
            return $this->falloValidacion();
        }

        $this->model->update($id, $data);
        $this->model->setConductores($id, $data['conductor_ids'] ?? []);

        return $this->response->setJSON($this->show($id)->getJSON());
    }

    public function delete(int $id): ResponseInterface
    {
        if ($this->model->find($id) === null) {
            return $this->noEncontrado('Vehículo no encontrado.');
        }

        $this->model->delete($id);

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function normalizar(array $data): array
    {
        if (array_key_exists('estado', $data)) {
            $data['estado'] = (int) $data['estado'];
        }
        foreach (['area_id', 'anio'] as $campo) {
            if (array_key_exists($campo, $data) && in_array($data[$campo], ['', null], true)) {
                $data[$campo] = null;
            } elseif (array_key_exists($campo, $data)) {
                $data[$campo] = (int) $data[$campo];
            }
        }
        if (array_key_exists('rendimiento', $data) && in_array($data['rendimiento'], ['', null], true)) {
            $data['rendimiento'] = null;
        }
        if (array_key_exists('rendimiento_hora', $data) && in_array($data['rendimiento_hora'], ['', null], true)) {
            $data['rendimiento_hora'] = null;
        }
        if (array_key_exists('conductor_ids', $data) && ! is_array($data['conductor_ids'])) {
            $data['conductor_ids'] = $data['conductor_ids'] === null ? [] : [$data['conductor_ids']];
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validarConductores(array $data): bool
    {
        $ids = array_values(array_unique(array_map('intval', $data['conductor_ids'] ?? [])));

        $data['conductor_ids'] = $ids;

        if ($ids === []) {
            return true;
        }

        $existentes = model(ConductorModel::class)->whereIn('id', $ids)->countAllResults();

        if ((int) $existentes !== count($ids)) {
            $this->validator->setError('conductor_ids', 'Uno o más conductores seleccionados no existen.');

            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(?int $id): array
    {
        $unico = $id === null
            ? 'is_unique[vehiculos.placa]'
            : 'is_unique[vehiculos.placa,id,' . $id . ']';

        return [
            'placa'            => ['label' => 'Placa', 'rules' => 'required|max_length[20]|' . $unico],
            'marca'            => ['label' => 'Marca', 'rules' => 'required|max_length[60]'],
            'modelo'           => ['label' => 'Modelo', 'rules' => 'required|max_length[60]'],
            'color'            => ['label' => 'Color', 'rules' => 'required|max_length[60]'],
            'estado'           => ['label' => 'Estado', 'rules' => 'required|in_list[0,1]'],
            'area_id'          => ['label' => 'Área', 'rules' => 'permit_empty|is_not_unique[areas.id]'],
            'tipo_combustible' => ['label' => 'Tipo de combustible', 'rules' => 'required|in_list[gasolina,diesel]'],
            'tipo_vehiculo'    => ['label' => 'Tipo de vehículo', 'rules' => 'required|in_list[automovil,camioneta,camion,motocicleta,maquinaria]'],
            'anio'             => ['label' => 'Año', 'rules' => 'permit_empty|integer|greater_than_equal_to[1900]|less_than_equal_to[2100]'],
            'cilindraje'       => ['label' => 'Cilindraje', 'rules' => 'permit_empty|in_list[v4,v6,v8,v10,v12,otro]'],
            'rendimiento'      => ['label' => 'Rendimiento (km/L)', 'rules' => 'permit_empty|decimal|greater_than[0]'],
            'rendimiento_hora' => ['label' => 'Rendimiento por hora', 'rules' => 'permit_empty|decimal|greater_than[0]'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function mensajesEspecificos(string $campo): array
    {
        if ($campo === 'rendimiento_hora') {
            return [
                'decimal'      => 'El rendimiento por hora debe ser un número decimal.',
                'greater_than' => 'El rendimiento por hora debe ser mayor que 0.',
            ];
        }

        return [];
    }
}

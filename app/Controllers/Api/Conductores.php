<?php

namespace App\Controllers\Api;

use App\Models\ConductorModel;
use CodeIgniter\HTTP\ResponseInterface;

class Conductores extends CrudBase
{
    private ConductorModel $model;

    public function __construct()
    {
        $this->model = model(ConductorModel::class);
    }

    public function index(): ResponseInterface
    {
        return $this->response->setJSON([
            'data' => $this->model->conArea()->orderBy('conductores.nombres', 'ASC')->findAll(),
        ]);
    }

    public function show(int $id): ResponseInterface
    {
        $conductor = $this->model->conArea()->find($id);

        if ($conductor === null) {
            return $this->noEncontrado('Conductor no encontrado.');
        }

        return $this->response->setJSON($conductor);
    }

    public function create(): ResponseInterface
    {
        $data = $this->normalizar($this->payload());

        if (! $this->valida($data, $this->reglas())) {
            return $this->falloValidacion();
        }

        $id = $this->model->insert($data);

        return $this->response->setStatusCode(201)->setJSON($this->model->conArea()->find($id));
    }

    public function update(int $id): ResponseInterface
    {
        if ($this->model->find($id) === null) {
            return $this->noEncontrado('Conductor no encontrado.');
        }

        $data = $this->normalizar($this->payload());

        if (! $this->valida($data, $this->reglas())) {
            return $this->falloValidacion();
        }

        $this->model->update($id, $data);

        return $this->response->setJSON($this->model->conArea()->find($id));
    }

    public function delete(int $id): ResponseInterface
    {
        if ($this->model->find($id) === null) {
            return $this->noEncontrado('Conductor no encontrado.');
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
        if (array_key_exists('area_id', $data) && in_array($data['area_id'], ['', null], true)) {
            $data['area_id'] = null;
        } elseif (array_key_exists('area_id', $data)) {
            $data['area_id'] = (int) $data['area_id'];
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(): array
    {
        return [
            'nombres'   => ['label' => 'Nombres', 'rules' => 'required|max_length[100]'],
            'apellidos' => ['label' => 'Apellidos', 'rules' => 'required|max_length[100]'],
            'telefono'  => ['label' => 'Teléfono', 'rules' => 'required|max_length[20]'],
            'estado'    => ['label' => 'Estado', 'rules' => 'required|in_list[0,1]'],
            'area_id'   => ['label' => 'Área', 'rules' => 'permit_empty|is_not_unique[areas.id]'],
        ];
    }
}

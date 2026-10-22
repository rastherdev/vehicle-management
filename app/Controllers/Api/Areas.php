<?php

namespace App\Controllers\Api;

use App\Models\AreaModel;
use CodeIgniter\HTTP\ResponseInterface;

class Areas extends CrudBase
{
    private AreaModel $model;

    public function __construct()
    {
        $this->model = model(AreaModel::class);
    }

    public function index(): ResponseInterface
    {
        return $this->response->setJSON([
            'data' => $this->model->orderBy('nombre', 'ASC')->findAll(),
        ]);
    }

    public function show(int $id): ResponseInterface
    {
        $area = $this->model->find($id);

        if ($area === null) {
            return $this->noEncontrado('Área no encontrada.');
        }

        return $this->response->setJSON($area);
    }

    public function create(): ResponseInterface
    {
        $data = $this->normalizar($this->payload());

        if (! $this->valida($data, $this->reglas())) {
            return $this->falloValidacion();
        }

        $id = $this->model->insert($data);

        return $this->response->setStatusCode(201)->setJSON($this->model->find($id));
    }

    public function update(int $id): ResponseInterface
    {
        if ($this->model->find($id) === null) {
            return $this->noEncontrado('Área no encontrada.');
        }

        $data = $this->normalizar($this->payload());

        if (! $this->valida($data, $this->reglas())) {
            return $this->falloValidacion();
        }

        $this->model->update($id, $data);

        return $this->response->setJSON($this->model->find($id));
    }

    public function delete(int $id): ResponseInterface
    {
        if ($this->model->find($id) === null) {
            return $this->noEncontrado('Área no encontrada.');
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
        if (array_key_exists('descripcion', $data) && $data['descripcion'] === '') {
            $data['descripcion'] = null;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(): array
    {
        return [
            'nombre'      => ['label' => 'Nombre', 'rules' => 'required|max_length[100]'],
            'descripcion' => ['label' => 'Descripción', 'rules' => 'permit_empty|max_length[500]'],
            'estado'      => ['label' => 'Estado', 'rules' => 'required|in_list[0,1]'],
        ];
    }
}

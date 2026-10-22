<?php

namespace App\Controllers\Api;

use App\Models\GastoModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Gastos extends CrudBase
{
    private const CATEGORIAS = ['arreglo', 'refacciones', 'mantenimiento', 'seguro', 'tenencia', 'lavado', 'otro'];

    private const EXTENSIONES = ['jpg', 'jpeg', 'png', 'webp'];

    private GastoModel $model;

    public function __construct()
    {
        $this->model = model(GastoModel::class);
    }

    public function index(): ResponseInterface
    {
        $model = $this->model->orderBy('fecha', 'DESC')->orderBy('id', 'DESC');

        $vehiculoId = (int) ($this->request->getGet('vehiculo_id') ?? 0);
        if ($vehiculoId > 0) {
            $model = $model->where('vehiculo_id', $vehiculoId);
        }

        return $this->response->setJSON(['data' => $model->findAll()]);
    }

    public function store(): ResponseInterface
    {
        $data = $this->normalizar($this->payload());

        if (! $this->valida($data, $this->reglas())) {
            return $this->falloValidacion();
        }

        $foto = $this->guardarFoto();

        if (! empty($this->validator->getErrors())) {
            return $this->falloValidacion();
        }

        $data['foto_comprobante'] = $foto;

        $id = $this->model->insert($data);

        return $this->response->setStatusCode(201)->setJSON($this->model->find($id));
    }

    public function foto(string $archivo): ResponseInterface
    {
        $ruta = WRITEPATH . 'uploads/gastos/' . basename($archivo);

        if (! is_file($ruta)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($ruta) ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setBody(file_get_contents($ruta));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function normalizar(array $data): array
    {
        if (array_key_exists('vehiculo_id', $data) && $data['vehiculo_id'] !== '') {
            $data['vehiculo_id'] = (int) $data['vehiculo_id'];
        }
        if (array_key_exists('notas', $data) && $data['notas'] === '') {
            $data['notas'] = null;
        }

        return $data;
    }

    private function guardarFoto(): ?string
    {
        $archivo = $this->request->getFile('foto_comprobante');

        if ($archivo === null || ! $archivo->isValid()) {
            return null;
        }

        $extension = strtolower($archivo->getExtension());

        if (! in_array($extension, self::EXTENSIONES, true)) {
            $this->validator->setError('foto_comprobante', 'El comprobante debe ser JPG, PNG o WebP.');

            return null;
        }

        if ($archivo->getSize() > 5 * 1024 * 1024) {
            $this->validator->setError('foto_comprobante', 'El comprobante no puede superar 5 MB.');

            return null;
        }

        $nombre = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $archivo->move(WRITEPATH . 'uploads/gastos', $nombre);

        return $nombre;
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(): array
    {
        $categorias = implode(',', self::CATEGORIAS);

        return [
            'vehiculo_id' => ['label' => 'Vehículo', 'rules' => 'required|is_not_unique[vehiculos.id]'],
            'categoria'   => ['label' => 'Categoría', 'rules' => 'required|in_list[' . $categorias . ']'],
            'concepto'    => ['label' => 'Concepto', 'rules' => 'required|max_length[100]'],
            'fecha'       => ['label' => 'Fecha', 'rules' => 'required|valid_date'],
            'monto'       => ['label' => 'Monto', 'rules' => 'required|decimal|greater_than[0]'],
            'notas'       => ['label' => 'Notas', 'rules' => 'permit_empty|max_length[500]'],
        ];
    }
}

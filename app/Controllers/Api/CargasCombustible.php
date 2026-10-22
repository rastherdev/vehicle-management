<?php

namespace App\Controllers\Api;

use App\Models\CargaCombustibleModel;
use App\Models\VehiculoModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class CargasCombustible extends CrudBase
{
    private const CAMPOS_FOTO = [
        'foto_bomba_gasolineria',
        'foto_odometro',
        'foto_ticket_pago',
    ];

    private const EXTENSIONES = ['jpg', 'jpeg', 'png', 'webp'];

    private CargaCombustibleModel $model;

    public function __construct()
    {
        $this->model = model(CargaCombustibleModel::class);
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
        $data = $this->normalizar($this->request->getPost());

        if (! $this->valida($data, $this->reglas())) {
            return $this->falloValidacion();
        }

        $data = array_merge($data, $this->guardarFotos());

        if (! empty($this->validator->getErrors())) {
            return $this->falloValidacion();
        }

        $data['fecha_registro'] = date('Y-m-d H:i:s');

        $id = $this->model->insert($data);

        return $this->response->setStatusCode(201)->setJSON($this->model->find($id));
    }

    public function foto(string $archivo): ResponseInterface
    {
        $ruta = WRITEPATH . 'uploads/combustible/' . basename($archivo);

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
        if (array_key_exists('tanque_lleno', $data)) {
            $data['tanque_lleno'] = (int) $data['tanque_lleno'];
        } else {
            $data['tanque_lleno'] = 0;
        }
        if (array_key_exists('vehiculo_id', $data) && $data['vehiculo_id'] !== '') {
            $data['vehiculo_id'] = (int) $data['vehiculo_id'];
        }
        if (array_key_exists('odometro', $data) && $data['odometro'] !== '') {
            $data['odometro'] = (int) $data['odometro'];
        }
        if (array_key_exists('notas', $data) && $data['notas'] === '') {
            $data['notas'] = null;
        }

        return $data;
    }

    /**
     * Guarda las fotos subidas y devuelve los nombres de archivo.
     *
     * @return array<string, string|null>
     */
    private function guardarFotos(): array
    {
        $resultado = [];
        $directorio = WRITEPATH . 'uploads/combustible';

        foreach (self::CAMPOS_FOTO as $campo) {
            $archivo = $this->request->getFile($campo);

            if ($archivo === null || ! $archivo->isValid()) {
                $resultado[$campo] = null;
                continue;
            }

            $extension = strtolower($archivo->getExtension());

            if (! in_array($extension, self::EXTENSIONES, true)) {
                $this->validator->setError($campo, 'La foto debe ser JPG, PNG o WebP.');
                $resultado[$campo] = null;
                continue;
            }

            if ($archivo->getSize() > 5 * 1024 * 1024) {
                $this->validator->setError($campo, 'La foto no puede superar 5 MB.');
                $resultado[$campo] = null;
                continue;
            }

            $nombre = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
            $archivo->move($directorio, $nombre);
            $resultado[$campo] = $nombre;
        }

        return $resultado;
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(): array
    {
        return [
            'vehiculo_id' => ['label' => 'Vehículo', 'rules' => 'required|is_not_unique[vehiculos.id]'],
            'fecha'       => ['label' => 'Fecha', 'rules' => 'required|valid_date'],
            'odometro'    => ['label' => 'Odómetro', 'rules' => 'required|integer|greater_than_equal_to[0]'],
            'litros'      => ['label' => 'Litros', 'rules' => 'required|decimal|greater_than[0]'],
            'monto'       => ['label' => 'Monto', 'rules' => 'required|decimal|greater_than_equal_to[0]'],
            'tanque_lleno'=> ['label' => 'Tanque lleno', 'rules' => 'permit_empty|in_list[0,1]'],
            'notas'       => ['label' => 'Notas', 'rules' => 'permit_empty|max_length[500]'],
        ];
    }
}

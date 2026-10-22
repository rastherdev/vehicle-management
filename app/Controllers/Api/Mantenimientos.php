<?php

namespace App\Controllers\Api;

use App\Models\GastoModel;
use App\Models\MantenimientoModel;
use App\Models\VehiculoModel;
use CodeIgniter\HTTP\ResponseInterface;

class Mantenimientos extends CrudBase
{
    private const TIPOS = ['aceite', 'llantas', 'frenos', 'filtros', 'revision_general', 'otro'];
    private const PRIORIDADES = ['baja', 'media', 'alta', 'critica'];
    private const ESTADOS = ['pendiente', 'en_proceso', 'completado', 'cancelado', 'vencido'];

    private MantenimientoModel $model;

    public function __construct()
    {
        $this->model = model(MantenimientoModel::class);
    }

    public function index(): ResponseInterface
    {
        $this->model->marcarVencidos();

        $model = $this->model->conVehiculo();

        $vehiculoId = (int) ($this->request->getGet('vehiculo_id') ?? 0);
        if ($vehiculoId > 0) {
            $model = $model->where('mantenimiento_programado.vehiculo_id', $vehiculoId);
        }

        $estado = (string) ($this->request->getGet('estado') ?? '');
        if ($estado !== '' && in_array($estado, self::ESTADOS, true)) {
            $model = $model->where('mantenimiento_programado.estado', $estado);
        }

        $model = $model
            ->orderBy("(CASE WHEN mantenimiento_programado.estado IN ('pendiente','vencido','en_proceso') THEN 0 ELSE 1 END)", 'ASC', false)
            ->orderBy('mantenimiento_programado.fecha_programada', 'ASC')
            ->orderBy('mantenimiento_programado.id', 'DESC');

        return $this->response->setJSON(['data' => $model->findAll()]);
    }

    public function show(int $id): ResponseInterface
    {
        $mant = $this->model->conVehiculo()->find($id);

        if ($mant === null) {
            return $this->noEncontrado('Mantenimiento no encontrado.');
        }

        return $this->response->setJSON($mant);
    }

    public function store(): ResponseInterface
    {
        $data = $this->normalizar($this->payload());

        if (! $this->valida($data, $this->reglas())) {
            return $this->falloValidacion();
        }

        if (! $this->tieneDisparador($data)) {
            $this->validator->setError('disparador', 'Indica al menos un disparador: fecha, odómetro o intervalo.');

            return $this->falloValidacion();
        }

        $data['estado'] = 'pendiente';

        $id = $this->model->insert($data);

        return $this->response->setStatusCode(201)->setJSON($this->model->conVehiculo()->find($id));
    }

    public function iniciar(int $id): ResponseInterface
    {
        $mant = $this->model->find($id);

        if ($mant === null) {
            return $this->noEncontrado('Mantenimiento no encontrado.');
        }

        if (! in_array($mant['estado'], ['pendiente', 'vencido'], true)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Solo se puede iniciar un mantenimiento pendiente o vencido.']);
        }

        $this->model->update($id, ['estado' => 'en_proceso']);

        return $this->response->setJSON($this->model->conVehiculo()->find($id));
    }

    public function completar(int $id): ResponseInterface
    {
        $mant = $this->model->find($id);

        if ($mant === null) {
            return $this->noEncontrado('Mantenimiento no encontrado.');
        }

        if ($mant['estado'] === 'completado') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'El mantenimiento ya está completado.']);
        }

        $data = $this->normalizar($this->payload());

        $reglas = [
            'odometro_completado' => ['label' => 'Odómetro al completar', 'rules' => 'permit_empty|integer|greater_than_equal_to[0]'],
            'registrar_gasto'     => ['label' => 'Registrar gasto', 'rules' => 'permit_empty|in_list[0,1]'],
            'monto'               => ['label' => 'Monto del gasto', 'rules' => 'permit_empty|decimal|greater_than[0]'],
            'categoria'           => ['label' => 'Categoría', 'rules' => 'permit_empty|in_list[arreglo,refacciones,mantenimiento,seguro,tenencia,lavado,otro]'],
            'concepto'            => ['label' => 'Concepto', 'rules' => 'permit_empty|max_length[100]'],
        ];

        if (! $this->valida($data, $reglas)) {
            return $this->falloValidacion();
        }

        if (($data['registrar_gasto'] ?? 0) == 1 && empty($data['monto'])) {
            $this->validator->setError('monto', 'Indica el monto del gasto o desmarca la opción.');

            return $this->falloValidacion();
        }

        $gastoId = null;

        if (($data['registrar_gasto'] ?? 0) == 1) {
            $gastoId = model(GastoModel::class)->insert([
                'vehiculo_id' => (int) $mant['vehiculo_id'],
                'categoria'   => ($data['categoria'] ?? '') ?: 'mantenimiento',
                'concepto'    => ($data['concepto'] ?? '') ?: ('Mantenimiento: ' . $mant['tipo']),
                'fecha'       => date('Y-m-d'),
                'monto'       => $data['monto'],
                'notas'       => 'Gasto generado al completar el mantenimiento.',
            ]);
        }

        $this->model->update($id, [
            'estado'               => 'completado',
            'fecha_completado'     => date('Y-m-d'),
            'odometro_completado'  => $data['odometro_completado'] ?: null,
            'registro_gasto_id'    => $gastoId,
        ]);

        return $this->response->setJSON($this->model->conVehiculo()->find($id));
    }

    public function cancelar(int $id): ResponseInterface
    {
        $mant = $this->model->find($id);

        if ($mant === null) {
            return $this->noEncontrado('Mantenimiento no encontrado.');
        }

        if ($mant['estado'] === 'completado') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Un mantenimiento completado no se puede cancelar.']);
        }

        $this->model->update($id, ['estado' => 'cancelado']);

        return $this->response->setJSON($this->model->conVehiculo()->find($id));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function tieneDisparador(array $data): bool
    {
        foreach (['fecha_programada', 'odometro_programado', 'intervalo_km', 'intervalo_dias'] as $campo) {
            if (! empty($data[$campo])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function normalizar(array $data): array
    {
        foreach (['vehiculo_id', 'odometro_programado', 'intervalo_km', 'intervalo_dias', 'odometro_completado', 'registrar_gasto'] as $campo) {
            if (array_key_exists($campo, $data) && in_array($data[$campo], ['', null], true)) {
                $data[$campo] = null;
            } elseif (array_key_exists($campo, $data) && in_array($campo, ['vehiculo_id', 'odometro_programado', 'intervalo_km', 'intervalo_dias', 'odometro_completado', 'registrar_gasto'], true)) {
                $data[$campo] = (int) $data[$campo];
            }
        }
        foreach (['fecha_programada', 'descripcion', 'notas', 'costo_estimado'] as $campo) {
            if (array_key_exists($campo, $data) && in_array($data[$campo], ['', null], true)) {
                $data[$campo] = null;
            }
        }
        if (! array_key_exists('prioridad', $data) || $data['prioridad'] === '') {
            $data['prioridad'] = 'media';
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(): array
    {
        return [
            'vehiculo_id'        => ['label' => 'Vehículo', 'rules' => 'required|is_not_unique[vehiculos.id]'],
            'tipo'               => ['label' => 'Tipo', 'rules' => 'required|in_list[' . implode(',', self::TIPOS) . ']'],
            'descripcion'        => ['label' => 'Descripción', 'rules' => 'permit_empty|max_length[500]'],
            'prioridad'          => ['label' => 'Prioridad', 'rules' => 'permit_empty|in_list[' . implode(',', self::PRIORIDADES) . ']'],
            'fecha_programada'   => ['label' => 'Fecha programada', 'rules' => 'permit_empty|valid_date'],
            'odometro_programado'=> ['label' => 'Odómetro programado', 'rules' => 'permit_empty|integer|greater_than_equal_to[0]'],
            'intervalo_km'       => ['label' => 'Intervalo (km)', 'rules' => 'permit_empty|integer|greater_than[0]'],
            'intervalo_dias'     => ['label' => 'Intervalo (días)', 'rules' => 'permit_empty|integer|greater_than[0]'],
            'costo_estimado'     => ['label' => 'Costo estimado', 'rules' => 'permit_empty|decimal|greater_than_equal_to[0]'],
            'notas'              => ['label' => 'Notas', 'rules' => 'permit_empty|max_length[500]'],
        ];
    }
}

<?php

namespace App\Controllers\Api;

use App\Models\MantenimientoModel;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends CrudBase
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function resumen(): ResponseInterface
    {
        $hoy = date('Y-m-d');
        $inicioMes = date('Y-m-01');

        // ---------- Flota ----------
        $flota = $this->db->table('vehiculos')
            ->select('SUM(CASE WHEN estado = 1 THEN 1 ELSE 0 END) AS activos, SUM(CASE WHEN estado = 0 THEN 1 ELSE 0 END) AS inactivos')
            ->get()->getRowArray();

        // ---------- Dinero del mes ----------
        $cargasMes = $this->db->table('cargas_combustible')
            ->select('COALESCE(SUM(litros), 0) AS litros, COALESCE(SUM(monto), 0) AS monto')
            ->where('fecha >=', $inicioMes)->get()->getRowArray();

        $gastosMes = $this->db->table('registro_gasto')
            ->select('COALESCE(SUM(monto), 0) AS monto')
            ->where('fecha >=', $inicioMes)->get()->getRowArray();

        $precioLitro = ((float) $cargasMes['litros']) > 0
            ? (float) $cargasMes['monto'] / (float) $cargasMes['litros']
            : null;

        // ---------- Mantenimientos ----------
        $mantModel = model(MantenimientoModel::class);
        $mantModel->marcarVencidos();

        $pendientes = $mantModel->where('estado', 'pendiente')->countAllResults();
        $vencidos = $mantModel->where('estado', 'vencido')->countAllResults();
        $proximos = $mantModel->conVehiculo()
            ->whereIn('mantenimiento_programado.estado', ['pendiente'])
            ->where('mantenimiento_programado.fecha_programada >=', $hoy)
            ->where('mantenimiento_programado.fecha_programada <=', date('Y-m-d', strtotime('+7 days')))
            ->orderBy('mantenimiento_programado.fecha_programada', 'ASC')
            ->limit(6)->findAll();

        // ---------- Serie últimos 6 meses ----------
        $serie = [];

        for ($i = 5; $i >= 0; $i--) {
            $inicio = date('Y-m-01', strtotime('-' . $i . ' months'));
            $fin = date('Y-m-t', strtotime('-' . $i . ' months'));

            $combustible = $this->db->table('cargas_combustible')
                ->select('COALESCE(SUM(monto), 0) AS monto')
                ->where('fecha >=', $inicio)->where('fecha <=', $fin)
                ->get()->getRowArray();

            $gastos = $this->db->table('registro_gasto')
                ->select('COALESCE(SUM(monto), 0) AS monto')
                ->where('fecha >=', $inicio)->where('fecha <=', $fin)
                ->get()->getRowArray();

            $serie[] = [
                'mes'         => ucfirst(\IntlDateFormatter::formatObject(
                    \DateTimeImmutable::createFromFormat('!Y-m-d', $inicio),
                    'MMMM',
                    'es_CO'
                )),
                'combustible' => (float) $combustible['monto'],
                'gastos'      => (float) $gastos['monto'],
            ];
        }

        // ---------- Alertas de impacto ----------
        $impacto = \App\Services\ImpactoCombustible::analizarFlota();
        $alertas = [];

        foreach (($impacto['data'] ?? []) as $v) {
            if (in_array($v['semaforo'], ['amarillo', 'rojo'], true)) {
                $alertas[] = $v;
            }
        }

        usort($alertas, static fn ($a, $b) => ($a['semaforo'] === 'rojo' ? 0 : 1) <=> ($b['semaforo'] === 'rojo' ? 0 : 1));
        $alertas = array_slice($alertas, 0, 5);

        return $this->response->setJSON([
            'flota'         => [
                'activos'   => (int) ($flota['activos'] ?? 0),
                'inactivos' => (int) ($flota['inactivos'] ?? 0),
            ],
            'dinero_mes'    => [
                'combustible' => (float) $cargasMes['monto'],
                'gastos'      => (float) $gastosMes['monto'],
                'litros'      => (float) $cargasMes['litros'],
                'precio_litro'=> $precioLitro !== null ? round($precioLitro, 2) : null,
            ],
            'mantenimientos'=> [
                'pendientes' => $pendientes,
                'vencidos'   => $vencidos,
                'proximos'   => $proximos,
            ],
            'serie'         => $serie,
            'alertas'       => $alertas,
        ]);
    }
}

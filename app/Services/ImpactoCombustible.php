<?php

namespace App\Services;

use App\Models\CargaCombustibleModel;
use App\Models\VehiculoModel;

class ImpactoCombustible
{
    /**
     * Analiza todos los vehículos activos: rendimiento configurado vs real,
     * alertas y semáforo.
     *
     * @return array{data: list<array<string, mixed>>, precio_litro_global: float|null}
     */
    public static function analizarFlota(): array
    {
        $vehiculos = model(VehiculoModel::class)->conArea()
            ->where('vehiculos.estado', 1)
            ->orderBy('vehiculos.placa', 'ASC')->findAll();

        $db = db_connect();

        $global = $db->table('cargas_combustible')
            ->select('SUM(monto) AS monto, SUM(litros) AS litros')->get()->getRowArray();
        $precioGlobal = ((float) $global['litros']) > 0 ? (float) $global['monto'] / (float) $global['litros'] : null;

        $data = [];

        foreach ($vehiculos as $v) {
            $data[] = self::analizarVehiculo($v, $precioGlobal);
        }

        return ['data' => $data, 'precio_litro_global' => $precioGlobal];
    }

    /**
     * @param array<string, mixed> $v
     *
     * @return array<string, mixed>
     */
    public static function analizarVehiculo(array $v, ?float $precioGlobal): array
    {
        $cargas = model(CargaCombustibleModel::class)
            ->where('vehiculo_id', (int) $v['id'])
            ->orderBy('fecha', 'ASC')->orderBy('id', 'ASC')->findAll();

        $alertas = [];
        $totalLitros = 0.0;
        $totalMonto = 0.0;
        $nCargas = count($cargas);
        $odometroPrev = null;
        $odometroRegresion = false;

        foreach ($cargas as $c) {
            $totalLitros += (float) $c['litros'];
            $totalMonto += (float) $c['monto'];

            if ($odometroPrev !== null && (int) $c['odometro'] < $odometroPrev) {
                $odometroRegresion = true;
            }
            $odometroPrev = (int) $c['odometro'];
        }

        $precioLitro = $totalLitros > 0 ? $totalMonto / $totalLitros : null;

        $rendimientoReal = null;
        $kmRecorridos = 0.0;

        $llenos = array_values(array_filter($cargas, static fn ($c) => (int) $c['tanque_lleno'] === 1));

        if (count($llenos) >= 2) {
            $distancia = 0.0;
            $litrosPeriodo = 0.0;
            $indiceCarga = 0;
            $total = count($cargas);

            for ($i = 1, $n = count($llenos); $i < $n; $i++) {
                $anterior = $llenos[$i - 1];
                $actual = $llenos[$i];
                $d = (int) $actual['odometro'] - (int) $anterior['odometro'];

                if ($d <= 0) {
                    continue;
                }

                $distancia += $d;
                $litrosPeriodo += (float) $actual['litros'];

                while ($indiceCarga < $total && (int) $cargas[$indiceCarga]['id'] < (int) $actual['id']) {
                    if ((int) $cargas[$indiceCarga]['id'] > (int) $anterior['id'] && (int) $cargas[$indiceCarga]['tanque_lleno'] === 0) {
                        $litrosPeriodo += (float) $cargas[$indiceCarga]['litros'];
                    }
                    $indiceCarga++;
                }
            }

            if ($litrosPeriodo > 0) {
                $rendimientoReal = $distancia / $litrosPeriodo;
                $kmRecorridos = $distancia;
            }
        }

        if ($nCargas === 0) {
            $alertas[] = 'Sin registros de combustible.';
        } elseif (count($llenos) < 2) {
            $alertas[] = 'Datos insuficientes: se necesitan al menos 2 cargas con tanque lleno para calcular rendimiento.';
        }

        if (empty($v['rendimiento'])) {
            $alertas[] = 'Sin rendimiento configurado (km/L) en el vehículo.';
        }

        if ($rendimientoReal !== null && ! empty($v['rendimiento'])) {
            $config = (float) $v['rendimiento'];
            $pct = ($rendimientoReal - $config) / $config;

            if ($pct < -0.25) {
                $alertas[] = 'Rendimiento real muy por debajo del configurado (' . number_format($pct * 100, 0) . '%).';
            } elseif ($pct < -0.10) {
                $alertas[] = 'Rendimiento real por debajo del configurado (' . number_format($pct * 100, 0) . '%).';
            }
        }

        if ($odometroRegresion) {
            $alertas[] = 'Odómetro inconsistente: hay lecturas que retroceden.';
        }

        if ($precioLitro !== null && $precioGlobal !== null && $precioLitro > $precioGlobal * 1.15) {
            $alertas[] = 'Precio por litro elevado vs el promedio global de la flota.';
        }

        if ($precioLitro !== null && $precioGlobal !== null && $precioLitro < $precioGlobal * 0.85) {
            $alertas[] = 'Precio por litro notablemente bajo vs el promedio global (verificar captura).';
        }

        if ($nCargas === 0) {
            $semaforo = 'gris';
        } elseif ($rendimientoReal === null || empty($v['rendimiento'])) {
            $semaforo = count($alertas) > 0 ? 'amarillo' : 'gris';
        } else {
            $config = (float) $v['rendimiento'];
            $pct = ($rendimientoReal - $config) / $config;

            if ($pct < -0.25) {
                $semaforo = 'rojo';
            } elseif ($pct < -0.10 || $odometroRegresion || count($alertas) > 0) {
                $semaforo = 'amarillo';
            } else {
                $semaforo = 'verde';
            }
        }

        return [
            'vehiculo_id'        => (int) $v['id'],
            'placa'              => $v['placa'],
            'marca'              => $v['marca'],
            'modelo'             => $v['modelo'],
            'area'               => $v['area'] ?? null,
            'rendimiento_config' => $v['rendimiento'] !== null ? (float) $v['rendimiento'] : null,
            'rendimiento_real'   => $rendimientoReal !== null ? round($rendimientoReal, 2) : null,
            'total_litros'       => round($totalLitros, 2),
            'total_monto'        => round($totalMonto, 2),
            'precio_litro'       => $precioLitro !== null ? round($precioLitro, 2) : null,
            'km_recorridos'      => round($kmRecorridos, 0),
            'n_cargas'           => $nCargas,
            'alertas'            => $alertas,
            'semaforo'           => $semaforo,
        ];
    }
}

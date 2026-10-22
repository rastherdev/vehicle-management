<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Admin extends BaseController
{
    private const SECTIONS = [
        'inicio'         => ['title' => 'Inicio', 'icon' => 'bi-house-door'],
        'vehiculos'      => ['title' => 'Vehículos', 'icon' => 'bi-car-front'],
        'mantenimientos' => ['title' => 'Mantenimientos', 'icon' => 'bi-tools'],
        'reportes'       => ['title' => 'Reportes y gráficas', 'icon' => 'bi-graph-up'],
        'configuracion'  => ['title' => 'Configuración', 'icon' => 'bi-gear'],
    ];

    public function index(): string
    {
        return $this->render('inicio', 'admin/dashboard');
    }

    public function section(string $name): string|ResponseInterface
    {
        if (! array_key_exists($name, self::SECTIONS)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->render($name, 'admin/placeholder');
    }

    protected function render(string $section, string $view): string
    {
        $nombre   = (string) session()->get('user_nombre');
        $apellido = (string) session()->get('user_apellido');

        $data = [
            'menu'          => self::SECTIONS,
            'activeSection' => $section,
            'sectionTitle'  => self::SECTIONS[$section]['title'],
            'userName'      => trim($nombre . ' ' . $apellido),
            'initials'      => strtoupper(substr($nombre, 0, 1) . substr($apellido, 0, 1)),
        ];

        $data['content'] = view($view, $data);

        return view('layouts/admin', $data);
    }
}

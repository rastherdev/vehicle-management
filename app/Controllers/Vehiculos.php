<?php

namespace App\Controllers;

class Vehiculos extends Admin
{
    public function index(): string
    {
        return $this->render('vehiculos', 'admin/vehiculos');
    }
}

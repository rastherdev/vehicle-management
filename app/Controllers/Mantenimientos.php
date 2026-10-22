<?php

namespace App\Controllers;

class Mantenimientos extends Admin
{
    public function index(): string
    {
        return $this->render('mantenimientos', 'admin/mantenimientos');
    }
}

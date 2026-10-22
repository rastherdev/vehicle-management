<?php

namespace App\Controllers;

class Configuracion extends Admin
{
    public function index(): string
    {
        return $this->render('configuracion', 'admin/configuracion');
    }
}

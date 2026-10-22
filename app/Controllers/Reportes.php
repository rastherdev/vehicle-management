<?php

namespace App\Controllers;

class Reportes extends Admin
{
    public function index(): string
    {
        return $this->render('reportes', 'admin/reportes');
    }
}

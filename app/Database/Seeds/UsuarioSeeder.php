<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('usuarios')->insert([
            'email'    => 'admin@vehiculos.com',
            'password' => password_hash('Admin123!', PASSWORD_DEFAULT),
            'nombre'   => 'Administrador',
            'apellido' => 'Principal',
            'activo'   => 1,
        ]);
    }
}

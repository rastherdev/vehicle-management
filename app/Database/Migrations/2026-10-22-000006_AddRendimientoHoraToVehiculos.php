<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRendimientoHoraToVehiculos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('vehiculos', [
            'rendimiento_hora' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('vehiculos', 'rendimiento_hora');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConductorVehiculoTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'vehiculo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'conductor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
        $this->forge->addPrimaryKey(['vehiculo_id', 'conductor_id']);
        $this->forge->addForeignKey('vehiculo_id', 'vehiculos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('conductor_id', 'conductores', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('conductor_vehiculo', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('conductor_vehiculo', true);
    }
}

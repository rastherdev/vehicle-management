<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCargasCombustibleTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vehiculo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'fecha' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'odometro' => [
                'type' => 'INT',
                'null' => false,
            ],
            'litros' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,2',
                'null'       => false,
            ],
            'monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
            ],
            'tanque_lleno' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'notas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'foto_bomba_gasolineria' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'foto_odometro' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'foto_ticket_pago' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'fecha_registro' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('vehiculo_id', 'vehiculos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cargas_combustible', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('cargas_combustible', true);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRegistroGastoTable extends Migration
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
            'categoria' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
            ],
            'concepto' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'fecha' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
            ],
            'notas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'foto_comprobante' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('vehiculo_id', 'vehiculos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('registro_gasto', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('registro_gasto', true);
    }
}

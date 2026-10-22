<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMantenimientoProgramadoTable extends Migration
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
            'tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'prioridad' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'media',
                'null'       => false,
            ],
            'fecha_programada' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'odometro_programado' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'intervalo_km' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'intervalo_dias' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pendiente',
                'null'       => false,
            ],
            'fecha_completado' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'odometro_completado' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'registro_gasto_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'costo_estimado' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'notas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('vehiculo_id', 'vehiculos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('registro_gasto_id', 'registro_gasto', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('mantenimiento_programado', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('mantenimiento_programado', true);
    }
}

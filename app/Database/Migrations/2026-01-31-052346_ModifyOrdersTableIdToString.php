<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyOrdersTableIdToString extends Migration
{
    public function up()
    {
        // Drop foreign key constraint dari order_items
        if ($this->db->DBDriver == 'Postgre') {
            $this->db->query("ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_order_id_foreign");
        }

        // Modify orders table id to string
        $this->forge->modifyColumn('orders', [
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => 12,
                'null' => false
            ]
        ]);

        // Modify order_items table order_id to string
        $this->forge->modifyColumn('order_items', [
            'order_id' => [
                'type' => 'VARCHAR',
                'constraint' => 12,
                'null' => false
            ]
        ]);

        // Re-add foreign key constraint
        if ($this->db->DBDriver == 'Postgre') {
            $this->db->query("ALTER TABLE order_items ADD CONSTRAINT order_items_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE");
        }
    }

    public function down()
    {
        // Drop foreign key constraint
        if ($this->db->DBDriver == 'Postgre') {
            $this->db->query("ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_order_id_foreign");
        }

        // Revert back to auto increment integer
        $this->forge->modifyColumn('orders', [
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
                'null' => false
            ]
        ]);

        $this->forge->modifyColumn('order_items', [
            'order_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false
            ]
        ]);

        // Re-add foreign key
        if ($this->db->DBDriver == 'Postgre') {
            $this->db->query("ALTER TABLE order_items ADD CONSTRAINT order_items_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE");
        }
    }
}

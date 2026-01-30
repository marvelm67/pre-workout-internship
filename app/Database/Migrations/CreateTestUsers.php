<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTestUsers extends Migration
{
    public function up()
    {
        // This migration is for testing purposes only
        // Insert test admin and user accounts
        
        $data = [
            [
                'username' => 'admin_test',
                'email' => 'admin@test.com',
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'user_test',
                'email' => 'user@test.com',
                'password' => password_hash('user123', PASSWORD_BCRYPT),
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }

    public function down()
    {
        // Remove test users
        $this->db->table('users')->where('email', 'admin@test.com')->delete();
        $this->db->table('users')->where('email', 'user@test.com')->delete();
    }
}

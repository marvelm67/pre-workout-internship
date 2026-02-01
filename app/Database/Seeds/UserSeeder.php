<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;
use App\Libraries\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();
        
        // Data users untuk testing
        $users = [
            [
                'id' => 'A001',
                'username' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ],
            [
                'id' => 'U001',
                'username' => 'user',
                'email' => 'user@user.com',
                'password' => Hash::make('password'),
                'role' => 'user'
            ],
            [
                'id' => 'U002',
                'username' => 'john',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'role' => 'user'
            ]
        ];

        foreach ($users as $user) {
            // Cek apakah user sudah ada
            $existingUser = $userModel->where('email', $user['email'])->first();
            
            if (!$existingUser) {
                $userModel->insert($user);
                echo "User {$user['username']} berhasil dibuat.\n";
            } else {
                echo "User {$user['username']} sudah ada.\n";
            }
        }
    }
}

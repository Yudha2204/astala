<?php

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        $users = new UserModel();

        if ($users->where('email', 'adminastala@mail.com')->first()) {
            return;
        }

        $users->insert([
            'nama' => 'Super Admin',
            'email' => 'adminastala@mail.com',
            'password' => 'Astala1201_',
            'no_hp' => '088123456789',
            'role' => 'admin',
            'sub_user' => 'editor',
            'is_verified' => true,
            'is_active' => true,
            'theme' => 'system',
        ]);
    }
}

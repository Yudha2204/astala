<?php

namespace App\Models;

class UserModel extends AstalaModel
{
    protected $table = 'users';

    protected $allowedFields = [
        'nama',
        'email',
        'password',
        'no_hp',
        'role',
        'sub_user',
        'is_verified',
        'is_active',
        'foto',
        'dashboard_preferences',
        'theme',
    ];

    protected array $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data): array
    {
        if (! isset($data['data']['password']) || $data['data']['password'] === '') {
            return $data;
        }

        $password = $data['data']['password'];

        if (! password_get_info($password)['algo']) {
            $data['data']['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        return $data;
    }

    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password'] ?? '');
    }
}

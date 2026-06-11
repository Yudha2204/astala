<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\UserModel;
use RuntimeException;

class AuthController extends BaseController
{
    public function showLogin()
    {
        return view('auth/login', [
            'title' => 'Login - ASTALA',
        ]);
    }

    public function login()
    {
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        $users = new UserModel();
        $user = $users->where('email', $email)->first();

        if (! $user || ! $users->verifyPassword($user, $password)) {
            return redirect()->to('/auth/login')->with('error', 'Email atau password salah');
        }

        $this->setUserSession($user);
        $this->logActivity('LOGIN', 'User ' . $user['nama'] . ' berhasil login', 'user', (int) $user['id']);

        return redirect()->to('/dashboard')->with('success', 'Selamat datang, ' . $user['nama'] . '!');
    }

    public function showSignup()
    {
        return view('auth/signup', [
            'title' => 'Daftar - ASTALA',
        ]);
    }

    public function signup()
    {
        $payload = [
            'nama' => trim((string) $this->request->getPost('nama')),
            'email' => trim((string) $this->request->getPost('email')),
            'no_hp' => trim((string) $this->request->getPost('no_hp')),
            'password' => (string) $this->request->getPost('password'),
        ];

        $confirmPassword = (string) $this->request->getPost('confirm_password');

        if ($payload['password'] !== $confirmPassword) {
            return redirect()->to('/auth/signup')->with('error', 'Password dan konfirmasi password tidak cocok');
        }

        $users = new UserModel();
        $existingUser = $users->where('email', $payload['email'])->first();

        if ($existingUser && (bool) $existingUser['is_verified']) {
            return redirect()->to('/auth/signup')->with('error', 'Email sudah terdaftar');
        }

        if ($existingUser) {
            $users->update($existingUser['id'], [
                'nama' => $payload['nama'],
                'no_hp' => $payload['no_hp'],
                'password' => $payload['password'],
                'is_verified' => true,
            ]);

            $this->logActivity('REGISTER', 'User ' . $payload['nama'] . ' berhasil mendaftar (update existing)', 'user', (int) $existingUser['id']);

            return redirect()->to('/auth/login')->with('success', 'Akun berhasil diperbarui. Silakan login.');
        }

        $id = $users->insert([
            ...$payload,
            'role' => 'mitra',
            'is_verified' => true,
        ], true);

        $this->logActivity('REGISTER', 'User ' . $payload['nama'] . ' berhasil mendaftar', 'user', (int) $id);

        return redirect()->to('/auth/login')->with('success', 'Akun berhasil dibuat. Silakan login.');
    }

    public function sso()
    {
        try {
            $token = (string) $this->request->getGet('token');

            if ($token === '') {
                return redirect()->to('/auth/login')->with('error', 'Token SSO tidak ditemukan');
            }

            $decoded = $this->verifyJwt($token, env('ASTALA_JWT_SECRET', 'sso_secret_key_2026'));
            $email = $decoded['email'] ?? null;

            if (! $email) {
                throw new RuntimeException('Missing email in SSO token.');
            }

            $users = new UserModel();
            $user = $users->where('email', $email)->first();

            if (! $user) {
                return redirect()->to('/auth/login')->with('error', 'User dari portal tidak ditemukan di sistem ASTALA');
            }

            if (! (bool) $user['is_active']) {
                return redirect()->to('/auth/login')->with('error', 'Akun Anda telah dinonaktifkan di sistem ASTALA');
            }

            $this->setUserSession($user);
            $this->logActivity('LOGIN_SSO', 'User ' . $user['nama'] . ' login via Portal SSO', 'user', (int) $user['id']);

            return redirect()->to('/dashboard')->with('success', 'Selamat datang, ' . $user['nama'] . '!');
        } catch (\Throwable) {
            return redirect()->to('/auth/login')->with('error', 'Sesi login portal telah berakhir atau tidak valid');
        }
    }

    public function logout()
    {
        $user = session('user');

        if ($user) {
            $this->logActivity('LOGOUT', 'User ' . ($user['nama'] ?? '') . ' logout', 'user', (int) ($user['id'] ?? 0));
        }

        session()->destroy();

        return redirect()->to('/auth/login');
    }

    private function setUserSession(array $user): void
    {
        session()->set('user', [
            'id' => $user['id'],
            'nama' => $user['nama'],
            'email' => $user['email'],
            'role' => $user['role'],
            'sub_user' => $user['sub_user'] ?? null,
            'no_hp' => $user['no_hp'] ?? null,
            'foto' => $user['foto'] ?? null,
            'theme' => $user['theme'] ?? 'system',
        ]);
    }

    private function logActivity(string $action, ?string $description = null, ?string $entityType = null, ?int $entityId = null): void
    {
        $user = session('user');

        (new ActivityLogModel())->insert([
            'user_id' => $user['id'] ?? null,
            'action' => $action,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => (string) $this->request->getUserAgent(),
        ]);
    }

    private function verifyJwt(string $token, string $secret): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid JWT format.');
        }

        [$header, $payload, $signature] = $parts;
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $header . '.' . $payload, $secret, true));

        if (! hash_equals($expected, $signature)) {
            throw new RuntimeException('Invalid JWT signature.');
        }

        $decoded = json_decode($this->base64UrlDecode($payload), true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid JWT payload.');
        }

        if (isset($decoded['exp']) && time() >= (int) $decoded['exp']) {
            throw new RuntimeException('Expired JWT.');
        }

        return $decoded;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}

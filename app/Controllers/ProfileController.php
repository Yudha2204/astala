<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\UserModel;

class ProfileController extends BaseController
{
    private const MAX_PHOTO_SIZE = 2_097_152;

    public function index()
    {
        $sessionUser = session('user');
        $user = (new UserModel())->find((int) ($sessionUser['id'] ?? 0));

        if (! $user) {
            return redirect()->to('/dashboard')->with('error', 'User tidak ditemukan');
        }

        return view('profile/index', [
            'title' => 'Profil Saya - ASTALA',
            'pageTitle' => 'Profil Saya',
            'user' => $user,
        ]);
    }

    public function update()
    {
        $sessionUser = session('user');
        $userId = (int) ($sessionUser['id'] ?? 0);
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (! $user) {
            return redirect()->to('/profile')->with('error', 'User tidak ditemukan');
        }

        $payload = [
            'nama' => trim((string) $this->request->getPost('nama')),
            'no_hp' => trim((string) $this->request->getPost('no_hp')),
        ];

        try {
            $photoPath = $this->saveProfilePhoto($user);
            if ($photoPath !== null) {
                $payload['foto'] = $photoPath;
            }
        } catch (\RuntimeException $e) {
            return redirect()->to('/profile')->with('error', $e->getMessage());
        }

        $userModel->update($userId, $payload);

        $freshUser = $userModel->find($userId);
        if ($freshUser) {
            session()->set('user', [
                'id' => $freshUser['id'],
                'nama' => $freshUser['nama'],
                'email' => $freshUser['email'],
                'role' => $freshUser['role'],
                'sub_user' => $freshUser['sub_user'] ?? null,
                'no_hp' => $freshUser['no_hp'] ?? null,
                'foto' => $freshUser['foto'] ?? null,
                'theme' => $freshUser['theme'] ?? 'system',
            ]);
        }

        $this->logActivity('UPDATE_PROFILE', 'User memperbarui profil', 'user', $userId);

        return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui');
    }

    public function password()
    {
        $sessionUser = session('user');
        $userId = (int) ($sessionUser['id'] ?? 0);
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (! $user) {
            return redirect()->to('/profile')->with('error', 'User tidak ditemukan');
        }

        $currentPassword = (string) $this->request->getPost('current_password');
        $newPassword = (string) $this->request->getPost('new_password');
        $confirmPassword = (string) $this->request->getPost('confirm_password');

        if (! $userModel->verifyPassword($user, $currentPassword)) {
            return redirect()->to('/profile')->with('error', 'Password saat ini salah');
        }

        if ($newPassword !== $confirmPassword) {
            return redirect()->to('/profile')->with('error', 'Konfirmasi password baru tidak cocok');
        }

        if (strlen($newPassword) < 6) {
            return redirect()->to('/profile')->with('error', 'Password baru minimal 6 karakter');
        }

        $userModel->update($userId, ['password' => $newPassword]);
        $this->logActivity('CHANGE_PASSWORD', 'User mengubah password', 'user', $userId);

        return redirect()->to('/profile')->with('success', 'Password berhasil diubah');
    }

    public function settings()
    {
        $sessionUser = session('user');
        $user = (new UserModel())->find((int) ($sessionUser['id'] ?? 0)) ?: $sessionUser;

        return view('profile/settings', [
            'title' => 'Pengaturan - ASTALA',
            'user' => $user,
        ]);
    }

    public function updateTheme()
    {
        $theme = (string) $this->request->getPost('theme');
        if (! in_array($theme, ['light', 'dark', 'system'], true)) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Tema tidak valid']);
        }

        $sessionUser = session('user');
        $userId = (int) ($sessionUser['id'] ?? 0);
        (new UserModel())->update($userId, ['theme' => $theme]);

        $sessionUser['theme'] = $theme;
        session()->set('user', $sessionUser);

        return $this->response->setJSON(['success' => true, 'theme' => $theme]);
    }

    private function saveProfilePhoto(array $user): ?string
    {
        $file = $this->request->getFile('foto');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        if (! str_starts_with((string) $file->getMimeType(), 'image/') || $file->getSize() > self::MAX_PHOTO_SIZE) {
            throw new \RuntimeException('Foto profil harus berupa gambar maksimal 2MB');
        }

        $dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'profiles';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $name = 'profile-' . (int) $user['id'] . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . ($file->getClientExtension() ?: 'jpg');
        $file->move($dir, $name);

        if (! empty($user['foto'])) {
            $this->deleteOldPhoto((string) $user['foto']);
        }

        return '/uploads/profiles/' . $name;
    }

    private function deleteOldPhoto(string $path): void
    {
        $relative = ltrim($path, '/\\');
        if (! str_starts_with($relative, 'uploads/profiles/')) {
            $relative = 'uploads/profiles/' . basename($relative);
        }

        $fullPath = realpath(FCPATH . $relative);
        $profileDir = realpath(FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'profiles');

        if ($fullPath && $profileDir && str_starts_with($fullPath, $profileDir) && is_file($fullPath)) {
            @unlink($fullPath);
        }
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
}

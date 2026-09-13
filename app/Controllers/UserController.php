<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\UserModel;

class UserController extends BaseController
{
    private const PER_PAGE = 10;

    public function index()
    {
        $query = [
            'search' => trim((string) $this->request->getGet('search')),
            'role'   => trim((string) $this->request->getGet('role')),
            'status' => trim((string) $this->request->getGet('status')),
        ];
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        $userModel = new UserModel();

        // Base builder for list
        $builder = db_connect()->table('users');
        $this->applyFilters($builder, $query);

        $total = $builder->countAllResults(false);
        $users = $builder
            ->orderBy('created_at', 'DESC')
            ->limit(self::PER_PAGE, ($page - 1) * self::PER_PAGE)
            ->get()
            ->getResultArray();

        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        $stats = [
            'total'    => $userModel->countAllResults(),
            'active'   => (new UserModel())->where('is_active', 1)->countAllResults(),
            'inactive' => (new UserModel())->where('is_active', 0)->countAllResults(),
            'admin'    => (new UserModel())->where('role', 'admin')->countAllResults(),
        ];

        return view('admin/users/index', [
            'title'      => 'Manajemen User - ASTALA',
            'user'       => session('user'),
            'users'      => $users,
            'stats'      => $stats,
            'query'      => $query,
            'pagination' => [
                'page'       => $page,
                'totalPages' => $totalPages,
                'total'      => $total,
                'hasPrev'    => $page > 1,
                'hasNext'    => $page < $totalPages,
            ],
        ]);
    }

    public function showAdd()
    {
        return view('admin/users/form', [
            'title'    => 'Tambah User - ASTALA',
            'user'     => session('user'),
            'mode'     => 'add',
            'editUser' => null,
        ]);
    }

    public function add()
    {
        $nama            = trim((string) $this->request->getPost('nama'));
        $email           = strtolower(trim((string) $this->request->getPost('email')));
        $noHp            = trim((string) $this->request->getPost('no_hp'));
        $password        = (string) $this->request->getPost('password');
        $confirmPassword = (string) $this->request->getPost('confirm_password');
        $role            = (string) $this->request->getPost('role');
        $subUser         = (string) $this->request->getPost('sub_user');
        $isActive        = $this->request->getPost('is_active') ? 1 : 0;
        $isVerified      = $this->request->getPost('is_verified') ? 1 : 0;

        if ($nama === '' || $email === '' || $password === '' || $role === '') {
            return redirect()->back()->withInput()->with('error', 'Semua kolom bertanda * wajib diisi');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Format email tidak valid');
        }

        if (strlen($password) < 6) {
            return redirect()->back()->withInput()->with('error', 'Password minimal 6 karakter');
        }

        if ($password !== $confirmPassword) {
            return redirect()->back()->withInput()->with('error', 'Konfirmasi password tidak cocok');
        }

        $userModel = new UserModel();
        if ($userModel->where('email', $email)->first()) {
            return redirect()->back()->withInput()->with('error', 'Email sudah terdaftar untuk pengguna lain');
        }

        if (! in_array($role, ['admin', 'manager', 'karyawan', 'mitra'], true)) {
            $role = 'karyawan';
        }

        if ($role === 'admin') {
            $subUser = 'editor';
        } elseif ($role === 'mitra') {
            $subUser = 'viewer';
        } elseif (! in_array($subUser, ['editor', 'viewer'], true)) {
            $subUser = 'viewer';
        }

        $id = $userModel->insert([
            'nama'        => $nama,
            'email'       => $email,
            'password'    => $password,
            'no_hp'       => $noHp ?: null,
            'role'        => $role,
            'sub_user'    => $subUser,
            'is_active'   => $isActive,
            'is_verified' => $isVerified,
            'theme'       => 'system',
        ], true);

        $this->logActivity('CREATE_USER', "Menambahkan user baru: {$nama} ({$email}) dengan role {$role}", 'user', (int) $id);

        return redirect()->to('/admin/users')->with('success', 'User berhasil ditambahkan');
    }

    public function showEdit(int $id)
    {
        $targetUser = (new UserModel())->find($id);

        if (! $targetUser) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan');
        }

        return view('admin/users/form', [
            'title'    => 'Edit User - ASTALA',
            'user'     => session('user'),
            'mode'     => 'edit',
            'editUser' => $targetUser,
        ]);
    }

    public function update(int $id)
    {
        $userModel  = new UserModel();
        $targetUser = $userModel->find($id);

        if (! $targetUser) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan');
        }

        $currentAdminId = (int) (session('user.id') ?? 0);
        $nama           = trim((string) $this->request->getPost('nama'));
        $email          = strtolower(trim((string) $this->request->getPost('email')));
        $noHp           = trim((string) $this->request->getPost('no_hp'));
        $password       = (string) $this->request->getPost('password');
        $confirmPassword = (string) $this->request->getPost('confirm_password');
        $role           = (string) $this->request->getPost('role');
        $subUser        = (string) $this->request->getPost('sub_user');
        $isActive       = $this->request->getPost('is_active') ? 1 : 0;
        $isVerified     = $this->request->getPost('is_verified') ? 1 : 0;

        if ($nama === '' || $email === '' || $role === '') {
            return redirect()->back()->withInput()->with('error', 'Nama, Email, dan Role wajib diisi');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Format email tidak valid');
        }

        // Email duplicate check
        $duplicate = $userModel->where('email', $email)->where('id !=', $id)->first();
        if ($duplicate) {
            return redirect()->back()->withInput()->with('error', 'Email sudah digunakan oleh user lain');
        }

        // Password change check
        if ($password !== '') {
            if (strlen($password) < 6) {
                return redirect()->back()->withInput()->with('error', 'Password baru minimal 6 karakter');
            }
            if ($password !== $confirmPassword) {
                return redirect()->back()->withInput()->with('error', 'Konfirmasi password baru tidak cocok');
            }
        }

        // Prevent self-lockout
        if ($id === $currentAdminId) {
            $role     = 'admin';
            $isActive = 1;
        }

        if (! in_array($role, ['admin', 'manager', 'karyawan', 'mitra'], true)) {
            $role = $targetUser['role'];
        }

        if ($role === 'admin') {
            $subUser = 'editor';
        } elseif ($role === 'mitra') {
            $subUser = 'viewer';
        } elseif (! in_array($subUser, ['editor', 'viewer'], true)) {
            $subUser = 'viewer';
        }

        $payload = [
            'nama'        => $nama,
            'email'       => $email,
            'no_hp'       => $noHp ?: null,
            'role'        => $role,
            'sub_user'    => $subUser,
            'is_active'   => $isActive,
            'is_verified' => $isVerified,
        ];

        if ($password !== '') {
            $payload['password'] = $password;
        }

        $userModel->update($id, $payload);

        // If self updated, refresh session
        if ($id === $currentAdminId) {
            $freshUser = $userModel->find($id);
            if ($freshUser) {
                session()->set('user', [
                    'id'       => $freshUser['id'],
                    'nama'     => $freshUser['nama'],
                    'email'    => $freshUser['email'],
                    'role'     => $freshUser['role'],
                    'sub_user' => $freshUser['sub_user'] ?? null,
                    'no_hp'    => $freshUser['no_hp'] ?? null,
                    'foto'     => $freshUser['foto'] ?? null,
                    'theme'    => $freshUser['theme'] ?? 'system',
                ]);
            }
        }

        $this->logActivity('UPDATE_USER', "Mengubah data user: {$nama} ({$email})", 'user', $id);

        return redirect()->to('/admin/users')->with('success', 'User berhasil diperbarui');
    }

    public function toggleActive(int $id)
    {
        $currentAdminId = (int) (session('user.id') ?? 0);
        if ($id === $currentAdminId) {
            return redirect()->to('/admin/users')->with('error', 'Tidak dapat mengubah status akun sendiri');
        }

        $userModel  = new UserModel();
        $targetUser = $userModel->find($id);

        if (! $targetUser) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan');
        }

        $newStatus = $targetUser['is_active'] ? 0 : 1;
        $userModel->update($id, ['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        $this->logActivity('UPDATE_USER', "User {$targetUser['nama']} {$statusText}", 'user', $id);

        return redirect()->to('/admin/users')->with('success', "Akun user {$targetUser['nama']} berhasil {$statusText}");
    }

    public function delete(int $id)
    {
        $currentAdminId = (int) (session('user.id') ?? 0);
        if ($id === $currentAdminId) {
            return redirect()->to('/admin/users')->with('error', 'Tidak dapat menghapus akun Anda sendiri');
        }

        $userModel  = new UserModel();
        $targetUser = $userModel->find($id);

        if (! $targetUser) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan');
        }

        $activeLoans = db_connect()->table('peminjaman')
            ->where('user_id', $id)
            ->where('status_peminjaman', 'aktif')
            ->countAllResults();

        if ($activeLoans > 0) {
            return redirect()->to('/admin/users')->with('error', 'User memiliki peminjaman barang yang masih aktif, tidak dapat dihapus');
        }

        $userModel->delete($id);
        $this->logActivity('DELETE_USER', "Menghapus user: {$targetUser['nama']} ({$targetUser['email']})", 'user', $id);

        return redirect()->to('/admin/users')->with('success', 'User berhasil dihapus');
    }

    private function applyFilters($builder, array $query): void
    {
        if ($query['search'] !== '') {
            $builder->groupStart()
                ->like('nama', $query['search'])
                ->orLike('email', $query['search'])
                ->orLike('no_hp', $query['search'])
                ->groupEnd();
        }

        if ($query['role'] !== '') {
            $builder->where('role', $query['role']);
        }

        if ($query['status'] === 'active') {
            $builder->where('is_active', 1);
        } elseif ($query['status'] === 'inactive') {
            $builder->where('is_active', 0);
        }
    }

    private function logActivity(string $action, ?string $description = null, ?string $entityType = null, ?int $entityId = null): void
    {
        $user = session('user');

        (new ActivityLogModel())->insert([
            'user_id'     => $user['id'] ?? null,
            'action'      => $action,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'ip_address'  => $this->request->getIPAddress(),
            'user_agent'  => (string) $this->request->getUserAgent(),
        ]);
    }
}

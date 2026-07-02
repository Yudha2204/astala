<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\NotifikasiModel;
use App\Models\PeminjamanModel;
use App\Models\PengambilanAsetModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $user = session('user');

        if (! $user) {
            return redirect()->to('/auth/login');
        }

        $notifications = (new NotifikasiModel())
            ->where('user_id', $user['id'])
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();

        $unreadNotifications = (new NotifikasiModel())
            ->where('user_id', $user['id'])
            ->where('is_read', false)
            ->countAllResults();

        $data = [
            'title' => 'Dashboard - ASTALA',
            'user' => $user,
            'notifications' => $notifications,
            'unreadNotifications' => $unreadNotifications,
            'stats' => [],
            'overdueLoans' => $this->getOverdueLoans(),
            'currentLoans' => [],
            'recentPickups' => [],
        ];

        if ($user['role'] === 'admin') {
            $data['dashboardType'] = 'admin';
            $data['stats'] = $this->inventoryStats() + [
                'totalUser' => (new UserModel())->countAllResults(),
                'activePeminjaman' => $this->countWhere(PeminjamanModel::class, ['status_peminjaman' => 'aktif']),
            ];

            return view('dashboard/index', $data);
        }

        if ($user['role'] === 'manager') {
            $data['dashboardType'] = 'manager';
            $data['stats'] = $this->inventoryStats();

            return view('dashboard/index', $data);
        }



        $data['dashboardType'] = 'karyawan';
        $data['stats'] = [
            'totalPeminjaman' => $this->countWhere(PeminjamanModel::class, ['user_id' => $user['id']]),
            'aktivePeminjaman' => $this->countWhere(PeminjamanModel::class, ['user_id' => $user['id'], 'status_peminjaman' => 'aktif']),
            'selesaiPeminjaman' => $this->countWhere(PeminjamanModel::class, ['user_id' => $user['id'], 'status_peminjaman' => 'selesai']),
        ];
        $data['currentLoans'] = $this->getCurrentLoans((int) $user['id']);

        return view('dashboard/index', $data);
    }

    private function inventoryStats(): array
    {
        return [
            'totalBarang' => (new BarangModel())->countAllResults(),
            'barangTersedia' => $this->countWhere(BarangModel::class, ['status_ketersediaan' => 'tersedia']),
            'barangDipinjam' => $this->countWhere(BarangModel::class, ['status_ketersediaan' => 'dipinjam']),
            'barangBaik' => $this->countWhere(BarangModel::class, ['status_kondisi' => 'baik']),
            'barangRusak' => $this->countWhere(BarangModel::class, ['status_kondisi' => 'rusak']),
        ];
    }



    private function countWhere(string $modelClass, array $where): int
    {
        return (new $modelClass())->where($where)->countAllResults();
    }

    private function getOverdueLoans(): array
    {
        return db_connect()->table('peminjaman p')
            ->select('p.*, u.nama AS user_nama, u.email AS user_email, b.nama_barang, b.nomor_seri')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('barang b', 'b.id = p.barang_id', 'left')
            ->where('p.status_peminjaman', 'aktif')
            ->where('p.tanggal_kembali_rencana <', date('Y-m-d H:i:s'))
            ->orderBy('p.tanggal_kembali_rencana', 'ASC')
            ->limit(10)
            ->get()
            ->getResultArray();
    }

    private function getCurrentLoans(int $userId): array
    {
        return db_connect()->table('peminjaman p')
            ->select('p.*, b.nama_barang, b.nomor_seri')
            ->join('barang b', 'b.id = p.barang_id', 'left')
            ->where('p.user_id', $userId)
            ->where('p.status_peminjaman', 'aktif')
            ->orderBy('p.tanggal_kembali_rencana', 'ASC')
            ->get()
            ->getResultArray();
    }


}

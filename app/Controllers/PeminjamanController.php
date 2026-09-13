<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\BarangModel;
use App\Models\FotoBarangModel;
use App\Models\FotoPeminjamanModel;
use App\Models\PeminjamanModel;
use CodeIgniter\Files\File;
use DateTimeImmutable;
use Throwable;

class PeminjamanController extends BaseController
{
    private const HISTORY_PER_PAGE = 10;
    private const MAX_PHOTO_SIZE = 5_242_880;

    public function items()
    {
        $search = trim((string) $this->request->getGet('search'));
        $status = trim((string) $this->request->getGet('status'));

        $model = new BarangModel();
        if ($search !== '') {
            $model->groupStart()
                ->like('nama_barang', $search)
                ->orLike('nomor_seri', $search)
                ->groupEnd();
        }

        if ($status !== '') {
            $model->where('status_ketersediaan', $status);
        }

        $barangs = $model
            ->orderBy('status_ketersediaan', 'ASC')
            ->orderBy('nama_barang', 'ASC')
            ->findAll();

        $photos = $this->barangPhotos(array_column($barangs, 'id'));
        $tersedia = [];
        $tidakTersedia = [];

        foreach ($barangs as $barang) {
            $barang['fotos'] = $photos[$barang['id']] ?? [];

            if ($barang['status_ketersediaan'] === 'tersedia' && $barang['status_kondisi'] === 'baik') {
                $tersedia[] = $barang;
            } elseif ($barang['status_ketersediaan'] === 'dipinjam' || $barang['status_kondisi'] === 'rusak') {
                $tidakTersedia[] = $barang;
            }
        }

        return view('peminjaman/items', [
            'title' => 'Pinjam Barang - ASTALA',
            'user' => session('user'),
            'tersedia' => $tersedia,
            'tidakTersedia' => $tidakTersedia,
            'query' => ['search' => $search, 'status' => $status],
        ]);
    }

    public function form()
    {
        $user = session('user');
        $peminjamanId = (int) ($this->request->getGet('peminjaman_id') ?? 0);
        $barangId = (int) ($this->request->getGet('barang_id') ?? 0);
        $peminjaman = null;
        $barang = null;

        if ($peminjamanId > 0) {
            $peminjaman = (new PeminjamanModel())->find($peminjamanId);

            if (! $peminjaman || (int) $peminjaman['user_id'] !== (int) ($user['id'] ?? 0)) {
                return redirect()->to('/peminjaman/items')->with('error', 'Peminjaman tidak ditemukan');
            }

            $barang = (new BarangModel())->find((int) $peminjaman['barang_id']);
        } elseif ($barangId > 0) {
            $barang = (new BarangModel())->find($barangId);
        }

        if (! $barang) {
            return redirect()->to('/peminjaman/items')->with('error', 'Barang tidak ditemukan');
        }

        $barang['fotos'] = $this->barangPhotos([$barang['id']])[$barang['id']] ?? [];

        return view('peminjaman/form', [
            'title' => 'Form Peminjaman - ASTALA',
            'user' => $user,
            'peminjaman' => $peminjaman,
            'barang' => $barang,
        ]);
    }

    public function borrow()
    {
        $validation = $this->validateBorrowDates();
        if ($validation !== true) {
            return redirect()->back()->withInput()->with('error', $validation);
        }

        $user = session('user');
        $peminjamanId = (int) ($this->request->getPost('peminjaman_id') ?? 0);
        $barangId = (int) ($this->request->getPost('barang_id') ?? 0);
        $barcodeDetected = trim((string) $this->request->getPost('barcode_detected')) ?: null;

        $db = db_connect();
        $db->transStart();

        try {
            $peminjamanModel = new PeminjamanModel();
            $barangModel = new BarangModel();
            $peminjaman = null;

            if ($peminjamanId > 0) {
                $peminjaman = $peminjamanModel->find($peminjamanId);
                if (! $peminjaman || (int) $peminjaman['user_id'] !== (int) ($user['id'] ?? 0)) {
                    $db->transRollback();
                    return redirect()->to('/peminjaman/items')->with('error', 'Peminjaman tidak ditemukan');
                }

                $barang = $barangModel->find((int) $peminjaman['barang_id']);
            } else {
                $barang = $barangModel->find($barangId);
            }

            if (! $barang) {
                $db->transRollback();
                return redirect()->to('/peminjaman/items')->with('error', 'Barang tidak ditemukan');
            }

            if ($barcodeDetected && $barcodeDetected !== $barang['nomor_seri']) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Barcode tidak cocok dengan barang yang dipilih');
            }

            if ($barang['status_ketersediaan'] !== 'tersedia' && $peminjamanId === 0) {
                $db->transRollback();
                return redirect()->to('/peminjaman/items')->with('error', 'Barang sedang tidak tersedia');
            }

            $payload = [
                'user_id' => $user['id'] ?? null,
                'barang_id' => $barang['id'],
                'lokasi_peminjaman' => trim((string) $this->request->getPost('lokasi_peminjaman')),
                'keperluan' => trim((string) $this->request->getPost('keperluan')) ?: null,
                'tanggal_pinjam' => $this->toSqlDate((string) $this->request->getPost('tanggal_pinjam')),
                'tanggal_kembali_rencana' => $this->toSqlDate((string) $this->request->getPost('tanggal_kembali_rencana')),
                'status_peminjaman' => 'aktif',
            ];

            if ($peminjamanId > 0) {
                $peminjamanModel->update($peminjamanId, $payload);
                $loanId = $peminjamanId;
            } else {
                $loanId = (int) $peminjamanModel->insert($payload, true);
            }

            $barangModel->update((int) $barang['id'], ['status_ketersediaan' => 'dipinjam']);
            $this->savePhotos($loanId, 'pinjam', $barcodeDetected);
            $this->logActivity('PINJAM_BARANG', 'Meminjam barang: ' . $barang['nama_barang'], 'peminjaman', $loanId);

            $db->transComplete();

            return redirect()->to('/peminjaman/current')->with('success', 'Barang berhasil dipinjam');
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Borrow error: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/peminjaman/items')->with('error', 'Terjadi kesalahan saat meminjam barang');
        }
    }

    public function current()
    {
        $user = session('user');
        $loans = $this->loanRows(['p.user_id' => $user['id'] ?? 0, 'p.status_peminjaman' => 'aktif'], null, 0, 'p.tanggal_kembali_rencana ASC');
        $now = new DateTimeImmutable();

        foreach ($loans as &$loan) {
            $loan['isOverdue'] = $this->isPast($loan['tanggal_kembali_rencana'], $now);
        }

        return view('peminjaman/current', [
            'title' => 'Barang Dipinjam - ASTALA',
            'user' => $user,
            'loans' => $loans,
        ]);
    }

    public function returnForm(int $id)
    {
        $user = session('user');
        $peminjaman = $this->loanDetail($id);

        if (! $peminjaman || (int) $peminjaman['user_id'] !== (int) ($user['id'] ?? 0)) {
            return redirect()->to('/peminjaman/current')->with('error', 'Peminjaman tidak ditemukan');
        }

        if ($peminjaman['status_peminjaman'] !== 'aktif') {
            return redirect()->to('/peminjaman/current')->with('error', 'Peminjaman sudah selesai');
        }

        return view('peminjaman/return', [
            'title' => 'Pengembalian Barang - ASTALA',
            'user' => $user,
            'peminjaman' => $peminjaman,
        ]);
    }

    public function returnItem(int $id)
    {
        $user = session('user');
        $barcodeDetected = trim((string) $this->request->getPost('barcode_detected')) ?: null;
        $peminjaman = $this->loanDetail($id);

        if (! $peminjaman || (int) $peminjaman['user_id'] !== (int) ($user['id'] ?? 0)) {
            return redirect()->to('/peminjaman/current')->with('error', 'Peminjaman tidak ditemukan');
        }

        if ($barcodeDetected && $barcodeDetected !== $peminjaman['barang']['nomor_seri']) {
            return redirect()->to('/peminjaman/return/' . $id)->with('error', 'Barcode tidak cocok dengan barang yang dipinjam');
        }

        $now = new DateTimeImmutable();
        $isLate = $this->isPast($peminjaman['tanggal_kembali_rencana'], $now);
        $db = db_connect();
        $db->transStart();

        try {
            (new PeminjamanModel())->update($id, [
                'tanggal_kembali_aktual' => $now->format('Y-m-d H:i:s'),
                'status_peminjaman' => 'selesai',
                'is_late' => $isLate ? 1 : 0,
            ]);

            (new BarangModel())->update((int) $peminjaman['barang_id'], ['status_ketersediaan' => 'tersedia']);
            $this->savePhotos($id, 'kembali', $barcodeDetected);
            $this->logActivity('KEMBALI_BARANG', 'Mengembalikan barang: ' . $peminjaman['barang']['nama_barang'] . ($isLate ? ' (TERLAMBAT)' : ''), 'peminjaman', $id);

            $db->transComplete();

            return redirect()->to('/peminjaman/history')->with('success', 'Barang berhasil dikembalikan' . ($isLate ? '. Catatan: pengembalian terlambat.' : ''));
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Return error: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/peminjaman/current')->with('error', 'Terjadi kesalahan saat mengembalikan barang');
        }
    }

    public function history()
    {
        $user = session('user');
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $where = ['p.user_id' => $user['id'] ?? 0];
        $total = $this->loanCount($where);
        $loans = $this->loanRows($where, self::HISTORY_PER_PAGE, ($page - 1) * self::HISTORY_PER_PAGE, 'p.created_at DESC');

        return view('peminjaman/history', [
            'title' => 'Riwayat Peminjaman - ASTALA',
            'user' => $user,
            'loans' => $loans,
            'pagination' => $this->pagination($page, $total, self::HISTORY_PER_PAGE),
        ]);
    }

    public function detail(int $id)
    {
        $user = session('user');
        $peminjaman = $this->loanDetail($id);

        if (! $peminjaman) {
            return redirect()->back()->with('error', 'Peminjaman tidak ditemukan');
        }

        $role = $user['role'] ?? '';
        if (! in_array($role, ['admin', 'manager'], true) && (int) $peminjaman['user_id'] !== (int) ($user['id'] ?? 0)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses');
        }

        return view('peminjaman/detail', [
            'title' => 'Detail Peminjaman - ASTALA',
            'user' => $user,
            'peminjaman' => $peminjaman,
        ]);
    }

    public function allLoans()
    {
        $query = [
            'status' => trim((string) $this->request->getGet('status')),
            'is_late' => trim((string) $this->request->getGet('is_late')),
            'search' => trim((string) $this->request->getGet('search')),
            'page' => max(1, (int) ($this->request->getGet('page') ?? 1)),
        ];

        $builder = $this->loanBaseBuilder();
        $this->applyAdminLoanFilters($builder, $query);
        $total = $builder->countAllResults(false);
        $loans = $builder
            ->orderBy('p.created_at', 'DESC')
            ->limit(self::HISTORY_PER_PAGE, ($query['page'] - 1) * self::HISTORY_PER_PAGE)
            ->get()
            ->getResultArray();

        $this->hydrateLoans($loans);

        return view('admin/loans', [
            'title' => 'Semua Peminjaman - ASTALA',
            'user' => session('user'),
            'loans' => $loans,
            'query' => $query,
            'pagination' => $this->pagination($query['page'], $total, self::HISTORY_PER_PAGE),
        ]);
    }

    private function loanBaseBuilder()
    {
        return db_connect()->table('peminjaman p')
            ->select('p.*, b.nama_barang, b.nomor_seri, b.kategori, b.lokasi_penyimpanan, b.wajib_qr, u.nama AS user_nama, u.email AS user_email, u.role AS user_role')
            ->join('barang b', 'b.id = p.barang_id', 'left')
            ->join('users u', 'u.id = p.user_id', 'left');
    }

    private function loanRows(array $where, ?int $limit, int $offset, string $order): array
    {
        $builder = $this->loanBaseBuilder()->where($where);
        $this->applyOrder($builder, $order);

        if ($limit !== null) {
            $builder->limit($limit, $offset);
        }

        $loans = $builder->get()->getResultArray();
        $this->hydrateLoans($loans);

        return $loans;
    }

    private function loanCount(array $where): int
    {
        return (int) $this->loanBaseBuilder()->where($where)->countAllResults();
    }

    private function loanDetail(int $id): ?array
    {
        $loan = $this->loanBaseBuilder()->where('p.id', $id)->get()->getRowArray();
        if (! $loan) {
            return null;
        }

        $loans = [$loan];
        $this->hydrateLoans($loans);

        return $loans[0];
    }

    private function hydrateLoans(array &$loans): void
    {
        if (! $loans) {
            return;
        }

        $barangPhotos = $this->barangPhotos(array_column($loans, 'barang_id'));
        $loanPhotos = $this->loanPhotos(array_column($loans, 'id'));

        foreach ($loans as &$loan) {
            $loan['barang'] = [
                'id' => $loan['barang_id'],
                'nama_barang' => $loan['nama_barang'] ?? 'Barang',
                'nomor_seri' => $loan['nomor_seri'] ?? '-',
                'kategori' => $loan['kategori'] ?? null,
                'lokasi_penyimpanan' => $loan['lokasi_penyimpanan'] ?? null,
                'wajib_qr' => (bool) ($loan['wajib_qr'] ?? false),
                'fotos' => $barangPhotos[$loan['barang_id']] ?? [],
            ];
            $loan['user'] = [
                'id' => $loan['user_id'],
                'nama' => $loan['user_nama'] ?? 'User',
                'email' => $loan['user_email'] ?? '-',
                'role' => $loan['user_role'] ?? '-',
            ];
            $loan['fotos'] = $loanPhotos[$loan['id']] ?? [];
            unset($loan['nama_barang'], $loan['nomor_seri'], $loan['kategori'], $loan['lokasi_penyimpanan'], $loan['wajib_qr'], $loan['user_nama'], $loan['user_email'], $loan['user_role']);
        }
    }

    private function applyAdminLoanFilters($builder, array $query): void
    {
        if ($query['status'] !== '') {
            $builder->where('p.status_peminjaman', $query['status']);
        }

        if ($query['is_late'] === 'true') {
            $builder->where('p.is_late', 1);
        } elseif ($query['is_late'] === 'false') {
            $builder->where('p.is_late', 0);
        }

        if ($query['search'] !== '') {
            $builder->groupStart()
                ->like('u.nama', $query['search'])
                ->orLike('b.nama_barang', $query['search'])
                ->orLike('b.nomor_seri', $query['search'])
                ->groupEnd();
        }
    }

    private function applyOrder($builder, string $order): void
    {
        [$field, $direction] = array_pad(preg_split('/\s+/', trim($order), 2), 2, 'ASC');
        $builder->orderBy($field, strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC');
    }

    private function barangPhotos(array $ids): array
    {
        $ids = array_values(array_filter(array_unique(array_map('intval', $ids))));
        if (! $ids) {
            return [];
        }

        $photos = (new FotoBarangModel())->whereIn('barang_id', $ids)->orderBy('urutan', 'ASC')->findAll();
        $grouped = [];
        foreach ($photos as $photo) {
            $grouped[$photo['barang_id']][] = $photo;
        }

        return $grouped;
    }

    private function loanPhotos(array $ids): array
    {
        $ids = array_values(array_filter(array_unique(array_map('intval', $ids))));
        if (! $ids) {
            return [];
        }

        $photos = (new FotoPeminjamanModel())->whereIn('peminjaman_id', $ids)->orderBy('created_at', 'ASC')->findAll();
        $grouped = [];
        foreach ($photos as $photo) {
            $grouped[$photo['peminjaman_id']][] = $photo;
        }

        return $grouped;
    }

    private function validateBorrowDates()
    {
        $pinjam = $this->parseInputDate((string) $this->request->getPost('tanggal_pinjam'));
        $kembali = $this->parseInputDate((string) $this->request->getPost('tanggal_kembali_rencana'));
        $now = new DateTimeImmutable('-1 minute');

        if (! $pinjam) {
            return 'Tanggal pinjam tidak valid';
        }
        if (! $kembali) {
            return 'Tanggal kembali tidak valid';
        }
        if ($pinjam < $now || $kembali < $now) {
            return 'Tanggal tidak boleh di masa lalu';
        }
        if ($kembali <= $pinjam) {
            return 'Tanggal kembali harus setelah tanggal pinjam';
        }
        if (! $this->isOfficeHours($pinjam)) {
            return 'Tanggal pinjam harus pada jam kerja (Senin-Jumat, 08:00-17:00 WIB)';
        }
        if (! $this->isOfficeHours($kembali)) {
            return 'Tanggal kembali harus pada jam kerja (Senin-Jumat, 08:00-17:00 WIB)';
        }

        return true;
    }

    private function parseInputDate(string $value): ?DateTimeImmutable
    {
        if (trim($value) === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value)
            ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);

        return $date ?: null;
    }

    private function toSqlDate(string $value): string
    {
        return ($this->parseInputDate($value) ?? new DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    private function isOfficeHours(DateTimeImmutable $date): bool
    {
        $day = (int) $date->format('N');
        $minutes = ((int) $date->format('G')) * 60 + (int) $date->format('i');

        return $day >= 1 && $day <= 5 && $minutes >= 480 && $minutes <= 1020;
    }

    private function isPast(?string $value, DateTimeImmutable $reference): bool
    {
        if (! $value) {
            return false;
        }

        try {
            return new DateTimeImmutable($value) < $reference;
        } catch (Throwable) {
            return false;
        }
    }

    private function savePhotos(int $loanId, string $tipe, ?string $barcodeDetected): void
    {
        foreach ($this->uploadedPhotos() as $file) {
            if (! $file->isValid() || $file->hasMoved()) {
                continue;
            }

            if (! str_starts_with((string) $file->getMimeType(), 'image/') || $file->getSize() > self::MAX_PHOTO_SIZE) {
                continue;
            }

            $dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'peminjaman';
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $extension = $file instanceof File ? $file->getExtension() : $file->getClientExtension();
            $name = 'peminjaman-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . ($extension ?: 'jpg');
            $file->move($dir, $name);

            (new FotoPeminjamanModel())->insert([
                'peminjaman_id' => $loanId,
                'foto_path' => '/uploads/peminjaman/' . $name,
                'tipe' => $tipe,
                'barcode_detected' => $barcodeDetected,
            ]);
        }
    }

    private function uploadedPhotos(): array
    {
        $files = $this->request->getFiles()['fotos'] ?? [];
        if (! is_array($files)) {
            return [$files];
        }

        $flat = [];
        array_walk_recursive($files, static function ($file) use (&$flat): void {
            if (is_object($file)) {
                $flat[] = $file;
            }
        });

        return $flat;
    }

    private function pagination(int $page, int $total, int $perPage): array
    {
        $totalPages = max(1, (int) ceil($total / $perPage));

        return [
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
        ];
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

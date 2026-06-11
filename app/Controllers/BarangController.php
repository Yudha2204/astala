<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\BarangModel;
use App\Models\FotoBarangModel;

class BarangController extends BaseController
{
    private const PER_PAGE = 12;
    private const MAX_PHOTOS = 5;
    private const MAX_PHOTO_SIZE = 5_242_880;

    public function index()
    {
        $query = [
            'search' => trim((string) $this->request->getGet('search')),
            'status_kondisi' => trim((string) $this->request->getGet('status_kondisi')),
            'status_ketersediaan' => trim((string) $this->request->getGet('status_ketersediaan')),
            'page' => max(1, (int) ($this->request->getGet('page') ?? 1)),
        ];

        $model = new BarangModel();
        $this->applyFilters($model, $query);
        $total = $model->countAllResults(false);
        $barangs = $model
            ->orderBy('created_at', 'DESC')
            ->findAll(self::PER_PAGE, ($query['page'] - 1) * self::PER_PAGE);

        $photos = $this->photosByBarang(array_column($barangs, 'id'));
        foreach ($barangs as &$barang) {
            $barang['fotos'] = $photos[$barang['id']] ?? [];
        }

        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        return view('barang/index', [
            'title' => 'Daftar Barang - ASTALA',
            'user' => session('user'),
            'barangs' => $barangs,
            'query' => $query,
            'pagination' => [
                'page' => $query['page'],
                'totalPages' => $totalPages,
                'total' => $total,
                'hasPrev' => $query['page'] > 1,
                'hasNext' => $query['page'] < $totalPages,
            ],
        ]);
    }

    public function showAdd()
    {
        return view('barang/form', [
            'title' => 'Tambah Barang - ASTALA',
            'mode' => 'add',
            'user' => session('user'),
            'barang' => null,
            'existingNames' => $this->existingNames(),
        ]);
    }

    public function add()
    {
        $payload = $this->payload();
        $payload['status_ketersediaan'] = 'tersedia';

        if ((new BarangModel())->where('nomor_seri', $payload['nomor_seri'])->first()) {
            return redirect()->to('/barang/add')->with('error', 'Nomor seri sudah terdaftar');
        }

        $barangModel = new BarangModel();
        $barangId = $barangModel->insert($payload, true);

        $this->savePhotos((int) $barangId);
        $this->logActivity('CREATE_BARANG', "Menambahkan barang: {$payload['nama_barang']} ({$payload['nomor_seri']})", 'barang', (int) $barangId);

        return redirect()->to('/barang')->with('success', 'Barang berhasil ditambahkan');
    }

    public function showEdit(int $id)
    {
        $barang = (new BarangModel())->find($id);

        if (! $barang) {
            return redirect()->to('/barang')->with('error', 'Barang tidak ditemukan');
        }

        $barang['fotos'] = (new FotoBarangModel())
            ->where('barang_id', $id)
            ->orderBy('urutan', 'ASC')
            ->findAll();

        return view('barang/form', [
            'title' => 'Edit Barang - ASTALA',
            'mode' => 'edit',
            'user' => session('user'),
            'barang' => $barang,
            'existingNames' => $this->existingNames(),
        ]);
    }

    public function edit(int $id)
    {
        $barangModel = new BarangModel();
        $barang = $barangModel->find($id);

        if (! $barang) {
            return redirect()->to('/barang')->with('error', 'Barang tidak ditemukan');
        }

        $payload = $this->payload();
        $payload['status_ketersediaan'] = $this->request->getPost('status_ketersediaan') ?: 'tersedia';

        $duplicate = (new BarangModel())
            ->where('nomor_seri', $payload['nomor_seri'])
            ->where('id !=', $id)
            ->first();

        if ($duplicate) {
            return redirect()->to('/barang/edit/' . $id)->with('error', 'Nomor seri sudah digunakan barang lain');
        }

        $barangModel->update($id, $payload);
        $this->deletePhotosFromPost($id);
        $this->savePhotos($id);
        $this->logActivity('UPDATE_BARANG', "Mengubah barang: {$payload['nama_barang']} ({$payload['nomor_seri']})", 'barang', $id);

        return redirect()->to('/barang')->with('success', 'Barang berhasil diperbarui');
    }

    public function delete(int $id)
    {
        $barangModel = new BarangModel();
        $barang = $barangModel->find($id);

        if (! $barang) {
            return redirect()->to('/barang')->with('error', 'Barang tidak ditemukan');
        }

        if ($barang['status_ketersediaan'] === 'dipinjam') {
            return redirect()->to('/barang')->with('error', 'Barang sedang dipinjam, tidak bisa dihapus');
        }

        $photos = (new FotoBarangModel())->where('barang_id', $id)->findAll();
        foreach ($photos as $photo) {
            $this->deletePhysicalPhoto($photo['foto_path']);
        }

        (new FotoBarangModel())->where('barang_id', $id)->delete();
        $this->logActivity('DELETE_BARANG', "Menghapus barang: {$barang['nama_barang']} ({$barang['nomor_seri']})", 'barang', $id);
        $barangModel->delete($id);

        return redirect()->to('/barang')->with('success', 'Barang berhasil dihapus');
    }

    public function detail(int $id)
    {
        $barang = (new BarangModel())->find($id);

        if (! $barang) {
            return redirect()->to('/barang')->with('error', 'Barang tidak ditemukan');
        }

        $barang['fotos'] = (new FotoBarangModel())
            ->where('barang_id', $id)
            ->orderBy('urutan', 'ASC')
            ->findAll();

        return view('barang/detail', [
            'title' => 'Detail ' . $barang['nama_barang'] . ' - ASTALA',
            'user' => session('user'),
            'barang' => $barang,
            'loans' => $this->loanTimeline($id),
        ]);
    }

    public function getByBarcode(string $code)
    {
        $barang = (new BarangModel())->where('nomor_seri', $code)->first();

        if (! $barang) {
            return $this->response->setJSON(['success' => false, 'message' => 'Barang tidak ditemukan']);
        }

        $barang['fotos'] = (new FotoBarangModel())->where('barang_id', $barang['id'])->findAll();

        return $this->response->setJSON(['success' => true, 'data' => $barang]);
    }

    public function getNames()
    {
        $q = trim((string) $this->request->getGet('q'));
        $builder = (new BarangModel())->select('nama_barang')->groupBy('nama_barang')->orderBy('nama_barang', 'ASC')->limit(10);

        if ($q !== '') {
            $builder->like('nama_barang', $q);
        }

        return $this->response->setJSON(array_column($builder->findAll(), 'nama_barang'));
    }

    public function toggleQR(int $id)
    {
        $barang = (new BarangModel())->find($id);

        if (! $barang) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Barang tidak ditemukan']);
        }

        $wajibQr = filter_var($this->request->getJSON(true)['wajib_qr'] ?? $this->request->getPost('wajib_qr'), FILTER_VALIDATE_BOOLEAN);
        (new BarangModel())->update($id, ['wajib_qr' => $wajibQr]);
        $this->logActivity('UPDATE_BARANG_QR', 'Mengubah status wajib QR barang: ' . $barang['nama_barang'] . ' menjadi ' . ($wajibQr ? 'ON' : 'OFF'), 'barang', $id);

        return $this->response->setJSON(['success' => true, 'message' => 'Status QR berhasil diperbarui']);
    }

    private function applyFilters(BarangModel $model, array $query): void
    {
        if ($query['search'] !== '') {
            $model->groupStart()
                ->like('nama_barang', $query['search'])
                ->orLike('nomor_seri', $query['search'])
                ->orLike('kategori', $query['search'])
                ->groupEnd();
        }

        if ($query['status_kondisi'] !== '') {
            $model->where('status_kondisi', $query['status_kondisi']);
        }

        if ($query['status_ketersediaan'] !== '') {
            $model->where('status_ketersediaan', $query['status_ketersediaan']);
        }
    }

    private function payload(): array
    {
        return [
            'nama_barang' => trim((string) $this->request->getPost('nama_barang')),
            'nomor_seri' => trim((string) $this->request->getPost('nomor_seri')),
            'deskripsi' => trim((string) $this->request->getPost('deskripsi')) ?: null,
            'kategori' => trim((string) $this->request->getPost('kategori')) ?: null,
            'lokasi_penyimpanan' => trim((string) $this->request->getPost('lokasi_penyimpanan')) ?: null,
            'status_kondisi' => $this->request->getPost('status_kondisi') ?: 'baik',
        ];
    }

    private function existingNames(): array
    {
        return array_column(
            (new BarangModel())->select('nama_barang')->groupBy('nama_barang')->orderBy('nama_barang', 'ASC')->findAll(),
            'nama_barang'
        );
    }

    private function photosByBarang(array $ids): array
    {
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

    private function savePhotos(int $barangId): void
    {
        $files = $this->request->getFiles()['fotos'] ?? [];
        $files = is_array($files) ? $files : [$files];
        $currentCount = (new FotoBarangModel())->where('barang_id', $barangId)->countAllResults();
        $slot = $currentCount;

        foreach ($files as $file) {
            if ($slot >= self::MAX_PHOTOS || ! $file->isValid() || $file->hasMoved()) {
                continue;
            }

            if (! str_starts_with((string) $file->getMimeType(), 'image/') || $file->getSize() > self::MAX_PHOTO_SIZE) {
                continue;
            }

            $dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'barang';
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $name = 'barang-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $file->getExtension();
            $file->move($dir, $name);
            $slot++;

            (new FotoBarangModel())->insert([
                'barang_id' => $barangId,
                'foto_path' => '/uploads/barang/' . $name,
                'urutan' => $slot,
            ]);
        }
    }

    private function deletePhotosFromPost(int $barangId): void
    {
        $ids = json_decode((string) $this->request->getPost('deleted_photos'), true);
        if (! is_array($ids)) {
            return;
        }

        $photoModel = new FotoBarangModel();
        foreach ($ids as $id) {
            $photo = $photoModel->where('barang_id', $barangId)->where('id', (int) $id)->first();
            if (! $photo) {
                continue;
            }
            $this->deletePhysicalPhoto($photo['foto_path']);
            $photoModel->delete($photo['id']);
        }
    }

    private function deletePhysicalPhoto(?string $path): void
    {
        if (! $path) {
            return;
        }

        $file = FCPATH . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
        if (is_file($file) && str_contains(realpath($file) ?: '', realpath(FCPATH . 'uploads') ?: FCPATH)) {
            unlink($file);
        }
    }

    private function loanTimeline(int $barangId): array
    {
        $user = session('user');
        if (! in_array($user['role'] ?? '', ['admin', 'manager'], true) && (($user['role'] ?? '') !== 'karyawan' || ($user['sub_user'] ?? '') !== 'editor')) {
            return [];
        }

        $loans = db_connect()->table('peminjaman p')
            ->select('p.*, u.nama AS user_nama, u.role AS user_role')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->where('p.barang_id', $barangId)
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();

        if (! $loans) {
            return [];
        }

        $loanIds = array_column($loans, 'id');
        $photos = db_connect()->table('foto_peminjaman')->whereIn('peminjaman_id', $loanIds)->get()->getResultArray();
        $groupedPhotos = [];
        foreach ($photos as $photo) {
            $groupedPhotos[$photo['peminjaman_id']][] = $photo;
        }

        foreach ($loans as &$loan) {
            $loan['fotos'] = $groupedPhotos[$loan['id']] ?? [];
        }

        return $loans;
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

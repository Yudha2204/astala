<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\AsetMaterialModel;
use App\Models\FotoAsetMaterialModel;
use App\Models\GudangModel;
use App\Models\NotifikasiModel;
use App\Models\StokHistoryModel;
use App\Models\UserModel;
use Throwable;

class AsetMaterialController extends BaseController
{
    private const MAX_PHOTOS = 5;
    private const MAX_PHOTO_SIZE = 5_242_880;

    public function index()
    {
        $query = [
            'gudang_id' => (int) ($this->request->getGet('gudang_id') ?? 0),
            'tipe' => trim((string) $this->request->getGet('tipe')),
            'search' => trim((string) $this->request->getGet('search')),
        ];

        $builder = $this->baseAsetBuilder();
        if ($query['gudang_id'] > 0) {
            $builder->where('a.gudang_id', $query['gudang_id']);
        }
        if (in_array($query['tipe'], ['kabel', 'odp', 'closure'], true)) {
            $builder->where('a.tipe', $query['tipe']);
        }
        if ($query['search'] !== '') {
            $builder->like('a.nama', $query['search']);
        }

        $asets = $builder
            ->orderBy('a.tipe', 'ASC')
            ->orderBy('a.core', 'ASC')
            ->orderBy('a.nama', 'ASC')
            ->get()
            ->getResultArray();

        $asetsByGudang = [];
        foreach ($asets as $aset) {
            $gudangNama = $aset['gudang_nama'] ?: 'Unknown';
            if (! isset($asetsByGudang[$gudangNama])) {
                $asetsByGudang[$gudangNama] = [
                    'gudang' => [
                        'id' => $aset['gudang_id'],
                        'nama' => $aset['gudang_nama'],
                        'tipe' => $aset['gudang_tipe'],
                    ],
                    'kabel' => [],
                    'odp' => [],
                    'closure' => [],
                ];
            }
            $asetsByGudang[$gudangNama][$aset['tipe']][] = $aset;
        }

        return view('admin/aset-material/index', [
            'title' => 'Aset Material - ASTALA',
            'user' => session('user'),
            'asets' => $asets,
            'asetsByGudang' => $asetsByGudang,
            'gudangs' => (new GudangModel())->orderBy('nama', 'ASC')->findAll(),
            'query' => $query,
        ]);
    }

    public function detail(int $id)
    {
        $aset = $this->findAset($id);
        if (! $aset) {
            return redirect()->to('/admin/aset-material')->with('error', 'Aset tidak ditemukan');
        }

        $aset['fotos'] = (new FotoAsetMaterialModel())
            ->where('aset_id', $id)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $stokHistories = db_connect()->table('stok_history s')
            ->select('s.*, u.nama AS user_nama')
            ->join('users u', 'u.id = s.created_by', 'left')
            ->where('s.aset_id', $id)
            ->orderBy('s.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/aset-material/detail', [
            'title' => 'Detail ' . $aset['nama'] . ' - ASTALA',
            'user' => session('user'),
            'aset' => $aset,
            'stokHistories' => $stokHistories,
            'isLowStock' => $this->isLowStock($aset),
        ]);
    }

    public function showAdd()
    {
        return view('admin/aset-material/form', [
            'title' => 'Tambah Aset Material - ASTALA',
            'user' => session('user'),
            'aset' => null,
            'gudangs' => (new GudangModel())->orderBy('nama', 'ASC')->findAll(),
            'mode' => 'add',
        ]);
    }

    public function add()
    {
        $payload = $this->createPayload();
        $db = db_connect();
        $db->transStart();

        try {
            $asetId = (int) (new AsetMaterialModel())->insert($payload, true);
            $primaryPhoto = $this->savePhotos($asetId)[0] ?? null;

            $initialStock = $payload['tipe'] === 'kabel' ? (int) $payload['stok_roll'] : (int) $payload['stok_pcs'];
            if ($initialStock > 0) {
                (new StokHistoryModel())->insert([
                    'aset_id' => $asetId,
                    'tipe_aktivitas' => 'masuk',
                    'jumlah_roll' => $payload['tipe'] === 'kabel' ? $payload['stok_roll'] : 0,
                    'jumlah_meter' => $payload['stok_meter'],
                    'jumlah_pcs' => $payload['tipe'] !== 'kabel' ? $payload['stok_pcs'] : 0,
                    'foto_path' => $primaryPhoto,
                    'keterangan' => 'Stok awal',
                    'created_by' => session('user')['id'] ?? null,
                ]);
            }

            $this->logActivity('CREATE_ASET', "Menambahkan aset material: {$payload['nama']} ({$payload['tipe']} {$payload['core']} core)", 'aset_material', $asetId);
            $db->transComplete();

            $this->checkLowStockAndNotify($asetId);

            return redirect()->to('/admin/aset-material')->with('success', 'Aset material berhasil ditambahkan');
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Add aset error: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/admin/aset-material/add')->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showAddStock(int $id)
    {
        $aset = $this->findAset($id);
        if (! $aset) {
            return redirect()->to('/admin/aset-material')->with('error', 'Aset tidak ditemukan');
        }

        return view('admin/aset-material/add-stock', [
            'title' => 'Tambah Stok - ASTALA',
            'user' => session('user'),
            'aset' => $aset,
        ]);
    }

    public function addStock(int $id)
    {
        $asetModel = new AsetMaterialModel();
        $aset = $asetModel->find($id);
        if (! $aset) {
            return redirect()->to('/admin/aset-material')->with('error', 'Aset tidak ditemukan');
        }

        $fotoPath = $this->saveSingleHistoryPhoto();

        if ($aset['tipe'] === 'kabel') {
            $addRoll = max(0, (int) $this->request->getPost('tambah_roll'));
            $addMeter = $addRoll * (int) $aset['meter_per_roll'];
            $asetModel->update($id, [
                'stok_roll' => (int) $aset['stok_roll'] + $addRoll,
                'stok_meter' => (int) $aset['stok_meter'] + $addMeter,
            ]);
            $history = [
                'jumlah_roll' => $addRoll,
                'jumlah_meter' => $addMeter,
                'jumlah_pcs' => 0,
                'keterangan' => trim((string) $this->request->getPost('keterangan')) ?: "Penambahan stok: +{$addRoll} roll",
            ];
            $description = "Menambah stok {$aset['nama']}: +{$addRoll} roll (+{$addMeter} meter)";
        } else {
            $addPcs = max(0, (int) $this->request->getPost('tambah_pcs'));
            $asetModel->update($id, ['stok_pcs' => (int) $aset['stok_pcs'] + $addPcs]);
            $history = [
                'jumlah_roll' => 0,
                'jumlah_meter' => 0,
                'jumlah_pcs' => $addPcs,
                'keterangan' => trim((string) $this->request->getPost('keterangan')) ?: "Penambahan stok: +{$addPcs} pcs",
            ];
            $description = "Menambah stok {$aset['nama']}: +{$addPcs} pcs";
        }

        (new StokHistoryModel())->insert($history + [
            'aset_id' => $id,
            'tipe_aktivitas' => 'masuk',
            'foto_path' => $fotoPath,
            'created_by' => session('user')['id'] ?? null,
        ]);

        $this->logActivity('ADD_STOCK', $description, 'aset_material', $id);
        $this->checkLowStockAndNotify($id);

        return redirect()->to('/admin/aset-material/detail/' . $id)->with('success', 'Stok berhasil ditambahkan');
    }

    public function showEdit(int $id)
    {
        $aset = (new AsetMaterialModel())->find($id);
        if (! $aset) {
            return redirect()->to('/admin/aset-material')->with('error', 'Aset tidak ditemukan');
        }

        $aset['fotos'] = (new FotoAsetMaterialModel())
            ->where('aset_id', $id)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('admin/aset-material/form', [
            'title' => 'Edit Aset Material - ASTALA',
            'user' => session('user'),
            'aset' => $aset,
            'gudangs' => (new GudangModel())->orderBy('nama', 'ASC')->findAll(),
            'mode' => 'edit',
        ]);
    }

    public function update(int $id)
    {
        $asetModel = new AsetMaterialModel();
        $aset = $asetModel->find($id);
        if (! $aset) {
            return redirect()->to('/admin/aset-material')->with('error', 'Aset tidak ditemukan');
        }

        $payload = [
            'gudang_id' => (int) $this->request->getPost('gudang_id'),
            'nama' => trim((string) $this->request->getPost('nama')),
            'core' => (int) $this->request->getPost('core'),
            'deskripsi' => trim((string) $this->request->getPost('deskripsi')) ?: null,
            'min_stok_roll' => $aset['tipe'] === 'kabel' ? max(0, (int) $this->request->getPost('min_stok_roll')) : (int) $aset['min_stok_roll'],
            'min_stok_pcs' => $aset['tipe'] !== 'kabel' ? max(0, (int) $this->request->getPost('min_stok_pcs')) : (int) $aset['min_stok_pcs'],
        ];

        $asetModel->update($id, $payload);
        $this->deletePhotosFromPost($id);
        $this->savePhotos($id);
        $this->logActivity('UPDATE_ASET', 'Mengubah aset material: ' . $payload['nama'], 'aset_material', $id);
        $this->checkLowStockAndNotify($id);

        return redirect()->to('/admin/aset-material')->with('success', 'Aset berhasil diperbarui');
    }

    public function delete(int $id)
    {
        $aset = (new AsetMaterialModel())->find($id);
        if (! $aset) {
            return redirect()->to('/admin/aset-material')->with('error', 'Aset tidak ditemukan');
        }

        $photos = (new FotoAsetMaterialModel())->where('aset_id', $id)->findAll();
        foreach ($photos as $photo) {
            $this->deletePhysicalPhoto($photo['foto_path']);
        }

        (new FotoAsetMaterialModel())->where('aset_id', $id)->delete();
        (new AsetMaterialModel())->delete($id);
        $this->logActivity('DELETE_ASET', 'Menghapus aset material: ' . $aset['nama'], 'aset_material', $id);

        return redirect()->to('/admin/aset-material')->with('success', 'Aset berhasil dihapus');
    }

    public function getByGudang(int $gudangId)
    {
        $asets = (new AsetMaterialModel())
            ->select('id,nama,tipe,stok_roll,stok_meter,stok_pcs,meter_per_roll')
            ->where('gudang_id', $gudangId)
            ->orderBy('tipe', 'ASC')
            ->orderBy('nama', 'ASC')
            ->findAll();

        return $this->response->setJSON(['success' => true, 'asets' => $asets]);
    }

    public function checkStock()
    {
        $aset = (new AsetMaterialModel())->find((int) $this->request->getGet('aset_id'));
        if (! $aset) {
            return $this->response->setJSON(['success' => false, 'available' => false, 'error' => 'Aset tidak ditemukan']);
        }

        $quantity = (int) $this->request->getGet('quantity');
        $currentStock = $aset['tipe'] === 'kabel' ? (int) $aset['stok_meter'] : (int) $aset['stok_pcs'];

        return $this->response->setJSON([
            'success' => true,
            'available' => $currentStock >= $quantity,
            'currentStock' => $currentStock,
            'requestedQuantity' => $quantity,
        ]);
    }

    private function baseAsetBuilder()
    {
        return db_connect()->table('aset_material a')
            ->select('a.*, g.nama AS gudang_nama, g.tipe AS gudang_tipe, g.lokasi AS gudang_lokasi')
            ->join('gudang g', 'g.id = a.gudang_id', 'left');
    }

    private function findAset(int $id): ?array
    {
        return $this->baseAsetBuilder()->where('a.id', $id)->get()->getRowArray();
    }

    private function createPayload(): array
    {
        $tipe = in_array($this->request->getPost('tipe'), ['kabel', 'odp', 'closure'], true) ? $this->request->getPost('tipe') : 'kabel';
        $meterPerRoll = max(1, (int) ($this->request->getPost('meter_per_roll') ?: 4000));
        $stokRoll = $tipe === 'kabel' ? max(0, (int) $this->request->getPost('stok_roll')) : 0;
        $stokPcs = $tipe !== 'kabel' ? max(0, (int) $this->request->getPost('stok_pcs')) : 0;

        return [
            'gudang_id' => (int) $this->request->getPost('gudang_id'),
            'tipe' => $tipe,
            'nama' => trim((string) $this->request->getPost('nama')),
            'core' => (int) $this->request->getPost('core'),
            'stok_roll' => $stokRoll,
            'stok_meter' => $stokRoll * $meterPerRoll,
            'meter_per_roll' => $meterPerRoll,
            'stok_pcs' => $stokPcs,
            'deskripsi' => trim((string) $this->request->getPost('deskripsi')) ?: null,
            'min_stok_roll' => $tipe === 'kabel' ? max(0, (int) $this->request->getPost('min_stok_roll')) : 0,
            'min_stok_pcs' => $tipe !== 'kabel' ? max(0, (int) $this->request->getPost('min_stok_pcs')) : 0,
        ];
    }

    private function isLowStock(array $aset): bool
    {
        if ($aset['tipe'] === 'kabel') {
            return (int) $aset['min_stok_roll'] > 0 && (int) $aset['stok_roll'] <= (int) $aset['min_stok_roll'];
        }

        return (int) $aset['min_stok_pcs'] > 0 && (int) $aset['stok_pcs'] <= (int) $aset['min_stok_pcs'];
    }

    private function savePhotos(int $asetId): array
    {
        $files = $this->uploadedFiles('fotos');
        $existingCount = (new FotoAsetMaterialModel())->where('aset_id', $asetId)->countAllResults();
        $saved = [];

        foreach ($files as $index => $file) {
            if ($existingCount + count($saved) >= self::MAX_PHOTOS || ! $file->isValid() || $file->hasMoved()) {
                continue;
            }
            if (! str_starts_with((string) $file->getMimeType(), 'image/') || $file->getSize() > self::MAX_PHOTO_SIZE) {
                continue;
            }

            $dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'aset-material';
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $name = 'aset-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . ($file->getClientExtension() ?: 'jpg');
            $file->move($dir, $name);
            $path = '/uploads/aset-material/' . $name;
            $saved[] = $path;

            (new FotoAsetMaterialModel())->insert([
                'aset_id' => $asetId,
                'foto_path' => $path,
                'is_primary' => $existingCount === 0 && $index === 0,
            ]);
        }

        return $saved;
    }

    private function saveSingleHistoryPhoto(): ?string
    {
        $files = $this->uploadedFiles('foto');
        $file = $files[0] ?? null;
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }
        if (! str_starts_with((string) $file->getMimeType(), 'image/') || $file->getSize() > self::MAX_PHOTO_SIZE) {
            return null;
        }

        $dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'aset-material';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $name = 'aset-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . ($file->getClientExtension() ?: 'jpg');
        $file->move($dir, $name);

        return '/uploads/aset-material/' . $name;
    }

    private function uploadedFiles(string $key): array
    {
        $files = $this->request->getFiles()[$key] ?? [];
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

    private function deletePhotosFromPost(int $asetId): void
    {
        $ids = $this->request->getPost('delete_fotos');
        if (! $ids) {
            return;
        }

        $ids = is_array($ids) ? $ids : [$ids];
        $model = new FotoAsetMaterialModel();
        foreach ($ids as $id) {
            $photo = $model->where('aset_id', $asetId)->where('id', (int) $id)->first();
            if (! $photo) {
                continue;
            }
            $this->deletePhysicalPhoto($photo['foto_path']);
            $model->delete($photo['id']);
        }
    }

    private function deletePhysicalPhoto(?string $path): void
    {
        if (! $path) {
            return;
        }

        $file = FCPATH . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
        $uploadRoot = realpath(FCPATH . 'uploads');
        $realFile = realpath($file);

        if ($realFile && $uploadRoot && str_starts_with($realFile, $uploadRoot) && is_file($realFile)) {
            unlink($realFile);
        }
    }

    private function checkLowStockAndNotify(int $asetId): void
    {
        $aset = $this->findAset($asetId);
        if (! $aset || ! $this->isLowStock($aset)) {
            return;
        }

        $stockInfo = $aset['tipe'] === 'kabel'
            ? "{$aset['stok_roll']} roll (min: {$aset['min_stok_roll']} roll)"
            : "{$aset['stok_pcs']} pcs (min: {$aset['min_stok_pcs']} pcs)";

        $admins = (new UserModel())->where('role', 'admin')->findAll();
        $notifModel = new NotifikasiModel();

        foreach ($admins as $admin) {
            $link = '/admin/aset-material/detail/' . $asetId;
            $existing = $notifModel
                ->where('user_id', $admin['id'])
                ->where('judul', 'Stok Menipis')
                ->where('link', $link)
                ->where('is_read', 0)
                ->first();

            if ($existing) {
                continue;
            }

            $notifModel->insert([
                'user_id' => $admin['id'],
                'judul' => 'Stok Menipis',
                'pesan' => 'Stok ' . $aset['nama'] . ' di ' . ($aset['gudang_nama'] ?: 'Gudang') . ' telah mencapai batas minimum: ' . $stockInfo,
                'tipe' => 'warning',
                'link' => $link,
            ]);
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

<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\AsetMaterialModel;
use App\Models\FotoPengambilanModel;
use App\Models\GudangModel;
use App\Models\NotifikasiModel;
use App\Models\PengambilanAsetModel;
use App\Models\PengambilanItemModel;
use App\Models\StokHistoryModel;
use App\Models\UserModel;
use DateTimeImmutable;
use Throwable;

class PengambilanController extends BaseController
{
    private const PER_PAGE = 10;
    private const MAX_PHOTO_SIZE = 5_242_880;

    public function index()
    {
        $user = session('user');
        $query = $this->listQuery();
        $builder = $this->baseBuilder()->where('p.mitra_id', $user['id'] ?? 0);
        $this->applyListFilters($builder, $query, false);
        $pengambilans = $builder->orderBy('p.created_at', 'DESC')->get()->getResultArray();
        $this->hydratePengambilans($pengambilans, false);

        return view('pengambilan/index', [
            'title' => 'Pengambilan Aset - ASTALA',
            'user' => $user,
            'pengambilans' => $pengambilans,
            'stats' => [
                'total' => (new PengambilanAsetModel())->where('mitra_id', $user['id'] ?? 0)->countAllResults(),
                'pending' => (new PengambilanAsetModel())->where('mitra_id', $user['id'] ?? 0)->whereIn('status', ['request', 'waiting'])->countAllResults(),
                'completed' => (new PengambilanAsetModel())->where('mitra_id', $user['id'] ?? 0)->where('status', 'done')->countAllResults(),
            ],
            'query' => $query,
        ]);
    }

    public function showRequest()
    {
        $gudangs = (new GudangModel())->orderBy('nama', 'ASC')->findAll();
        $asets = (new AsetMaterialModel())
            ->groupStart()
            ->where('stok_roll >', 0)
            ->orWhere('stok_pcs >', 0)
            ->groupEnd()
            ->orderBy('tipe', 'ASC')
            ->orderBy('nama', 'ASC')
            ->findAll();

        $byGudang = [];
        foreach ($asets as $aset) {
            $byGudang[$aset['gudang_id']][] = [
                'id' => (int) $aset['id'],
                'nama' => $aset['nama'],
                'tipe' => $aset['tipe'],
                'core' => (int) $aset['core'],
                'stok_roll' => (int) $aset['stok_roll'],
                'stok_meter' => (int) $aset['stok_meter'],
                'stok_pcs' => (int) $aset['stok_pcs'],
                'meter_per_roll' => (int) $aset['meter_per_roll'],
            ];
        }

        foreach ($gudangs as &$gudang) {
            $gudang['asetMaterials'] = $byGudang[$gudang['id']] ?? [];
        }

        return view('pengambilan/request', [
            'title' => 'Request Pengambilan - ASTALA',
            'user' => session('user'),
            'gudangs' => $gudangs,
        ]);
    }

    public function submitRequest()
    {
        $user = session('user');
        $items = json_decode((string) $this->request->getPost('items'), true);

        if (! is_array($items) || $items === []) {
            return redirect()->to('/pengambilan/request')->withInput()->with('error', 'Silakan pilih minimal satu aset');
        }

        $db = db_connect();
        $db->transStart();

        try {
            $id = (int) (new PengambilanAsetModel())->insert([
                'mitra_id' => $user['id'] ?? null,
                'gudang_id' => (int) $this->request->getPost('gudang_id'),
                'nama_mitra' => $user['nama'] ?? 'Mitra',
                'nama_petugas' => trim((string) $this->request->getPost('nama_petugas')),
                'deskripsi_keperluan' => trim((string) $this->request->getPost('deskripsi_keperluan')) ?: null,
                'status' => 'request',
                'tanggal_request' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ], true);

            $itemModel = new PengambilanItemModel();
            foreach ($items as $item) {
                $aset = (new AsetMaterialModel())->find((int) ($item['aset_id'] ?? 0));
                if (! $aset) {
                    continue;
                }

                $itemModel->insert([
                    'pengambilan_id' => $id,
                    'aset_id' => $aset['id'],
                    'jumlah_roll' => $aset['tipe'] === 'kabel' ? (float) ($item['jumlah_roll'] ?? 0) : 0,
                    'jumlah_meter' => $aset['tipe'] === 'kabel' ? (int) ($item['jumlah_meter'] ?? 0) : 0,
                    'jumlah_pcs' => $aset['tipe'] !== 'kabel' ? (int) ($item['jumlah_pcs'] ?? 0) : 0,
                ]);
            }

            $this->notifyAdmins('Request Pengambilan Baru', ($user['nama'] ?? 'Mitra') . ' mengajukan request pengambilan aset', 'info', '/pengambilan/admin');
            $this->logActivity('REQUEST_PENGAMBILAN', 'Mitra ' . ($user['nama'] ?? '-') . ' mengajukan request pengambilan', 'pengambilan_aset', $id);

            $db->transComplete();

            return redirect()->to('/pengambilan')->with('success', 'Request pengambilan berhasil diajukan');
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Submit pengambilan error: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/pengambilan/request')->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function detail(int $id)
    {
        $user = session('user');
        $pengambilan = $this->findPengambilan($id);

        if (! $pengambilan) {
            return redirect()->to('/pengambilan')->with('error', 'Data tidak ditemukan');
        }

        if ((int) $pengambilan['mitra_id'] !== (int) ($user['id'] ?? 0) && ($user['role'] ?? '') !== 'admin') {
            return redirect()->to('/pengambilan')->with('error', 'Anda tidak memiliki akses');
        }

        return view('pengambilan/detail', [
            'title' => 'Detail Pengambilan - ASTALA',
            'user' => $user,
            'pengambilan' => $pengambilan,
            'backUrl' => ($user['role'] ?? '') === 'admin' ? '/pengambilan/admin' : '/pengambilan',
        ]);
    }

    public function showPickup(int $id)
    {
        $pengambilan = $this->findPengambilan($id);

        if (! $pengambilan || $pengambilan['status'] !== 'pickup') {
            return redirect()->to('/pengambilan/detail/' . $id)->with('error', 'Status tidak valid untuk pickup');
        }

        return view('pengambilan/pickup', [
            'title' => 'Pickup Aset - ASTALA',
            'user' => session('user'),
            'pengambilan' => $pengambilan,
        ]);
    }

    public function submitPickup(int $id)
    {
        $pengambilan = $this->findPengambilan($id);
        if (! $pengambilan || $pengambilan['status'] !== 'pickup') {
            return redirect()->to('/pengambilan/detail/' . $id)->with('error', 'Status tidak valid untuk pickup');
        }

        $this->savePickupPhotos($id, $pengambilan['items']);

        (new PengambilanAsetModel())->update($id, [
            'status' => 'confirmation',
            'ttd_petugas' => (string) $this->request->getPost('ttd_petugas'),
            'tanggal_pickup' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $this->notifyAdmins('Pengambilan Menunggu Konfirmasi', 'Pengambilan oleh ' . $pengambilan['nama_mitra'] . ' menunggu konfirmasi admin', 'warning', '/pengambilan/admin/detail/' . $id);
        $this->logActivity('PICKUP_ASET', 'Mitra menyelesaikan pickup aset', 'pengambilan_aset', $id);

        return redirect()->to('/pengambilan/detail/' . $id)->with('success', 'Pickup berhasil. Menunggu konfirmasi admin.');
    }

    public function adminIndex()
    {
        $query = $this->listQuery();
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $countBuilder = $this->baseBuilder();
        $this->applyListFilters($countBuilder, $query, true);
        $total = $countBuilder->countAllResults();

        $dataBuilder = $this->baseBuilder();
        $this->applyListFilters($dataBuilder, $query, true);
        $pengambilans = $dataBuilder
            ->orderBy('p.created_at', 'DESC')
            ->limit(self::PER_PAGE, ($page - 1) * self::PER_PAGE)
            ->get()
            ->getResultArray();
        $this->hydratePengambilans($pengambilans, false);

        return view('admin/pengambilan/index', [
            'title' => 'Pengambilan Aset - ASTALA',
            'user' => session('user'),
            'pengambilans' => $pengambilans,
            'stats' => [
                'pending' => (new PengambilanAsetModel())->where('status', 'request')->countAllResults(),
                'waiting' => (new PengambilanAsetModel())->where('status', 'waiting')->countAllResults(),
                'confirmation' => (new PengambilanAsetModel())->where('status', 'confirmation')->countAllResults(),
                'done' => (new PengambilanAsetModel())->where('status', 'done')->countAllResults(),
            ],
            'query' => $query,
            'pagination' => $this->pagination($page, $total),
        ]);
    }

    public function adminDetail(int $id)
    {
        $pengambilan = $this->findPengambilan($id);
        if (! $pengambilan) {
            return redirect()->to('/pengambilan/admin')->with('error', 'Data tidak ditemukan');
        }

        return view('pengambilan/detail', [
            'title' => 'Detail Pengambilan - ASTALA',
            'user' => session('user'),
            'pengambilan' => $pengambilan,
            'backUrl' => '/pengambilan/admin',
            'adminMode' => true,
        ]);
    }

    public function approve(int $id)
    {
        $user = session('user');
        $pengambilan = (new PengambilanAsetModel())->find($id);
        if (! $pengambilan) {
            return redirect()->to('/pengambilan/admin')->with('error', 'Data tidak ditemukan');
        }

        (new PengambilanAsetModel())->update($id, [
            'status' => 'pickup',
            'tanggal_approval' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'approved_by' => $user['id'] ?? null,
        ]);

        $this->notifyUser((int) $pengambilan['mitra_id'], 'Request Disetujui', 'Request pengambilan aset Anda telah disetujui. Silakan lakukan pickup.', 'success', '/pengambilan/pickup/' . $id);
        $this->logActivity('APPROVE_PENGAMBILAN', 'Admin menyetujui request pengambilan', 'pengambilan_aset', $id);

        return redirect()->to('/pengambilan/admin')->with('success', 'Request berhasil disetujui');
    }

    public function reject(int $id)
    {
        $user = session('user');
        $pengambilan = (new PengambilanAsetModel())->find($id);
        if (! $pengambilan) {
            return redirect()->to('/pengambilan/admin')->with('error', 'Data tidak ditemukan');
        }

        $alasan = trim((string) $this->request->getPost('alasan'));
        (new PengambilanAsetModel())->update($id, [
            'status' => 'rejected',
            'alasan_penolakan' => $alasan ?: null,
            'tanggal_approval' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'approved_by' => $user['id'] ?? null,
        ]);

        $this->notifyUser((int) $pengambilan['mitra_id'], 'Request Ditolak', 'Request pengambilan aset Anda ditolak. Alasan: ' . ($alasan ?: 'Tidak ada keterangan'), 'danger', '/pengambilan/detail/' . $id);
        $this->logActivity('REJECT_PENGAMBILAN', 'Admin menolak request pengambilan: ' . $alasan, 'pengambilan_aset', $id);

        return redirect()->to('/pengambilan/admin')->with('success', 'Request berhasil ditolak');
    }

    public function showConfirm(int $id)
    {
        $pengambilan = $this->findPengambilan($id);
        if (! $pengambilan || $pengambilan['status'] !== 'confirmation') {
            return redirect()->to('/pengambilan/admin/detail/' . $id)->with('error', 'Status tidak valid untuk konfirmasi');
        }

        return view('admin/pengambilan/confirm', [
            'title' => 'Konfirmasi Pengambilan - ASTALA',
            'user' => session('user'),
            'pengambilan' => $pengambilan,
        ]);
    }

    public function submitConfirm(int $id)
    {
        $user = session('user');
        $pengambilan = $this->findPengambilan($id);
        if (! $pengambilan || $pengambilan['status'] !== 'confirmation') {
            return redirect()->to('/pengambilan/admin')->with('error', 'Data tidak ditemukan');
        }

        $db = db_connect();
        $db->transStart();

        try {
            $asetModel = new AsetMaterialModel();
            $historyModel = new StokHistoryModel();

            foreach ($pengambilan['items'] as $item) {
                $aset = $asetModel->find((int) $item['aset_id']);
                if (! $aset) {
                    continue;
                }

                if ($aset['tipe'] === 'kabel') {
                    $asetModel->update($aset['id'], [
                        'stok_meter' => max(0, (int) $aset['stok_meter'] - (int) $item['jumlah_meter']),
                        'stok_roll' => max(0, (float) $aset['stok_roll'] - (float) $item['jumlah_roll']),
                    ]);
                    $history = [
                        'jumlah_roll' => $item['jumlah_roll'],
                        'jumlah_meter' => $item['jumlah_meter'],
                        'jumlah_pcs' => 0,
                    ];
                } else {
                    $asetModel->update($aset['id'], [
                        'stok_pcs' => max(0, (int) $aset['stok_pcs'] - (int) $item['jumlah_pcs']),
                    ]);
                    $history = [
                        'jumlah_roll' => 0,
                        'jumlah_meter' => 0,
                        'jumlah_pcs' => $item['jumlah_pcs'],
                    ];
                }

                $historyModel->insert($history + [
                    'aset_id' => $aset['id'],
                    'tipe_aktivitas' => 'keluar',
                    'pengambilan_id' => $id,
                    'keterangan' => 'Pengambilan oleh ' . $pengambilan['nama_mitra'] . ' - ' . ($pengambilan['deskripsi_keperluan'] ?: 'Tidak ada keterangan'),
                    'created_by' => $user['id'] ?? null,
                ]);

                $this->checkLowStockAndNotify((int) $aset['id']);
            }

            (new PengambilanAsetModel())->update($id, [
                'status' => 'done',
                'ttd_admin' => (string) $this->request->getPost('ttd_admin'),
                'tanggal_done' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);

            $this->notifyUser((int) $pengambilan['mitra_id'], 'Pengambilan Selesai', 'Pengambilan aset telah dikonfirmasi.', 'success', '/pengambilan/detail/' . $id);
            $this->logActivity('CONFIRM_PENGAMBILAN', 'Admin mengkonfirmasi pengambilan aset', 'pengambilan_aset', $id);

            $db->transComplete();

            return redirect()->to('/pengambilan/admin')->with('success', 'Pengambilan berhasil dikonfirmasi');
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Confirm pengambilan error: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/pengambilan/admin/confirm/' . $id)->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function download(int $id)
    {
        return redirect()->to('/pengambilan/detail/' . $id)->with('error', 'Download surat belum dipindahkan ke CI4');
    }

    private function baseBuilder()
    {
        return db_connect()->table('pengambilan_aset p')
            ->select('p.*, g.nama AS gudang_nama, g.tipe AS gudang_tipe, u.nama AS mitra_user_nama, a.nama AS approver_nama')
            ->join('gudang g', 'g.id = p.gudang_id', 'left')
            ->join('users u', 'u.id = p.mitra_id', 'left')
            ->join('users a', 'a.id = p.approved_by', 'left');
    }

    private function listQuery(): array
    {
        return [
            'status' => trim((string) $this->request->getGet('status')),
            'search' => trim((string) $this->request->getGet('search')),
            'year' => trim((string) $this->request->getGet('year')),
            'month' => trim((string) $this->request->getGet('month')),
        ];
    }

    private function applyListFilters($builder, array $query, bool $admin): void
    {
        if ($query['status'] !== '') {
            $builder->where('p.status', $query['status']);
        }

        if ($query['search'] !== '') {
            $builder->groupStart()
                ->like('p.nama_petugas', $query['search'])
                ->orLike('p.nama_mitra', $query['search'])
                ->orLike('g.nama', $query['search']);
            if (is_numeric($query['search'])) {
                $builder->orWhere('p.id', (int) $query['search']);
            }
            if ($admin) {
                $builder->orLike('u.nama', $query['search']);
            }
            $builder->groupEnd();
        }

        if (! $admin && ($query['year'] !== '' || $query['month'] !== '')) {
            $year = $query['year'] !== '' ? (int) $query['year'] : (int) date('Y');
            $month = $query['month'] !== '' ? max(1, min(12, (int) $query['month'])) : null;

            $start = $month === null
                ? new DateTimeImmutable($year . '-01-01 00:00:00')
                : new DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $year, $month));
            $end = $month === null
                ? new DateTimeImmutable($year . '-12-31 23:59:59')
                : $start->modify('last day of this month')->setTime(23, 59, 59);

            $builder->where('p.tanggal_request >=', $start->format('Y-m-d H:i:s'))
                ->where('p.tanggal_request <=', $end->format('Y-m-d H:i:s'));
        }
    }

    private function findPengambilan(int $id): ?array
    {
        $row = $this->baseBuilder()->where('p.id', $id)->get()->getRowArray();
        if (! $row) {
            return null;
        }

        $rows = [$row];
        $this->hydratePengambilans($rows, true);

        return $rows[0];
    }

    private function hydratePengambilans(array &$rows, bool $withPhotos): void
    {
        if (! $rows) {
            return;
        }

        $ids = array_column($rows, 'id');
        $items = db_connect()->table('pengambilan_item i')
            ->select('i.*, a.nama AS aset_nama, a.tipe AS aset_tipe, a.core AS aset_core, a.stok_meter AS aset_stok_meter, a.stok_pcs AS aset_stok_pcs')
            ->join('aset_material a', 'a.id = i.aset_id', 'left')
            ->whereIn('i.pengambilan_id', $ids)
            ->get()
            ->getResultArray();
        $itemsByPengambilan = [];
        foreach ($items as $item) {
            $item['aset'] = [
                'id' => $item['aset_id'],
                'nama' => $item['aset_nama'] ?? 'N/A',
                'tipe' => $item['aset_tipe'] ?? '-',
                'core' => $item['aset_core'] ?? '-',
            ];
            $itemsByPengambilan[$item['pengambilan_id']][] = $item;
        }

        $photosByPengambilan = [];
        if ($withPhotos) {
            $photos = (new FotoPengambilanModel())->whereIn('pengambilan_id', $ids)->findAll();
            foreach ($photos as $photo) {
                $photosByPengambilan[$photo['pengambilan_id']][] = $photo;
            }
        }

        foreach ($rows as &$row) {
            $row['gudang'] = ['id' => $row['gudang_id'], 'nama' => $row['gudang_nama'] ?? '-', 'tipe' => $row['gudang_tipe'] ?? '-'];
            $row['mitra'] = ['id' => $row['mitra_id'], 'nama' => $row['mitra_user_nama'] ?? $row['nama_mitra']];
            $row['approver'] = ['id' => $row['approved_by'], 'nama' => $row['approver_nama'] ?? '-'];
            $row['items'] = $itemsByPengambilan[$row['id']] ?? [];
            $row['fotos'] = $photosByPengambilan[$row['id']] ?? [];
        }
    }

    private function savePickupPhotos(int $pengambilanId, array $items): void
    {
        $itemById = [];
        foreach ($items as $item) {
            $itemById[$item['id']] = $item;
        }

        foreach ($this->request->getFiles() as $field => $fileOrFiles) {
            $files = is_array($fileOrFiles) ? $fileOrFiles : [$fileOrFiles];
            foreach ($files as $file) {
                if (! preg_match('/^item_(\d+)_(.+)$/', (string) $field, $match) || ! $file->isValid() || $file->hasMoved()) {
                    continue;
                }
                if (! str_starts_with((string) $file->getMimeType(), 'image/') || $file->getSize() > self::MAX_PHOTO_SIZE) {
                    continue;
                }

                $itemId = (int) $match[1];
                $suffix = $match[2];
                $item = $itemById[$itemId] ?? null;
                $tipe = str_contains($suffix, 'kabel_ujung1') ? 'kabel_ujung1'
                    : (str_contains($suffix, 'kabel_ujung2') ? 'kabel_ujung2'
                    : (str_contains($suffix, 'kabel_roll') ? 'kabel_roll'
                    : (($item['aset']['tipe'] ?? '') === 'closure' ? 'closure' : 'odp')));

                $dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'pengambilan';
                if (! is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }

                $name = 'pengambilan-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . ($file->getClientExtension() ?: 'jpg');
                $file->move($dir, $name);
                (new FotoPengambilanModel())->insert([
                    'pengambilan_id' => $pengambilanId,
                    'item_id' => $itemId,
                    'foto_path' => '/uploads/pengambilan/' . $name,
                    'tipe' => $tipe,
                ]);
            }
        }
    }

    private function pagination(int $page, int $total): array
    {
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        return [
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
        ];
    }

    private function notifyAdmins(string $judul, string $pesan, string $tipe, string $link): void
    {
        $admins = (new UserModel())->where('role', 'admin')->findAll();
        foreach ($admins as $admin) {
            $this->notifyUser((int) $admin['id'], $judul, $pesan, $tipe, $link);
        }
    }

    private function checkLowStockAndNotify(int $asetId): void
    {
        $aset = db_connect()->table('aset_material a')
            ->select('a.*, g.nama AS gudang_nama')
            ->join('gudang g', 'g.id = a.gudang_id', 'left')
            ->where('a.id', $asetId)
            ->get()
            ->getRowArray();

        if (! $aset || ! $this->isLowStock($aset)) {
            return;
        }

        $stockInfo = $aset['tipe'] === 'kabel'
            ? "{$aset['stok_roll']} roll (min: {$aset['min_stok_roll']} roll)"
            : "{$aset['stok_pcs']} pcs (min: {$aset['min_stok_pcs']} pcs)";

        $notifModel = new NotifikasiModel();
        $link = '/admin/aset-material/detail/' . $asetId;

        foreach ((new UserModel())->where('role', 'admin')->findAll() as $admin) {
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

    private function isLowStock(array $aset): bool
    {
        if ($aset['tipe'] === 'kabel') {
            return (int) $aset['min_stok_roll'] > 0 && (float) $aset['stok_roll'] <= (int) $aset['min_stok_roll'];
        }

        return (int) $aset['min_stok_pcs'] > 0 && (int) $aset['stok_pcs'] <= (int) $aset['min_stok_pcs'];
    }

    private function notifyUser(int $userId, string $judul, string $pesan, string $tipe, string $link): void
    {
        (new NotifikasiModel())->insert([
            'user_id' => $userId,
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe' => $tipe,
            'link' => $link,
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
}

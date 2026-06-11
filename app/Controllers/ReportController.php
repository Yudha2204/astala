<?php

namespace App\Controllers;

use App\Libraries\SimplePdf;
use App\Libraries\DocxTemplatePdf;
use App\Libraries\BarangTemplatePdf;
use Throwable;

class ReportController extends BaseController
{
    public function exportInventoryFromTemplate()
    {
        $builder = db_connect()->table('barang')
            ->select('id, nama_barang, nomor_seri, kategori, status_kondisi, status_ketersediaan, lokasi_penyimpanan, created_at')
            ->orderBy('nama_barang', 'ASC');

        if ($this->request->getGet('status_kondisi')) {
            $builder->where('status_kondisi', $this->request->getGet('status_kondisi'));
        }
        if ($this->request->getGet('status_ketersediaan')) {
            $builder->where('status_ketersediaan', $this->request->getGet('status_ketersediaan'));
        }
        if ($search = trim((string) $this->request->getGet('search'))) {
            $builder->groupStart()->like('nama_barang', $search)->orLike('nomor_seri', $search)->groupEnd();
        }

        return $this->csv('laporan-inventaris', ['ID', 'Nama Barang', 'Nomor Seri', 'Kategori', 'Kondisi', 'Ketersediaan', 'Lokasi', 'Dibuat'], $builder->get()->getResultArray());
    }

    public function exportLoansFromTemplate()
    {
        $user = session('user');
        $builder = $this->loanBuilder()->where('p.user_id', $user['id'] ?? 0);
        $this->applyLoanFilters($builder);

        return $this->csv('laporan-peminjaman-saya', $this->loanHeaders(), $this->loanRows($builder->get()->getResultArray()));
    }

    public function exportAllLoansFromTemplate()
    {
        $builder = $this->loanBuilder();
        $this->applyLoanFilters($builder);

        return $this->csv('laporan-semua-peminjaman', $this->loanHeaders(), $this->loanRows($builder->get()->getResultArray()));
    }

    public function exportAsetMaterialFromTemplate()
    {
        $builder = db_connect()->table('aset_material a')
            ->select('a.id, g.nama AS gudang, a.tipe, a.nama, a.core, a.stok_roll, a.stok_meter, a.stok_pcs, a.min_stok_roll, a.min_stok_pcs, a.created_at')
            ->join('gudang g', 'g.id = a.gudang_id', 'left')
            ->orderBy('g.nama', 'ASC')
            ->orderBy('a.tipe', 'ASC')
            ->orderBy('a.nama', 'ASC');

        if ($this->request->getGet('gudang_id')) {
            $builder->where('a.gudang_id', $this->request->getGet('gudang_id'));
        }
        if ($this->request->getGet('tipe')) {
            $builder->where('a.tipe', $this->request->getGet('tipe'));
        }
        if ($search = trim((string) $this->request->getGet('search'))) {
            $builder->like('a.nama', $search);
        }

        $rows = $this->formatAsetMaterialRows($builder->get()->getResultArray());

        return $this->csv('laporan-aset-material', ['ID', 'Gudang', 'Tipe', 'Nama', 'Core', 'Min Roll', 'Min Pcs', 'Dibuat', 'Stok'], $rows);
    }

    public function exportPengambilanFromTemplate()
    {
        $builder = $this->pengambilanBuilder();
        $this->applyPengambilanFilters($builder);
        $rows = $builder->get()->getResultArray();
        $this->attachPengambilanItems($rows);

        return $this->csv('laporan-pengambilan', ['ID', 'Nama Mitra', 'User Mitra', 'Petugas', 'Gudang', 'Status', 'Keperluan', 'Tanggal Request', 'Tanggal Pickup', 'Tanggal Selesai', 'Items'], $rows);
    }

    public function exportLogsFromTemplate()
    {
        $builder = db_connect()->table('activity_logs l')
            ->select('l.id, u.nama AS user, u.email, l.action, l.description, l.entity_type, l.entity_id, l.ip_address, l.created_at')
            ->join('users u', 'u.id = l.user_id', 'left')
            ->orderBy('l.created_at', 'DESC');

        if ($this->request->getGet('action')) {
            $builder->where('l.action', $this->request->getGet('action'));
        }
        if ($search = trim((string) $this->request->getGet('search'))) {
            $builder->groupStart()
                ->like('l.description', $search)
                ->orLike('u.nama', $search)
                ->orLike('u.email', $search)
                ->groupEnd();
        }

        return $this->csv('laporan-log-aktivitas', ['ID', 'User', 'Email', 'Aksi', 'Deskripsi', 'Entity', 'Entity ID', 'IP Address', 'Waktu'], $builder->get()->getResultArray());
    }

    public function exportPDF(string $type)
    {
        $report = $this->reportData($type);
        if (! $report) {
            return redirect()->back()->with('error', 'Tipe laporan tidak dikenal');
        }

        if ($type === 'inventory') {
            $pdf = (new BarangTemplatePdf())->render($this->inventoryTemplateRows());

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="daftar-barang-' . date('Ymd-His') . '.pdf"')
                ->setBody($pdf);
        }

        try {
            $template = $this->templateReportData($type);
            if ($template !== null) {
                $result = (new DocxTemplatePdf())->render(
                    $template['template'],
                    $template['scalars'],
                    $template['blocks'],
                    $template['name'],
                    $template['imageFields'] ?? []
                );

                return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"')
                    ->setBody($result['bytes']);
            }
        } catch (Throwable $e) {
            log_message('error', 'DOCX template PDF failed: {message}', ['message' => $e->getMessage()]);
        }

        $pdf = (new SimplePdf())->render($report['title'], $report['headers'], $report['rows']);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $report['name'] . '-' . date('Ymd-His') . '.pdf"')
            ->setBody($pdf);
    }

    public function exportExcel(string $type)
    {
        return $this->exportByType($type);
    }

    private function exportByType(string $type)
    {
        return match ($type) {
            'inventory' => $this->exportInventoryFromTemplate(),
            'loans' => $this->exportAllLoansFromTemplate(),
            'logs' => $this->exportLogsFromTemplate(),
            'pengambilan' => $this->exportPengambilanFromTemplate(),
            'aset-material', 'aset_material' => $this->exportAsetMaterialFromTemplate(),
            default => redirect()->back()->with('error', 'Tipe laporan tidak dikenal'),
        };
    }

    private function reportData(string $type): ?array
    {
        return match ($type) {
            'inventory' => [
                'name' => 'laporan-inventaris',
                'title' => 'Laporan Inventaris',
                'headers' => ['ID', 'Nama Barang', 'Nomor Seri', 'Kategori', 'Kondisi', 'Ketersediaan', 'Lokasi', 'Dibuat'],
                'rows' => $this->inventoryRows(),
            ],
            'loans' => [
                'name' => 'laporan-semua-peminjaman',
                'title' => 'Laporan Semua Peminjaman',
                'headers' => $this->loanHeaders(),
                'rows' => $this->allLoanRows(),
            ],
            'my-loans' => [
                'name' => 'laporan-peminjaman-saya',
                'title' => 'Laporan Peminjaman Saya',
                'headers' => $this->loanHeaders(),
                'rows' => $this->ownLoanRows(),
            ],
            'logs' => [
                'name' => 'laporan-log-aktivitas',
                'title' => 'Laporan Log Aktivitas',
                'headers' => ['ID', 'User', 'Email', 'Aksi', 'Deskripsi', 'Entity', 'Entity ID', 'IP Address', 'Waktu'],
                'rows' => $this->logRows(),
            ],
            'pengambilan' => [
                'name' => 'laporan-pengambilan',
                'title' => 'Laporan Pengambilan Aset',
                'headers' => ['ID', 'Nama Mitra', 'User Mitra', 'Petugas', 'Gudang', 'Status', 'Keperluan', 'Request', 'Pickup', 'Selesai', 'Items'],
                'rows' => $this->pengambilanRows(),
            ],
            'aset-material', 'aset_material' => [
                'name' => 'laporan-aset-material',
                'title' => 'Laporan Aset Material',
                'headers' => ['ID', 'Gudang', 'Tipe', 'Nama', 'Core', 'Min Roll', 'Min Pcs', 'Dibuat', 'Stok'],
                'rows' => $this->asetMaterialRows(),
            ],
            default => null,
        };
    }

    private function templateReportData(string $type): ?array
    {
        return match ($type) {
            'inventory' => [
                'name' => 'laporan-inventaris',
                'template' => $this->templatePath('template-barang.docx'),
                'scalars' => [
                    'tanggal_cetak' => $this->formatDateTime(date('Y-m-d H:i:s')),
                    'user_cetak' => session('user')['nama'] ?? '-',
                    'total_barang' => count($this->inventoryTemplateRows()),
                ],
                'blocks' => ['barangs' => $this->inventoryTemplateRows()],
                'imageFields' => [
                    'barangs' => [
                        'foto' => ['width' => 100, 'height' => 100, 'ratio' => false],
                    ],
                ],
            ],
            'loans' => [
                'name' => 'laporan-semua-peminjaman',
                'template' => $this->templatePath('template-peminjaman-all.docx'),
                'scalars' => [
                    'tanggal_cetak' => $this->formatDateTime(date('Y-m-d H:i:s')),
                    'user_cetak' => session('user')['nama'] ?? '-',
                    'total_loans' => count($this->allLoanTemplateRows()),
                ],
                'blocks' => ['loans' => $this->allLoanTemplateRows()],
            ],
            'my-loans' => [
                'name' => 'laporan-peminjaman-saya',
                'template' => $this->templatePath('template-peminjaman.docx'),
                'scalars' => [
                    'tanggal_cetak' => $this->formatDateTime(date('Y-m-d H:i:s')),
                    'user_cetak' => session('user')['nama'] ?? '-',
                    'total_loans' => count($this->loanTemplateRows(true)),
                ],
                'blocks' => ['loans' => $this->loanTemplateRows(true)],
            ],
            'logs' => [
                'name' => 'laporan-log-aktivitas',
                'template' => $this->templatePath('template-log-aktivitas.docx'),
                'scalars' => [
                    'tanggal_cetak' => $this->formatDateTime(date('Y-m-d H:i:s')),
                    'user_cetak' => session('user')['nama'] ?? '-',
                ],
                'blocks' => ['logs' => $this->logTemplateRows()],
            ],
            'pengambilan' => [
                'name' => 'laporan-pengambilan',
                'template' => $this->templatePath('template-pengambilan.docx'),
                'scalars' => [
                    'tanggal_cetak' => $this->formatDateTime(date('Y-m-d H:i:s')),
                    'user_cetak' => session('user')['nama'] ?? '-',
                    'stat_request' => db_connect()->table('pengambilan_aset')->where('status', 'request')->countAllResults(),
                    'stat_waiting' => db_connect()->table('pengambilan_aset')->where('status', 'waiting')->countAllResults(),
                    'stat_confirmation' => db_connect()->table('pengambilan_aset')->where('status', 'confirmation')->countAllResults(),
                    'stat_done' => db_connect()->table('pengambilan_aset')->where('status', 'done')->countAllResults(),
                ],
                'blocks' => ['pengambilans' => $this->pengambilanTemplateRows()],
            ],
            'aset-material', 'aset_material' => [
                'name' => 'laporan-aset-material',
                'template' => $this->templatePath('template-aset-material.docx'),
                'scalars' => [
                    'tanggal_cetak' => $this->formatDateTime(date('Y-m-d H:i:s')),
                    'user_cetak' => session('user')['nama'] ?? '-',
                ],
                'blocks' => ['gudangs' => $this->asetMaterialTemplateRows()],
            ],
            default => null,
        };
    }

    private function templatePath(string $filename): string
    {
        return dirname(ROOTPATH) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $filename;
    }

    private function inventoryTemplateRows(): array
    {
        $rows = $this->inventoryRows();
        $photos = $this->firstBarangPhotos(array_column($rows, 'id'));

        return array_map(function (array $row, int $index) use ($photos): array {
            return [
                'no' => $index + 1,
                'foto' => $this->publicFilePath($photos[$row['id']] ?? null),
                'nama' => $row['nama_barang'] ?? '-',
                'sn' => $row['nomor_seri'] ?? '-',
                'kondisi' => ($row['status_kondisi'] ?? '') === 'baik' ? 'Baik' : ucfirst((string) ($row['status_kondisi'] ?? '-')),
                'ketersediaan' => ($row['status_ketersediaan'] ?? '') === 'tersedia' ? 'Tersedia' : ucfirst((string) ($row['status_ketersediaan'] ?? '-')),
                'lokasi' => $row['lokasi_penyimpanan'] ?? '-',
                'kategori' => $row['kategori'] ?? '-',
                'tanggal' => $this->formatDateTime($row['created_at'] ?? null),
            ];
        }, $rows, array_keys($rows));
    }

    private function allLoanTemplateRows(): array
    {
        return $this->loanTemplateRows(false);
    }

    private function loanTemplateRows(bool $ownOnly): array
    {
        $builder = $this->loanBuilder();
        if ($ownOnly) {
            $user = session('user');
            $builder->where('p.user_id', $user['id'] ?? 0);
        }
        $this->applyLoanFilters($builder);
        $rows = $builder->get()->getResultArray();

        return array_map(function (array $row, int $index): array {
            return [
                'no' => $index + 1,
                'nama_barang' => $row['nama_barang'] ?? '-',
                'sn' => $row['nomor_seri'] ?? '-',
                'lokasi' => $row['lokasi_peminjaman'] ?? '-',
                'tgl_pinjam' => $this->formatDateTime($row['tanggal_pinjam'] ?? null),
                'tgl_deadline' => $this->formatDateTime($row['tanggal_kembali_rencana'] ?? null),
                'tgl_kembali' => $this->formatDateTime($row['tanggal_kembali_aktual'] ?? null),
                'keperluan' => $row['keperluan'] ?? '-',
                'status' => strtoupper((string) ($row['status_peminjaman'] ?? '-')),
                'terlambat' => (int) ($row['is_late'] ?? 0) === 1 ? 'Terlambat' : '',
                'peminjam_nama' => $row['peminjam'] ?? '-',
                'peminjam_email' => $row['email'] ?? '-',
                'nama_peminjam' => $row['peminjam'] ?? '-',
                'email_peminjam' => $row['email'] ?? '-',
            ];
        }, $rows, array_keys($rows));
    }

    private function logTemplateRows(): array
    {
        $rows = $this->logRows();

        return array_map(function (array $row, int $index): array {
            return [
                'no' => $index + 1,
                'tanggal' => $this->formatDateTime($row['created_at'] ?? null),
                'user' => trim(($row['user'] ?? '-') . ' ' . ($row['email'] ? '(' . $row['email'] . ')' : '')),
                'aksi' => $row['action'] ?? '-',
                'deskripsi' => $row['description'] ?? '-',
                'ip' => $row['ip_address'] ?? '-',
            ];
        }, $rows, array_keys($rows));
    }

    private function pengambilanTemplateRows(): array
    {
        $rows = $this->pengambilanRows();

        return array_map(function (array $row, int $index): array {
            return [
                'no' => $index + 1,
                'mitra' => $row['mitra_user'] ?: ($row['nama_mitra'] ?? '-'),
                'petugas' => $row['nama_petugas'] ?? '-',
                'gudang' => $row['gudang'] ?? '-',
                'items_list' => $row['items'] ?? '-',
                'keterangan' => $row['deskripsi_keperluan'] ?? '-',
                'tanggal_request' => $this->formatDateTime($row['tanggal_request'] ?? null),
                'status' => strtoupper((string) ($row['status'] ?? '-')),
            ];
        }, $rows, array_keys($rows));
    }

    private function asetMaterialTemplateRows(): array
    {
        $rows = $this->asetMaterialRows();

        return array_map(static function (array $row, int $index): array {
            return [
                'nama_gudang' => $row['gudang'] ?? 'Tanpa Gudang',
                'no' => $index + 1,
                'nama' => $row['nama'] ?? '-',
                'tipe' => strtoupper((string) ($row['tipe'] ?? '-')),
                'core' => ($row['core'] ?? '-') . ' Core',
                'stok' => $row['stok'] ?? '-',
            ];
        }, $rows, array_keys($rows));
    }

    private function inventoryRows(): array
    {
        $builder = db_connect()->table('barang')
            ->select('id, nama_barang, nomor_seri, kategori, status_kondisi, status_ketersediaan, lokasi_penyimpanan, created_at')
            ->orderBy('nama_barang', 'ASC');

        if ($this->request->getGet('status_kondisi')) {
            $builder->where('status_kondisi', $this->request->getGet('status_kondisi'));
        }
        if ($this->request->getGet('status_ketersediaan')) {
            $builder->where('status_ketersediaan', $this->request->getGet('status_ketersediaan'));
        }
        if ($search = trim((string) $this->request->getGet('search'))) {
            $builder->groupStart()->like('nama_barang', $search)->orLike('nomor_seri', $search)->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    private function firstBarangPhotos(array $barangIds): array
    {
        $barangIds = array_values(array_filter(array_unique($barangIds)));
        if ($barangIds === []) {
            return [];
        }

        $rows = db_connect()->table('foto_barang')
            ->select('barang_id, foto_path')
            ->whereIn('barang_id', $barangIds)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $photos = [];
        foreach ($rows as $row) {
            if (! isset($photos[$row['barang_id']])) {
                $photos[$row['barang_id']] = $row['foto_path'];
            }
        }

        return $photos;
    }

    private function publicFilePath(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        $relative = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
        $fullPath = FCPATH . $relative;

        return is_file($fullPath) ? $fullPath : null;
    }

    private function formatDateTime(?string $date): string
    {
        if (! $date) {
            return '-';
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return '-';
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return date('d', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp)
            . ' pukul ' . date('H.i', $timestamp);
    }

    private function allLoanRows(): array
    {
        $builder = $this->loanBuilder();
        $this->applyLoanFilters($builder);

        return $this->loanRows($builder->get()->getResultArray());
    }

    private function ownLoanRows(): array
    {
        $user = session('user');
        $builder = $this->loanBuilder()->where('p.user_id', $user['id'] ?? 0);
        $this->applyLoanFilters($builder);

        return $this->loanRows($builder->get()->getResultArray());
    }

    private function asetMaterialRows(): array
    {
        $builder = db_connect()->table('aset_material a')
            ->select('a.id, g.nama AS gudang, a.tipe, a.nama, a.core, a.stok_roll, a.stok_meter, a.stok_pcs, a.min_stok_roll, a.min_stok_pcs, a.created_at')
            ->join('gudang g', 'g.id = a.gudang_id', 'left')
            ->orderBy('g.nama', 'ASC')
            ->orderBy('a.tipe', 'ASC')
            ->orderBy('a.nama', 'ASC');

        if ($this->request->getGet('gudang_id')) {
            $builder->where('a.gudang_id', $this->request->getGet('gudang_id'));
        }
        if ($this->request->getGet('tipe')) {
            $builder->where('a.tipe', $this->request->getGet('tipe'));
        }
        if ($search = trim((string) $this->request->getGet('search'))) {
            $builder->like('a.nama', $search);
        }

        return $this->formatAsetMaterialRows($builder->get()->getResultArray());
    }

    private function formatAsetMaterialRows(array $rows): array
    {
        return array_map(static function (array $row): array {
            $row['stok'] = $row['tipe'] === 'kabel'
                ? $row['stok_roll'] . ' roll / ' . $row['stok_meter'] . ' meter'
                : $row['stok_pcs'] . ' pcs';
            unset($row['stok_roll'], $row['stok_meter'], $row['stok_pcs']);

            return $row;
        }, $rows);
    }

    private function pengambilanBuilder()
    {
        return db_connect()->table('pengambilan_aset p')
            ->select('p.id, p.nama_mitra, u.nama AS mitra_user, p.nama_petugas, g.nama AS gudang, p.status, p.deskripsi_keperluan, p.tanggal_request, p.tanggal_pickup, p.tanggal_done')
            ->join('users u', 'u.id = p.mitra_id', 'left')
            ->join('gudang g', 'g.id = p.gudang_id', 'left')
            ->orderBy('p.created_at', 'DESC');
    }

    private function applyPengambilanFilters($builder): void
    {
        if ($this->request->getGet('status')) {
            $builder->where('p.status', $this->request->getGet('status'));
        }
        if ($this->request->getGet('mitra_id')) {
            $builder->where('p.mitra_id', $this->request->getGet('mitra_id'));
        }
        if ($search = trim((string) $this->request->getGet('search'))) {
            $builder->groupStart()
                ->like('p.nama_mitra', $search)
                ->orLike('u.nama', $search)
                ->orLike('p.nama_petugas', $search)
                ->orLike('g.nama', $search)
                ->groupEnd();
        }
    }

    private function pengambilanRows(): array
    {
        $builder = $this->pengambilanBuilder();
        $this->applyPengambilanFilters($builder);
        $rows = $builder->get()->getResultArray();
        $this->attachPengambilanItems($rows);

        return $rows;
    }

    private function logRows(): array
    {
        $builder = db_connect()->table('activity_logs l')
            ->select('l.id, u.nama AS user, u.email, l.action, l.description, l.entity_type, l.entity_id, l.ip_address, l.created_at')
            ->join('users u', 'u.id = l.user_id', 'left')
            ->orderBy('l.created_at', 'DESC');

        if ($this->request->getGet('action')) {
            $builder->where('l.action', $this->request->getGet('action'));
        }
        if ($search = trim((string) $this->request->getGet('search'))) {
            $builder->groupStart()
                ->like('l.description', $search)
                ->orLike('u.nama', $search)
                ->orLike('u.email', $search)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    private function loanBuilder()
    {
        return db_connect()->table('peminjaman p')
            ->select('p.id, u.nama AS peminjam, u.email, b.nama_barang, b.nomor_seri, p.lokasi_peminjaman, p.keperluan, p.tanggal_pinjam, p.tanggal_kembali_rencana, p.tanggal_kembali_aktual, p.status_peminjaman, p.is_late, p.created_at')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('barang b', 'b.id = p.barang_id', 'left')
            ->orderBy('p.created_at', 'DESC');
    }

    private function applyLoanFilters($builder): void
    {
        if ($this->request->getGet('status')) {
            $builder->where('p.status_peminjaman', $this->request->getGet('status'));
        }
        if ($this->request->getGet('is_late') === 'true') {
            $builder->where('p.is_late', 1);
        } elseif ($this->request->getGet('is_late') === 'false') {
            $builder->where('p.is_late', 0);
        }
        if ($this->request->getGet('start_date') && $this->request->getGet('end_date')) {
            $builder->where('p.created_at >=', $this->request->getGet('start_date') . ' 00:00:00')
                ->where('p.created_at <=', $this->request->getGet('end_date') . ' 23:59:59');
        }
        if ($search = trim((string) $this->request->getGet('search'))) {
            $builder->groupStart()
                ->like('u.nama', $search)
                ->orLike('b.nama_barang', $search)
                ->orLike('b.nomor_seri', $search)
                ->groupEnd();
        }
    }

    private function loanHeaders(): array
    {
        return ['ID', 'Peminjam', 'Email', 'Barang', 'Nomor Seri', 'Lokasi', 'Keperluan', 'Tanggal Pinjam', 'Deadline', 'Tanggal Kembali', 'Status', 'Terlambat', 'Dibuat'];
    }

    private function loanRows(array $rows): array
    {
        return array_map(static function (array $row): array {
            $row['is_late'] = (int) $row['is_late'] === 1 ? 'Ya' : 'Tidak';

            return $row;
        }, $rows);
    }

    private function attachPengambilanItems(array &$rows): void
    {
        if (! $rows) {
            return;
        }

        $ids = array_column($rows, 'id');
        $items = db_connect()->table('pengambilan_item i')
            ->select('i.pengambilan_id, a.nama, a.tipe, a.core, i.jumlah_roll, i.jumlah_meter, i.jumlah_pcs')
            ->join('aset_material a', 'a.id = i.aset_id', 'left')
            ->whereIn('i.pengambilan_id', $ids)
            ->orderBy('i.id', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];
        foreach ($items as $item) {
            $qty = $item['tipe'] === 'kabel'
                ? $item['jumlah_roll'] . ' roll / ' . $item['jumlah_meter'] . ' meter'
                : $item['jumlah_pcs'] . ' pcs';
            $grouped[$item['pengambilan_id']][] = trim(($item['nama'] ?? '-') . ' (' . strtoupper((string) $item['tipe']) . ', ' . ($item['core'] ?: '-') . ' core) - ' . $qty);
        }

        foreach ($rows as &$row) {
            $row['items'] = implode("\n", $grouped[$row['id']] ?? []);
        }
    }

    private function csv(string $name, array $headers, array $rows)
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, array_map(static fn ($value): string => (string) ($value ?? ''), array_values($row)));
        }

        rewind($handle);
        $csv = "\xEF\xBB\xBF" . stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $name . '-' . date('Ymd-His') . '.csv"')
            ->setBody($csv);
    }
}

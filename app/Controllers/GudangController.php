<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\GudangModel;

class GudangController extends BaseController
{
    public function index()
    {
        $gudangs = (new GudangModel())->orderBy('nama', 'ASC')->findAll();
        $counts = db_connect()->table('aset_material')
            ->select('gudang_id, COUNT(*) AS total')
            ->groupBy('gudang_id')
            ->get()
            ->getResultArray();

        $countByGudang = array_column($counts, 'total', 'gudang_id');
        foreach ($gudangs as &$gudang) {
            $gudang['aset_count'] = (int) ($countByGudang[$gudang['id']] ?? 0);
        }

        return view('admin/gudang/index', [
            'title' => 'Manajemen Gudang - ASTALA',
            'user' => session('user'),
            'gudangs' => $gudangs,
        ]);
    }

    public function showAdd()
    {
        return view('admin/gudang/form', [
            'title' => 'Tambah Gudang - ASTALA',
            'user' => session('user'),
            'gudang' => null,
        ]);
    }

    public function add()
    {
        $payload = $this->payload();
        $id = (int) (new GudangModel())->insert($payload, true);
        $this->logActivity('CREATE_GUDANG', 'Menambahkan gudang: ' . $payload['nama'], 'gudang', $id);

        return redirect()->to('/admin/gudang')->with('success', 'Gudang berhasil ditambahkan');
    }

    public function showEdit(int $id)
    {
        $gudang = (new GudangModel())->find($id);

        if (! $gudang) {
            return redirect()->to('/admin/gudang')->with('error', 'Gudang tidak ditemukan');
        }

        return view('admin/gudang/form', [
            'title' => 'Edit Gudang - ASTALA',
            'user' => session('user'),
            'gudang' => $gudang,
        ]);
    }

    public function update(int $id)
    {
        $gudangModel = new GudangModel();
        $gudang = $gudangModel->find($id);

        if (! $gudang) {
            return redirect()->to('/admin/gudang')->with('error', 'Gudang tidak ditemukan');
        }

        $payload = $this->payload();
        $gudangModel->update($id, $payload);
        $this->logActivity('UPDATE_GUDANG', 'Mengubah gudang: ' . $payload['nama'], 'gudang', $id);

        return redirect()->to('/admin/gudang')->with('success', 'Gudang berhasil diperbarui');
    }

    public function delete(int $id)
    {
        $gudangModel = new GudangModel();
        $gudang = $gudangModel->find($id);

        if (! $gudang) {
            return redirect()->to('/admin/gudang')->with('error', 'Gudang tidak ditemukan');
        }

        $asetCount = db_connect()->table('aset_material')->where('gudang_id', $id)->countAllResults();
        if ($asetCount > 0) {
            return redirect()->to('/admin/gudang')->with('error', 'Gudang masih memiliki aset material');
        }

        $gudangModel->delete($id);
        $this->logActivity('DELETE_GUDANG', 'Menghapus gudang: ' . $gudang['nama'], 'gudang', $id);

        return redirect()->to('/admin/gudang')->with('success', 'Gudang berhasil dihapus');
    }

    private function payload(): array
    {
        return [
            'nama' => trim((string) $this->request->getPost('nama')),
            'lokasi' => trim((string) $this->request->getPost('lokasi')) ?: null,
            'tipe' => $this->request->getPost('tipe') === 'outdoor' ? 'outdoor' : 'indoor',
            'deskripsi' => trim((string) $this->request->getPost('deskripsi')) ?: null,
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

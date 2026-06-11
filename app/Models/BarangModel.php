<?php

namespace App\Models;

class BarangModel extends AstalaModel
{
    protected $table = 'barang';

    protected $allowedFields = [
        'nama_barang',
        'nomor_seri',
        'deskripsi',
        'kategori',
        'lokasi_penyimpanan',
        'status_kondisi',
        'status_ketersediaan',
        'wajib_qr',
    ];

    protected array $casts = [
        'wajib_qr' => 'boolean',
    ];
}

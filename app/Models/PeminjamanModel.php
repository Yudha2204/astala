<?php

namespace App\Models;

class PeminjamanModel extends AstalaModel
{
    protected $table = 'peminjaman';

    protected $allowedFields = [
        'user_id',
        'barang_id',
        'lokasi_peminjaman',
        'keperluan',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali_aktual',
        'status_peminjaman',
        'is_late',
        'reminder_sent',
    ];

    protected array $casts = [
        'is_late' => 'boolean',
        'reminder_sent' => 'boolean',
    ];
}

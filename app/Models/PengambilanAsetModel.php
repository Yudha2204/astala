<?php

namespace App\Models;

class PengambilanAsetModel extends AstalaModel
{
    protected $table = 'pengambilan_aset';

    protected $allowedFields = [
        'mitra_id',
        'gudang_id',
        'status',
        'nama_mitra',
        'nama_petugas',
        'deskripsi_keperluan',
        'alasan_penolakan',
        'tanggal_request',
        'tanggal_approval',
        'tanggal_pickup',
        'tanggal_done',
        'ttd_petugas',
        'ttd_admin',
        'approved_by',
    ];
}

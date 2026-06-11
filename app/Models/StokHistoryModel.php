<?php

namespace App\Models;

class StokHistoryModel extends AstalaModel
{
    protected $table = 'stok_history';

    protected $allowedFields = [
        'aset_id',
        'tipe_aktivitas',
        'jumlah_roll',
        'jumlah_meter',
        'jumlah_pcs',
        'pengambilan_id',
        'foto_path',
        'keterangan',
        'created_by',
    ];
}

<?php

namespace App\Models;

class FotoPeminjamanModel extends AstalaModel
{
    protected $table = 'foto_peminjaman';

    protected $allowedFields = [
        'peminjaman_id',
        'foto_path',
        'tipe',
        'barcode_detected',
    ];
}

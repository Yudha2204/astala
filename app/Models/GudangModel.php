<?php

namespace App\Models;

class GudangModel extends AstalaModel
{
    protected $table = 'gudang';

    protected $allowedFields = [
        'nama',
        'lokasi',
        'tipe',
        'deskripsi',
    ];
}

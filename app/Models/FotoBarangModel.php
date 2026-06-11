<?php

namespace App\Models;

class FotoBarangModel extends AstalaModel
{
    protected $table = 'foto_barang';

    protected $allowedFields = [
        'barang_id',
        'foto_path',
        'urutan',
    ];
}

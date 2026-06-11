<?php

namespace App\Models;

class AsetMaterialModel extends AstalaModel
{
    protected $table = 'aset_material';

    protected $allowedFields = [
        'gudang_id',
        'tipe',
        'nama',
        'core',
        'stok_roll',
        'stok_meter',
        'meter_per_roll',
        'stok_pcs',
        'deskripsi',
        'min_stok_roll',
        'min_stok_pcs',
    ];
}

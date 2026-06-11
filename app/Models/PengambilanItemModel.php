<?php

namespace App\Models;

class PengambilanItemModel extends AstalaModel
{
    protected $table = 'pengambilan_item';

    protected $allowedFields = [
        'pengambilan_id',
        'aset_id',
        'jumlah_roll',
        'jumlah_meter',
        'jumlah_pcs',
    ];
}

<?php

namespace App\Models;

class FotoPengambilanModel extends AstalaModel
{
    protected $table = 'foto_pengambilan';

    protected $allowedFields = [
        'pengambilan_id',
        'item_id',
        'foto_path',
        'tipe',
    ];
}

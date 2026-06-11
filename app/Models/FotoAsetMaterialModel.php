<?php

namespace App\Models;

class FotoAsetMaterialModel extends AstalaModel
{
    protected $table = 'foto_aset_material';

    protected $allowedFields = [
        'aset_id',
        'foto_path',
        'is_primary',
    ];

    protected array $casts = [
        'is_primary' => 'boolean',
    ];
}

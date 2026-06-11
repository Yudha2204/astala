<?php

namespace App\Models;

class NotifikasiModel extends AstalaModel
{
    protected $table = 'notifikasi';

    protected $allowedFields = [
        'user_id',
        'judul',
        'pesan',
        'tipe',
        'link',
        'is_read',
    ];

    protected array $casts = [
        'is_read' => 'boolean',
    ];
}

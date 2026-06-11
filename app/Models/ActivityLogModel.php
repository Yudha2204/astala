<?php

namespace App\Models;

class ActivityLogModel extends AstalaModel
{
    protected $table = 'activity_logs';

    protected $allowedFields = [
        'user_id',
        'action',
        'description',
        'entity_type',
        'entity_id',
        'ip_address',
        'user_agent',
    ];
}

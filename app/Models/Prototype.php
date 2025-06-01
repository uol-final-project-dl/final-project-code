<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $description
 * @property string $uuid
 */
class Prototype extends Model
{
    protected $table = 'prototypes';

    protected $fillable = [
        'user_id',
        'uuid',
        'description',
        'status',
        'bundle',
        'log'
    ];
}

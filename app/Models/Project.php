<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $description
 * @property string $uuid
 */
class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'stage',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $status
 * @property string $type
 * @property string $filename
 * @property int $id
 */
class ProjectDocument extends Model
{
    protected $table = 'project_documents';

    protected $fillable = [
        'project_id',
        'filename',
        'type',
        'status',
        'content',
        'error_message'
    ];

    protected $hidden = [
        'content',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

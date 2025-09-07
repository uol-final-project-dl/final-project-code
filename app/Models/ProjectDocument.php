<?php

namespace App\Models;

use App\Traits\HasSelfCasting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $status
 * @property string $type
 * @property string $filename
 * @property int $id
 * @property string $content
 * @property Project $project
 */
class ProjectDocument extends Model
{
    use HasSelfCasting, HasFactory;

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

<?php

namespace App\Models;

use App\Traits\HasSelfCasting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $content
 * @property string $name
 * @property string $path
 * @property string $type
 * @property string $summary
 * @property int $id
 * @property int $project_id
 */
class CodeFile extends Model
{
    use HasSelfCasting;

    protected $table = 'code_files';

    protected $fillable = [
        'project_id',
        'name',
        'path',
        'type',
        'summary',
        'content',
    ];

    protected $hidden = [
        'content',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

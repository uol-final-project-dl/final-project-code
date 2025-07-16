<?php

namespace App\Models;

use App\Traits\HasSelfCasting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $status
 * @property string $type
 * @property string $filename
 * @property int $id
 * @property string $content
 */
class ProjectIdea extends Model
{
    use HasSelfCasting;

    protected $table = 'project_ideas';

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'ranking',
        'status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

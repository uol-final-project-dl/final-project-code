<?php

namespace App\Models;

use App\Traits\HasSelfCasting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $status
 * @property string $title
 * @property string $description
 * @property int $ranking
 * @property int $id
 * @property Project $project
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

    public function prototypes(): HasMany
    {
        return $this->hasMany(Prototype::class);
    }
}

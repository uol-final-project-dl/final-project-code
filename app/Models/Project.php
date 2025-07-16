<?php

namespace App\Models;

use App\Traits\HasSelfCasting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $description
 * @property int $id
 */
class Project extends Model
{
    use HasSelfCasting;

    protected $table = 'projects';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'stage',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project_documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function project_ideas(): HasMany
    {
        return $this->hasMany(ProjectIdea::class);
    }
}

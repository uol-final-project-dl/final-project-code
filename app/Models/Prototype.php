<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\HasSelfCasting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $title
 * @property string $description
 * @property string $uuid
 * @property mixed $status
 * @property ProjectIdea $project_idea
 * @property int $user_id
 * @property User $user
 * @property mixed $log
 */
class Prototype extends Model
{
    use HasSelfCasting;

    protected $table = 'prototypes';

    protected $fillable = [
        'type',
        'user_id',
        'project_idea_id',
        'uuid',
        'title',
        'description',
        'status',
        'bundle',
        'log'
    ];

    public function project_idea(): BelongsTo
    {
        return $this->belongsTo(ProjectIdea::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::updated(static function (self $prototype): void {
            // Only act when status just changed to READY
            if ($prototype->status !== StatusEnum::READY->value || !$prototype->wasChanged('status')) {
                return;
            }

            $project = $prototype->project_idea->project;

            $allReady = !$project
                ->project_ideas()
                ->whereHas(
                    'prototypes',
                    function ($q) {
                        $q->whereNotIn('status', [StatusEnum::READY->value, StatusEnum::FAILED->value]);
                    }
                )
                ->exists();

            if ($allReady) {
                $project->update(['status' => StatusEnum::READY->value]);
            }
        });
    }
}

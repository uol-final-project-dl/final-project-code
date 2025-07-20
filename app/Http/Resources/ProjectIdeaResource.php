<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $title
 * @property mixed $description
 * @property mixed $status
 * @property mixed $ranking
 * @property mixed $created_at
 * @property mixed $updated_at
 * @method relationLoaded(string $string)
 * @method load(string $string)
 */
class ProjectIdeaResource extends JsonResource
{
    public function toArray($request): array
    {
        if (!$this->relationLoaded('prototypes')) {
            $this->load('prototypes');
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'prototypes' => PrototypeResource::collection($this->whenLoaded('prototypes')),
            'ranking' => $this->ranking,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $name
 * @property mixed $status
 * @property mixed $stage
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $description
 * @property mixed $user_id
 * @property mixed $style_config
 * @method relationLoaded(string $string)
 * @method load(string $string)
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Ensure that the 'project_documents' relationship is loaded
        if (!$this->relationLoaded('project_documents')) {
            $this->load('project_documents');
        }

        if (!$this->relationLoaded('project_ideas')) {
            $this->load('project_ideas');
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'stage' => $this->stage,
            'description' => $this->description,
            'style_config' => $this->style_config,
            'user_id' => $this->user_id,
            'project_documents' => ProjectDocumentResource::collection($this->whenLoaded('project_documents')),
            'project_ideas' => ProjectIdeaResource::collection($this->whenLoaded('project_ideas')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}



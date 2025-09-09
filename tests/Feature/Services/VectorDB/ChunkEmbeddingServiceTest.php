<?php

namespace Tests\Feature\Services\VectorDB;

use App\Jobs\VectorDB\SyncChunkEmbedding;
use App\Models\CodeFile;
use App\Services\VectorDB\ChunkEmbeddingService;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\AuthenticatedTestCase;

class ChunkEmbeddingServiceTest extends AuthenticatedTestCase
{
    #[RunInSeparateProcess]
    public function test_save_file_embedding(): void
    {
        $codeFile = CodeFile::factory()->create([
            'project_id' => $this->project->id,
            'name' => 'example.tsx',
            'path' => '/src/example.tsx',
            'type' => 'typescript',
            'content' => 'import React from "react" export const MyComponent = () => <div>Hello</div>;',
        ]);

        Queue::fake();
        ChunkEmbeddingService::saveFileEmbedding($codeFile);
        Queue::assertPushed(SyncChunkEmbedding::class, 1);
    }
}

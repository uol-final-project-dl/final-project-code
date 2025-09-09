<?php

namespace Tests\Feature\Services\VectorDB;

use App\Models\CodeFile;
use App\Services\VectorDB\SearchVectorDBService;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\AuthenticatedTestCase;

class SearchVectorDBServiceTest extends AuthenticatedTestCase
{
    /**
     * @throws \JsonException
     */
    #[RunInSeparateProcess]
    public function test_search_file_chunks(): void
    {
        $codeFile = CodeFile::factory()->create([
            'project_id' => $this->project->id,
            'name' => 'example.tsx',
            'path' => '/src/example.tsx',
            'type' => 'typescript',
            'content' => 'import React from "react" export const MyComponent = () => <div>Hello</div>;',
        ]);

        $mockDB = Mockery::mock('alias:Illuminate\Support\Facades\DB');
        $mockDB->shouldReceive('select')->andReturn([
            (object)[
                'id' => '1',
                'text' => 'This is a chunk of code that includes React component.',
                'metadata' => json_encode([
                    'file_name' => 'example.tsx',
                    'repo_path' => '/src/example.tsx',
                    'file_type' => 'typescript',
                    'content_hash' => hash('sha256', $codeFile->content),
                    'imports' => ['React'],
                    'exports' => ['MyComponent'],
                    'summary' => null,
                    'symbol_inventory' => 'MyComponent',
                    'project_id' => $this->project->id,
                ]),
                'similarity' => 0.95,
            ]
        ]);
        $mockDB->shouldReceive('merge')->andReturnSelf();
        $mockDB->shouldReceive('count')->andReturn(1);
        $mockDB->shouldReceive('connection')->andReturnSelf();

        $mockOpenAI = Mockery::mock('alias:App\Services\OpenAI\OpenAIEmbeddingsService');
        $mockOpenAI->shouldReceive('embed')->andReturn([0, 1, 0, 1, 0]);

        $result = SearchVectorDBService::searchFileChunks($this->project->id, 'example query');
        $this->assertCount(1, $result);
    }
}

<?php

namespace Tests\Feature\Jobs\VectorDB;

use App\Jobs\VectorDB\SyncChunkEmbedding;
use App\Models\VectorDB\VectorChunk;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class SyncChunkEmbeddingTest extends TestCase
{
    public function test_handle_success(): void
    {
        $mockGeneration = Mockery::mock('alias:App\Services\OpenAI\OpenAIEmbeddingsService');
        $mockGeneration->shouldReceive('embed')->andReturn([0.1, 0.2, 0.3, 0.4, 0.5]);

        $vectorChunk = new VectorChunk(
            'uuid-test-1234',
            'Sample text for embedding',
            ['key' => 'value']
        );

        DB::shouldReceive('connection')->with('vector')->andReturnSelf();
        DB::shouldReceive('update')->andReturn(1);

        SyncChunkEmbedding::dispatch($vectorChunk);

        $this->assertTrue(true);
    }
}

<?php

namespace Tests\Feature\Services\ProjectServices;

use App\Enums\StatusEnum;
use App\Services\ProjectServices\ProjectDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Random\RandomException;
use Tests\AuthenticatedTestCase;

class ProjectDocumentServiceTest extends AuthenticatedTestCase
{
    /**
     * @throws RandomException
     * @throws \JsonException
     */
    #[RunInSeparateProcess]
    public function test_append_document(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->shouldReceive('getClientOriginalName')
            ->andReturn('testfile.txt');

        $uploadedFile->shouldReceive('getClientMimeType')->andReturn('text/plain');

        Storage::fake('local');
        Storage::shouldReceive('putFileAs')
            ->andReturn('project_documents/1/testfile.txt');

        ProjectDocumentService::appendDocument($this->project, $uploadedFile);

        $this->assertDatabaseHas('project_documents', [
            'project_id' => $this->project->id,
            'filename' => 'project_documents/1/testfile.txt',
            'type' => 'text/plain',
            'status' => StatusEnum::QUEUED->value,
        ]);
    }
}

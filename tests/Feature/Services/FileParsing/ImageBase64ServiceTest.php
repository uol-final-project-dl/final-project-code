<?php

namespace Tests\Feature\Services\FileParsing;

use App\Models\ProjectDocument;
use App\Services\FileParsing\ImageBase64Service;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\AuthenticatedTestCase;

class ImageBase64ServiceTest extends AuthenticatedTestCase
{

    #[RunInSeparateProcess]
    public function test_base64_documents_from_project(): void
    {
        $filePath = base_path('tests/sample.png');
        $fileContent = 'test file content';
        Storage::fake('local');
        Storage::put($filePath, $fileContent);

        ProjectDocument::factory()->create([
            'project_id' => $this->project->id,
            'type' => 'image/png',
            'filename' => $filePath,
        ]);

        $fileBase64 = base64_encode($fileContent);

        $this->project->refresh();
        $array = ImageBase64Service::base64DocumentsFromProject($this->project);
        $this->assertCount(1, $array);
        $this->assertArrayHasKey('base64', $array[0]);
        $this->assertEquals($fileBase64, $array[0]['base64']);
        $this->assertTrue(true);
    }
}

<?php

namespace Tests\Feature\Services\FileParsing;

use App\Services\FileParsing\FileParsingService;
use Tests\AuthenticatedTestCase;

class FileParsingServiceTest extends AuthenticatedTestCase
{

    public function test_parse_file(): void
    {
        FileParsingService::parseFile($this->project->id, 'var/path/to/file.js', 'console.log("Hello, World!");');
        $this->assertTrue(true);
    }
}

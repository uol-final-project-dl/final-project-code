<?php

namespace App\Services\ProjectServices;

use App\Enums\StatusEnum;
use App\Models\Project;
use App\Traits\HasMakeAble;
use Illuminate\Support\Facades\Storage;
use Random\RandomException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProjectDocumentService
{
    use HasMakeAble;

    /**
     * @throws RandomException
     */
    public static function createRandomFilename(string $fileName, string $uniqueIdentifier): string
    {
        return round(microtime(true) * 1000) . '_' . random_int(100000, 999999) . '_' . $uniqueIdentifier . '_' . $fileName;
    }

    /**
     * @throws \JsonException
     * @throws RandomException
     */
    public static function appendDocument(Project $project, UploadedFile $file): void
    {
        $fileName = $file->getClientOriginalName();
        $fileName = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $fileName);
        $count = $project->project_documents()->count() + 1;
        $path = self::createRandomFilename($fileName, $count . '_documents');

        $storedFileRoute = Storage::putFileAs(
            'project_documents/' . $project->id,
            $file,
            $path
        );

        $project->project_documents()->create([
            'filename' => $storedFileRoute,
            'type' => $file->getClientMimeType(),
            'status' => StatusEnum::QUEUED->value,
        ]);
    }
}

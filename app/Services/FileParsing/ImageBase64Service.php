<?php

namespace App\Services\FileParsing;

use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageBase64Service
{
    public static function base64DocumentsFromProject(Project $project): array
    {
        $imageDocuments = $project->project_documents()->where('type', 'like', '%image%')->get();

        $results = [];

        foreach ($imageDocuments as $imageDocument) {
            $fileContent = Storage::get($imageDocument->filename);
            if (!$fileContent) {
                continue;
            }
            $tmpFilePath = storage_path('app/tmp/' . Str::uuid() . '_project_document_' . $imageDocument->id . '.imageForParsing');
            file_put_contents($tmpFilePath, $fileContent);

            $results[] = [
                'base64' => base64_encode(file_get_contents($tmpFilePath)),
                'mimeType' => mime_content_type($tmpFilePath),
            ];
        }

        return $results;
    }
}

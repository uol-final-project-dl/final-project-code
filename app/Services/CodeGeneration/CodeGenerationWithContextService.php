<?php

namespace App\Services\CodeGeneration;

use App\Models\Project;
use App\Services\FileParsing\ImageBase64Service;
use App\Services\LLM\LLMCompletionService;
use App\Services\VectorDB\SearchVectorDBService;
use App\Traits\HasMakeAble;
use Illuminate\Support\Collection;

class CodeGenerationWithContextService
{
    use HasMakeAble;

    private function buildMessages(string $userPrompt, Collection $chunks): array
    {
        $context = $chunks
            ->values()
            ->map(function ($chunk, $idx) {
                $metadata = json_decode($chunk->metadata, true, 512, JSON_THROW_ON_ERROR);
                $path = ($metadata['repo_path'] . '/' . $metadata['file_name']) ?? 'unknown';

                $number = $idx + 1;

                return "### Chunk[{$number}] {$path}\n"
                    . "```code\n{$chunk->content}\n```";
            })
            ->implode("\n\n");

        $system = <<<SYS
        You are an expert full-stack engineer that can generate code based on a user prompt and a set of context files.
        Respond **only** with a JSON array. Each element:
        {
          "repo_path": "<relative/path/to/file>",
          "action": "modify" | "create",
          "content": "<complete new file content>"
        }

        Rules:
        - Do not add explanations or markdown outside the JSON.
        - Each file must be a complete file, not just the diff.
        - Each file must be a valid code file, ready to be used in the repository.
        - Use the context provided to understand the existing code.
        - If you lack necessary context, respond instead with:
          { "NEED_FILE": "<repo_path>" }
        SYS;

        $user = <<<USR
        CODING REQUEST:
        {$userPrompt}

        CONTEXT FILES (read-only):
        {$context}
        USR;

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
    }

    /**
     * @throws \JsonException
     * @throws \Exception
     */
    public function generateCode(Project $project, string $provider, string $userPrompt): array
    {
        $chunks = SearchVectorDBService::searchFileChunks($project->id, $userPrompt);

        $messages = $this->buildMessages($userPrompt, $chunks);
        $images = ImageBase64Service::base64DocumentsFromProject($project);

        return LLMCompletionService::chat($provider, [
            'model' => 'coding',
            'temperature' => 0.3,
            'messages' => $messages,
            'max_tokens' => 16000,
        ], $images);
    }
}

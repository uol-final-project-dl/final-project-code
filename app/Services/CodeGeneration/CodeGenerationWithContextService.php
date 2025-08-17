<?php

namespace App\Services\CodeGeneration;

use App\Services\LLM\LLMCompletionService;
use App\Services\VectorDB\SearchVectorDBService;
use App\Traits\HasMakeAble;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
                    . "```text\n{$chunk->content}\n```";
            })
            ->implode("\n\n");

        $system = <<<SYS
        You are an expert full-stack engineer.
        Respond **only** with a JSON array. Each element:
        {
          "repo_path": "<relative/path/to/file>",
          "action": "modify" | "create",
          "content": "<complete new file content>"
        }

        Rules:
        - Do not add explanations or markdown outside the JSON.
        - Each file must be a complete file, not just a diff.
        - Each file must be a valid code file, ready to be used in the repository.
        - Use the context provided to understand the existing code.
        - If you lack necessary context, respond instead with:
          { "NEED_FILE": "<repo_path>" }
        SYS;

        $user = <<<USR
        CONTEXT:
        {$context}

        CODING REQUEST:
        {$userPrompt}
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
    public function generateCode(string $provider, string $userPrompt): array
    {
        $chunks = SearchVectorDBService::searchFileChunks($userPrompt);

        $messages = $this->buildMessages($userPrompt, $chunks);

        $resp = LLMCompletionService::chat($provider, [
            'model' => 'coding',
            'temperature' => 0.2,
            'messages' => $messages,
            'max_tokens' => 16000,
        ]);

        $content = $resp['choices'][0]['message']['content'] ?? '';

        Log::info('LLM usage', $resp['usage'] ?? []);

        return [
            'answer' => $content,
            'sources' => $chunks,
            'usage' => $resp['usage'] ?? null,
        ];
    }
}

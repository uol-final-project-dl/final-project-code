<?php

namespace App\Services\CodeGeneration;

use App\Services\VectorDB\SearchVectorDBService;
use App\Traits\HasMakeAble;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class CodeGenerationWithContextService
{
    use HasMakeAble;

    private function buildMessages(string $userPrompt, Collection $chunks): array
    {
        $context = $chunks
            ->values()                                      // preserve ranking order
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
     */
    public function generateCode(string $userPrompt): array
    {
        $chunks = SearchVectorDBService::searchFileChunks($userPrompt);

        $messages = $this->buildMessages($userPrompt, $chunks);

        $resp = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'temperature' => 0.2,
            'messages' => $messages,
            'max_tokens' => 2048,
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

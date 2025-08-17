<?php

namespace App\Services\IdeaGeneration;

use App\Services\LLM\LLMCompletionService;
use App\Traits\HasMakeAble;

class IdeaGenerationFromRepoService
{
    use HasMakeAble;

    private static function buildMessages(string $transcript, string $contextFiles): array
    {
        $system = <<<SYS
        You are an expert product owner, full stack engineer and project ideation assistant.
        Rules:
        - You must respond only with a JSON array of issues for the RACG system to generate code from.
        - Each issue must be an object with:
          {
            "title": "<short title>",
            "description": "<technical description ready for code generation, using the file names and paths from the context>",
            "ranking": "<1-10 ranking based on feasibility and impact>"
          }
        - Do not include any explanations, text, or markdown outside the JSON.
        SYS;

        $user = <<<USR
        Using the following repository context (files) and a brainstorming transcript, generate 8 to 10 distinct, technically detailed project issues.

        Output only a JSON array. Each element must be an object:
        {
          "title": "<concise title of the issue>",
          "description": "<technical description ready for code generation, using the file names and paths from the context>",
          "ranking": "<1-10 ranking based on feasibility and impact>"
        }

        Guidelines:
        - Each issue must describe the changes to be made to the repository.
        - All prototypes should use only the packages already present in the repository (package.json).
        - Each description should clearly specify:
          - The purpose of the task.
          - Any important implementation considerations.
          - Any important functionality or features.
          - Any relevant file names and paths from the context.
        - Do not include any explanations or text outside the JSON array.

        BRAINSTORMING TRANSCRIPT:
        {$transcript}

        CONTEXT FILES:
        {$contextFiles}
        USR;

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
    }

    /**
     * @throws \Exception
     */
    public static function generateIdeas(string $provider, string $transcript, string $contextFiles): string
    {
        $messages = self::buildMessages($transcript, $contextFiles);

        return LLMCompletionService::chat($provider, [
            'model' => 'ideation',
            'temperature' => 0.7,
            'messages' => $messages,
            'max_tokens' => 16000,
        ]);
    }
}

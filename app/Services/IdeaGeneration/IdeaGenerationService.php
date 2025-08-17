<?php

namespace App\Services\IdeaGeneration;

use App\Services\LLM\LLMCompletionService;
use App\Traits\HasMakeAble;

class IdeaGenerationService
{
    use HasMakeAble;

    private static function buildMessages(string $context): array
    {
        $system = <<<SYS
        You are an expert product owner, full stack engineer and project ideation assistant.
        Rules:
        - You must respond only with a JSON array of ideas.
        - Each idea must be an object with:
          {
            "title": "<short title>",
            "description": "<technical description ready for code generation>",
            "ranking": "<1-10 ranking based on feasibility and impact>"
          }
        - Do not include any explanations, text, or markdown outside the JSON.
        SYS;

        $user = <<<USR
        Using the following project context and brainstorming session transcripts, generate 8 to 10 distinct, technically detailed project ideas.

        Output only a JSON array. Each element must be an object:
        {
          "title": "<concise title of the idea>",
          "description": "<technical description ready to be used as input for a code generation model>",
          "ranking": "<1-10 ranking based on feasibility and impact>"
        }

        Guidelines:
        - Each idea must describe a React prototype.
        - All prototypes should use only the "antd" UI framework.
        - Each description should clearly specify:
          - The purpose of the prototype.
          - The flow of the user interface.
          - The main components and Ant Design elements involved.
          - Any important implementation considerations.
          - Any important functionality or features.
        - The ideas should be varied in scope and complexity.
        - Do not include any explanations or text outside the JSON array.

        CONTEXT:
        {$context}
        USR;

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
    }

    /**
     * @throws \Exception
     */
    public static function generateIdeas(string $provider, string $context): string
    {
        $messages = self::buildMessages($context);

        return LLMCompletionService::chat($provider, [
            'model' => 'ideation',
            'temperature' => 0.7,
            'messages' => $messages,
            'max_tokens' => 10000,
        ]);
    }
}

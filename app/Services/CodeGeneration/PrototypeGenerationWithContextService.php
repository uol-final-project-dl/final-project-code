<?php

namespace App\Services\CodeGeneration;

use App\Traits\HasMakeAble;
use OpenAI\Laravel\Facades\OpenAI;

class PrototypeGenerationWithContextService
{
    use HasMakeAble;

    private function buildMessages(string $userPrompt): array
    {
        $package = file_get_contents(base_path('docker/react-buildbox/templates/base/package.json'));
        $baseline = file_get_contents(base_path('docker/react-buildbox/templates/base/src/App.jsx'));

        $system = <<<SYS
        You are an expert React/Tailwind engineer.
        Return **only** the complete replacement for \`src/App.jsx\`.
        All imports must already exist in package.json; do not add other files.
        SYS;

        $user = <<<USR
        PACKAGE.JSON (read-only context)
        ```json
        {$package}
        BASELINE APP.JSX (read-only context)
        {$baseline}
        REQUEST:
        {$userPrompt}

        Respond with only the new `App.jsx` source, ready to copy inside without any decorators (don't add "```jsx" for example).
        USR;

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
    }
    
    public function generate(string $userPrompt): string
    {
        $messages = $this->buildMessages($userPrompt);

        $resp = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'temperature' => 0.2,
            'messages' => $messages,
            'max_tokens' => 2048,
        ]);

        return $resp['choices'][0]['message']['content'] ?? '';
    }
}

<?php

namespace App\Services\CodeGeneration;

use App\Traits\HasMakeAble;
use OpenAI\Laravel\Facades\OpenAI;

class PrototypeGenerationWithContextService
{
    use HasMakeAble;

    private function buildMessages(string $userPrompt, string $codeSoFar = null): array
    {
        $package = file_get_contents(base_path('docker/react-buildbox/templates/base/package.json'));
        $baseline = file_get_contents(base_path('docker/react-buildbox/templates/base/src/App.jsx'));

        $system = <<<SYS
        You are an expert React engineer who can generate React code based on a user prompt.
        You can use Ant Design components. Use inline styles for custom CSS to make it look good.
        Return **only** the complete replacement for \`src/App.jsx\`. You must return **a complete file**.
        All imports must already exist in package.json; do not add any other files.
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

        if ($codeSoFar) {
            $user .= "\n\nPREVIOUS PARTIAL OUTPUT (incomplete—missing the rest of the file):\n```jsx\n{$codeSoFar}\n```\n"
                . "Please **continue** from where you left off so that the final `App.jsx` is a complete, valid React component. "
                . "When you finish, end with exactly `// END OF App.jsx`.";
        }

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
    }

    public function generate(string $userPrompt, string $codeSoFar = null): string
    {
        $messages = $this->buildMessages($userPrompt, $codeSoFar);

        $resp = OpenAI::chat()->create([
            'model' => 'gpt-4.1',
            'temperature' => 0.2,
            'messages' => $messages,
            'max_tokens' => 3000,
        ]);

        return $resp['choices'][0]['message']['content'] ?? '';
    }
}

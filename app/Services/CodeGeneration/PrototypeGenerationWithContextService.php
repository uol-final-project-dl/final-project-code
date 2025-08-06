<?php

namespace App\Services\CodeGeneration;

use App\Models\Prototype;
use App\Services\LLM\LLMCompletionService;
use App\Traits\HasMakeAble;

class PrototypeGenerationWithContextService
{
    use HasMakeAble;

    private function buildMessages(string $userPrompt, string $codeSoFar = null, string $oldCode = null, string $remixDescription = null): array
    {
        $package = file_get_contents(base_path('docker/react-buildbox/templates/base/package.json'));
        $baseline = file_get_contents(base_path('docker/react-buildbox/templates/base/src/App.jsx'));

        $baselineCode = $oldCode ?? $baseline;

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
        {$baselineCode}
        REQUEST:
        {$userPrompt}

        Respond with only the new `App.jsx` source, ready to copy inside without any decorators (don't add "```jsx" for example).
        USR;

        if ($remixDescription) {
            $user .= "\n\nIMPORTANT INSTRUCTIONS:\n You must implement the following changes on the baseline App.JSX: '{$remixDescription}'\n";
        }

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

    /**
     * @throws \Exception
     */
    public function generate(Prototype $prototype, string $userPrompt, string $codeSoFar = null, string $oldCode = null, string $remixDescription = null): string
    {
        if ($prototype->project_idea->project->style_config) {
            $userPrompt .= "\n\n STYLE PREFERENCES: \n" . $prototype->project_idea->project->style_config . "\n";
        }

        $messages = $this->buildMessages($userPrompt, $codeSoFar, $oldCode, $remixDescription);
        $provider = $prototype->user->provider;

        return LLMCompletionService::chat($provider, [
            'model' => 'coding',
            'temperature' => 0.2,
            'messages' => $messages,
            'max_tokens' => 6000,
        ]);
    }
}

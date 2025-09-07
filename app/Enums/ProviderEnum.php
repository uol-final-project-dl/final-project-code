<?php

namespace App\Enums;

enum ProviderEnum: string
{
    case OPENAI = 'openai';
    case ANTHROPIC = 'anthropic';
    case GOOGLE = 'google';
    case LLAMA_LOCAL = 'llama-local';
    case QWEN_LOCAL = 'qwen-local';
    case DEEPSEEK_LOCAL = 'deepseek-local';
    case LLAMA = 'llama';
    case QWEN = 'qwen';
    case DEEPSEEK = 'deepseek';

    public function label(): string
    {
        return match ($this) {
            self::OPENAI => 'OpenAI',
            self::ANTHROPIC => 'Anthropic',
            self::GOOGLE => 'Google',
            self::LLAMA_LOCAL => 'Llama Local',
            self::QWEN_LOCAL => 'Qwen Local',
            self::DEEPSEEK_LOCAL => 'Deepseek Local',
            self::LLAMA => 'Llama',
            self::QWEN => 'Qwen',
            self::DEEPSEEK => 'Deepseek',
        };
    }

    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

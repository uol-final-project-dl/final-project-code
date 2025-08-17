<?php

namespace App\Enums;

enum PrototypeTypeEnum: string
{
    case PULL_REQUEST = 'pull_request';
    case DEMO = 'demo';

    public function label(): string
    {
        return match ($this) {
            self::PULL_REQUEST => 'Pull Request',
            self::DEMO => 'Demo',
        };
    }

    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

}

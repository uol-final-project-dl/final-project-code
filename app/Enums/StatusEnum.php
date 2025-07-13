<?php

namespace App\Enums;

enum StatusEnum: string
{
    case REQUEST_DATA = 'request_data';
    case QUEUED = 'queued';
    case READY = 'ready';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::REQUEST_DATA => 'Request Data',
            self::QUEUED => 'Queued',
            self::READY => 'Ready',
            self::FAILED => 'Failed',
        };
    }

    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

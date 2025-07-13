<?php

namespace App\Enums;

enum ProjectStageEnum: string
{
    case BRAINSTORMING = 'brainstorming';
    case IDEATING = 'ideating';
    case PROTOTYPING = 'prototyping';
    case CODING = 'coding';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::BRAINSTORMING => 'Brainstorming',
            self::IDEATING => 'Ideating',
            self::PROTOTYPING => 'Prototyping',
            self::CODING => 'Coding',
            self::ARCHIVED => 'Archived',
        };
    }

    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

}

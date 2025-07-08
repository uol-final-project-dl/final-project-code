<?php

namespace App\Enums;

enum ProjectStageEnum: string
{
    case BRAINSTORMING = 'brainstorming';
    case IDEATING = 'ideating';
    case PROTOTYPING = 'prototyping';
    case CODING = 'coding';

    public function label(): string
    {
        return match ($this) {
            self::BRAINSTORMING => 'Brainstorming',
            self::IDEATING => 'Ideating',
            self::PROTOTYPING => 'Prototyping',
            self::CODING => 'Coding',
        };
    }

}

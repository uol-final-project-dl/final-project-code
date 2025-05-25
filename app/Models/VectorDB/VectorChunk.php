<?php

namespace App\Models\VectorDB;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class VectorChunk implements Arrayable, Jsonable, \JsonSerializable
{
    public function __construct(
        public string $id,
        public string $text,
        public array  $metadata = [],
    )
    {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'metadata' => $this->metadata,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}

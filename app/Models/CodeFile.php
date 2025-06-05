<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $content
 * @property string $name
 * @property string $path
 * @property string $type
 * @property string $summary
 * @property int $id
 */
class CodeFile extends Model
{
    protected $table = 'code_files';

    protected $fillable = [
        'name',
        'path',
        'type',
        'summary',
        'content',
    ];

    protected $hidden = [
        'content',
    ];
}

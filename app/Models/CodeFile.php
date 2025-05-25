<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CodeFile extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'path',
        'content',
    ];

    protected $hidden = [
        'content',
    ];
}

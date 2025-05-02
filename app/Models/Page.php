<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    public $table = 'pages';

    public $fillable = [
        'title',
        'short_description',
        'tags',
        'content',
        'pageable_type',
        'pageable_id',
        'status'
    ];

    protected $casts = [
        'title' => 'string',
        'short_description' => 'string',
        'content' => 'string',
        'pageable_type' => 'string'
    ];

    public static array $rules = [
        'title' => 'nullable|string|max:255',
        'short_description' => 'nullable|string|max:255',
        'tags' => 'nullable|string|max:255',
        'content' => 'nullable|string',
        'status' => 'nullable|string',
        'pageable_type' => 'nullable|string|max:255',
        'pageable_id' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    
}

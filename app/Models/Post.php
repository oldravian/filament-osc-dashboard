<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
class Post extends Model
{
    use HasSlug;
    public $table = 'posts';

    public $fillable = [
        'author_id',
        'title',
        'slug',
        'content',
        'featured_image',
        'tags',
        'status',
        'short_description'
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    public function postCategoryPivots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PostCategoryPivot::class, 'post_id');
    }

    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany{
        return $this->belongsToMany(\App\Models\PostCategory::class, 'post_category_pivot', 'post_id', 'post_category_id');
    }
}

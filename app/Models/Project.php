<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function medias()
    {
        return $this->hasMany(ProjectMedia::class);
    }

    public function primaryMedia()
    {
        return $this->hasOne(ProjectMedia::class)->where("is_primary", 1);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'project_categories');
    }

    public function technologies()
    {
        return $this->belongsToMany(Technology::class, 'project_technologies');
    }
}
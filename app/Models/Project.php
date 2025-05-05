<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function whereGitLinkExists(string $git_link): bool
    {
        $link_bro = $git_link[strlen($git_link)-1]=="/"? rtrim($git_link, '/') : $git_link . "/";
        return self::where('git_link', $git_link)->orWhere('git_link', $link_bro)->exists();
    }

    public static function whereSlugExists(string $slug): bool
    {
        return self::where('slug', $slug)->exists();
    }

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
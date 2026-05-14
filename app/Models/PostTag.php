<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class PostTag extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
            $originalSlug = $tag->slug;
            $count = 1;
            while (static::where('slug', $tag->slug)->exists()) {
                $tag->slug = $originalSlug . '-' . $count++;
            }
        });
    }

    /**
     * Relationships
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_post_tag');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->withCount('posts')
                     ->orderByDesc('posts_count')
                     ->limit($limit);
    }

    /**
     * Get post count for this tag
     */
    public function getPostCountAttribute()
    {
        return $this->posts()->published()->count();
    }

    /**
     * Static helpers
     */
    public static function getForDropdown()
    {
        return static::active()->ordered()->pluck('name', 'id');
    }
}

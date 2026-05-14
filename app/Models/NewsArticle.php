<?php

namespace App\Models;

use App\Traits\HasMedia;
use App\Traits\HasMetaData;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewsArticle extends Model
{
   use HasFactory, SoftDeletes, HasMedia, HasMetaData, HasTranslations;

    /**
     * The fields that can be translated
     */
    protected $translatable = [
        'title',
        'category',
        'excerpt',
        'content'
    ];

    protected $fillable = [
        'title',
        'category',
        'excerpt',
        'content',
        'image_url',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = ['primary_image'];

    /**
     * Get primary image URL using HasMedia trait
     * Only uses entityMedia - no legacy fallback
     */
    public function getPrimaryImageAttribute()
    {
        // Only use HasMedia trait (featured_image zone)
        return $this->getMediaUrlForZone('featured_image');
    }

    /**
     * Scope to get published articles
     */
    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now());
    }

    /**
     * Scope to get articles by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get featured/recent articles
     */
    public function scopeRecent($query, $limit = 5)
    {
        return $query->orderBy('published_at', 'desc')->limit($limit);
    }
}

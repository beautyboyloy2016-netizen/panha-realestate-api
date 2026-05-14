<?php
namespace App\Models;

use App\Traits\HasMedia;
use App\Traits\HasMetaData;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
  use Illuminate\Database\Eloquent\Model;

  class Project extends Model
  {
    use HasFactory, SoftDeletes, HasMedia, HasMetaData, HasTranslations;
    /**
     * The fields that can be translated
     */
    protected $translatable = [
        'name',
        'location',
        'developer',
        'description',
        'price_from',
        'completion'
    ];

    protected $fillable = [
        'name',
        'location',
        'developer',
        'units',
        'price_from',
        'completion',
        'image_url',
        'featured',
        'rental_yield',
        'description',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'rental_yield' => 'float',
        'units' => 'integer',
    ];

    protected $appends = ['primary_image', 'gallery_images'];

    /**
     * Get primary image URL using HasMedia trait
     * Only uses entityMedia - no legacy fallback
     */
    public function getPrimaryImageAttribute()
    {
        // Only use HasMedia trait (featured_image zone)
        return $this->getMediaUrlForZone('primary_image');
    }

    /**
     * Get all gallery images using HasMedia trait
     */
    public function getGalleryImagesAttribute()
    {
        $galleryMedia = $this->getMediaByZone('gallery');
        if ($galleryMedia->isNotEmpty()) {
            return $galleryMedia->map(function ($entityMedia) {
                return [
                    'id' => $entityMedia->media->id,
                    'url' => $entityMedia->media->full_url,
                    'thumbnail_url' => $entityMedia->media->thumbnail ?? $entityMedia->media->full_url,
                ];
            })->values();
        }

        return collect();
    }
  }

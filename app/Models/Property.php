<?php

namespace App\Models;

use App\Traits\HasMedia;
use App\Traits\HasMetaData;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory, SoftDeletes, HasMedia, HasMetaData, HasTranslations;

    /**
     * The fields that can be translated
     */
    protected $translatable = [
        'title',
        'description',
        'location',
        'district',
        'features'
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'listing_type',
        'property_type',
        'price',
        'location',
        'city',
        'district',
        'bedrooms',
        'bathrooms',
        'area',
        'area_unit',
        'latitude',
        'longitude',
        'features',
        'is_featured',
        'is_available',
        'views'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'area' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'features' => 'array',
        'is_featured' => 'boolean',
        'is_available' => 'boolean',
        'views' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer'
    ];

    protected $appends = ['formatted_price', 'primary_image', 'gallery_images'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderBy('order');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0);
    }

    /**
     * Get primary image URL using HasMedia trait
     * Only uses entityMedia - no Unsplash fallback
     */
    public function getPrimaryImageAttribute()
    {
        // Only use HasMedia trait (primary_image zone)
        return $this->getMediaUrlForZone('primary_image');
    }

    /**
     * Get all gallery images using HasMedia trait
     * Only uses entityMedia - no Unsplash fallback
     */
    public function getGalleryImagesAttribute()
    {
        // Only use HasMedia trait (gallery zone)
        $galleryMedia = $this->getMediaByZone('gallery');
        return $galleryMedia->map(function ($entityMedia) {
            return [
                'id' => $entityMedia->media->id,
                'url' => $entityMedia->media->full_url,
                'thumbnail_url' => $entityMedia->media->thumbnail ?? $entityMedia->media->full_url,
            ];
        })->values();
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForSale($query)
    {
        return $query->where('listing_type', 'For Sale');
    }

    public function scopeForRent($query)
    {
        return $query->where('listing_type', 'For Rent');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('property_type', $type);
    }

    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopePriceRange($query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('price', '>=', $min);
        }
        if ($max) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

    public function scopeBedroomsMin($query, $min)
    {
        return $query->where('bedrooms', '>=', $min);
    }

    public function scopeAreaRange($query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('area', '>=', $min);
        }
        if ($max) {
            $query->where('area', '<=', $max);
        }
        return $query;
    }

    // Increment view counter
    public function incrementViews()
    {
        $this->increment('views');
    }
}

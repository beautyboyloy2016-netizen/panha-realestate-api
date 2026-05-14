<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class MetaData extends Model
{
    use HasTranslations;

    protected $table = 'meta_data';

    protected $fillable = [
        'entity_type',
        'entity_id',
    ];

    /**
     * The attributes that should be translated.
     *
     * @var array
     */
    protected $translatable = ['title', 'description'];

    /**
     * Get the parent entity (Property, Project, NewsArticle, etc.)
     */
    public function entity()
    {
        return $this->morphTo();
    }

    /**
     * Get meta title for a specific locale
     */
    public function getTitle($locale = null)
    {
        return $this->getTranslation('title', $locale ?? app()->getLocale());
    }

    /**
     * Get meta description for a specific locale
     */
    public function getDescription($locale = null)
    {
        return $this->getTranslation('description', $locale ?? app()->getLocale());
    }

    /**
     * Set meta title for a specific locale
     */
    public function setTitle($value, $locale = null)
    {
        return $this->setTranslation('title', $value, $locale ?? app()->getLocale());
    }

    /**
     * Set meta description for a specific locale
     */
    public function setDescription($value, $locale = null)
    {
        return $this->setTranslation('description', $value, $locale ?? app()->getLocale());
    }
}

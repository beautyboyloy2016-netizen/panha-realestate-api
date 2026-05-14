<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityMedia extends Model
{
    protected $table = 'entity_media';

    protected $fillable = [
        'media_id',
        'entity_type',
        'entity_id',
        'zone'
    ];

    /**
     * Get the media file
     */
    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /**
     * Get the owning entity model (Property, Project, NewsArticle, etc.)
     */
    public function entity()
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }
}

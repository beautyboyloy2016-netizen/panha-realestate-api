<?php

namespace App\Traits;

use App\Models\EntityMedia;

trait HasMedia
{
  /**
   * Boot the HasMedia trait
   */
  public static function bootHasMedia()
  {
    static::deleting(function ($model) {
      $model->entityMedia()->delete();
    });
  }

  /**
   * Get all entity media relationships
   */
  public function entityMedia()
  {
    return $this->morphMany(EntityMedia::class, 'entity', 'entity_type', 'entity_id');
  }

  /**
   * Get media by zone
   */
  public function getMediaByZone($zone)
  {
    return $this->entityMedia()
      ->where('zone', $zone)
      ->with('media')
      ->get();
  }

  /**
   * Get single media by zone (for one-to-one)
   */
  public function getFirstMediaByZone($zone)
  {
    $entityMedia = $this->entityMedia()
      ->where('zone', $zone)
      ->with('media')
      ->first();

    return $entityMedia ? $entityMedia->media : null;
  }

  /**
   * Set single media for zone
   */
  public function setMediaForZone($mediaId, $zone)
  {
    // Remove existing media in this zone
    $this->entityMedia()->where('zone', $zone)->delete();

    // Add new media
    return $this->entityMedia()->create([
      'media_id' => $mediaId,
      'zone' => $zone
    ]);
  }

  /**
   * Add media to zone (for one-to-many)
   */
  public function addMediaToZone($mediaId, $zone)
  {
    return $this->entityMedia()->create([
      'media_id' => $mediaId,
      'zone' => $zone
    ]);
  }

  /**
   * Sync media for zone
   */
  public function syncMediaForZone(array $mediaIds, $zone)
  {
    // Remove existing
    $this->entityMedia()->where('zone', $zone)->delete();

    // Add new
    $result = collect();
    foreach ($mediaIds as $mediaId) {
      $result->push($this->addMediaToZone($mediaId, $zone));
    }

    return $result;
  }

  /**
   * Remove media from zone
   */
  public function removeMediaFromZone($mediaId, $zone)
  {
    return $this->entityMedia()
      ->where('zone', $zone)
      ->where('media_id', $mediaId)
      ->delete();
  }

  /**
   * Remove all media from zone
   */
  public function clearZone($zone)
  {
    return $this->entityMedia()->where('zone', $zone)->delete();
  }

  /**
   * Get media URL for zone
   */
  public function getMediaUrlForZone($zone, $default = null)
  {
    $media = $this->getFirstMediaByZone($zone);
    return $media ? $media->full_url : $default;
  }

  /**
   * Get all media URLs for zone
   */
  public function getMediaUrlsForZone($zone)
  {
    return $this->getMediaByZone($zone)->map(function ($entityMedia) {
      return $entityMedia->media->full_url;
    });
  }

  /**
   * Check if has media in zone
   */
  public function hasMediaInZone($zone)
  {
    return $this->entityMedia()->where('zone', $zone)->exists();
  }

  /**
   * Get media count for zone
   */
  public function getMediaCountForZone($zone)
  {
    return $this->entityMedia()->where('zone', $zone)->count();
  }
}

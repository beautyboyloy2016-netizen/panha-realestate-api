<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'filters',
        'columns',
        'schedule',
        'email_recipients',
        'created_by',
        'is_public',
        'is_active',
        'last_generated_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (empty($report->slug)) {
                $report->slug = Str::slug($report->name);
            }
        });
    }

    /**
     * Get creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Active reports
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Public reports
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: Scheduled reports
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('schedule');
    }

    /**
     * Get type badge
     */
    public function getTypeBadgeAttribute(): string
    {
        $badges = [
            'sales' => 'bg-success',
            'analytics' => 'bg-info',
            'property' => 'bg-primary',
            'user' => 'bg-warning',
            'transaction' => 'bg-secondary',
        ];

        $class = $badges[$this->type] ?? 'bg-secondary';
        return '<span class="badge ' . $class . '">' . ucfirst($this->type) . '</span>';
    }
}

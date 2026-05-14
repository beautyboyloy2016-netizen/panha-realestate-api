<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'icon',
        'settings',
        'processing_fee',
        'processing_fee_type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'settings' => 'array',
        'processing_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get transactions using this payment method
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope: Active payment methods
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope: Filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Calculate processing fee for a given amount
     */
    public function calculateFee(float $amount): float
    {
        if ($this->processing_fee_type === 'percentage') {
            return round($amount * ($this->processing_fee / 100), 2);
        }

        return $this->processing_fee;
    }

    /**
     * Get formatted processing fee
     */
    public function getFormattedFeeAttribute(): string
    {
        if ($this->processing_fee_type === 'percentage') {
            return $this->processing_fee . '%';
        }

        return '$' . number_format($this->processing_fee, 2);
    }

    /**
     * Get icon HTML
     */
    public function getIconHtmlAttribute(): string
    {
        return $this->icon ? '<i class="' . $this->icon . '"></i>' : '<i class="fas fa-credit-card"></i>';
    }
}

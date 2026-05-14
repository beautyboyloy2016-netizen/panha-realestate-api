<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'invoice_id',
        'payment_method_id',
        'transactionable_type',
        'transactionable_id',
        'type',
        'amount',
        'fee',
        'net_amount',
        'currency',
        'status',
        'gateway_transaction_id',
        'gateway_response',
        'description',
        'notes',
        'ip_address',
        'user_agent',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
    ];

    protected $appends = ['formatted_amount', 'status_badge'];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = self::generateTransactionId();
            }

            // Calculate net amount if not set
            if (empty($transaction->net_amount)) {
                $transaction->net_amount = $transaction->amount - $transaction->fee;
            }
        });
    }

    /**
     * Generate unique transaction ID
     */
    public static function generateTransactionId(): string
    {
        return 'TXN-' . strtoupper(Str::random(8)) . '-' . time();
    }

    /**
     * Get user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get payment method
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get related entity (property, project, etc.)
     */
    public function transactionable()
    {
        return $this->morphTo();
    }

    /**
     * Scope: By status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: By type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Payments only
     */
    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    /**
     * Scope: Refunds only
     */
    public function scopeRefunds($query)
    {
        return $query->where('type', 'refund');
    }

    /**
     * Scope: Date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === 'refund' ? '-' : '';
        return $prefix . $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => 'bg-warning',
            'processing' => 'bg-info',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'refunded' => 'bg-secondary',
            'cancelled' => 'bg-dark',
        ];

        $class = $badges[$this->status] ?? 'bg-secondary';
        return '<span class="badge ' . $class . '">' . ucfirst($this->status) . '</span>';
    }

    /**
     * Get type badge HTML
     */
    public function getTypeBadgeAttribute(): string
    {
        $badges = [
            'payment' => 'bg-primary',
            'refund' => 'bg-warning',
            'deposit' => 'bg-info',
            'withdrawal' => 'bg-secondary',
        ];

        $class = $badges[$this->type] ?? 'bg-secondary';
        return '<span class="badge ' . $class . '">' . ucfirst($this->type) . '</span>';
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $reason = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'notes' => $reason,
            'processed_at' => now(),
        ]);
    }

    /**
     * Create refund transaction
     */
    public function createRefund(float $amount = null): Transaction
    {
        return self::create([
            'user_id' => $this->user_id,
            'invoice_id' => $this->invoice_id,
            'payment_method_id' => $this->payment_method_id,
            'transactionable_type' => $this->transactionable_type,
            'transactionable_id' => $this->transactionable_id,
            'type' => 'refund',
            'amount' => $amount ?? $this->amount,
            'fee' => 0,
            'net_amount' => $amount ?? $this->amount,
            'currency' => $this->currency,
            'status' => 'pending',
            'description' => 'Refund for transaction: ' . $this->transaction_id,
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_number',
        'total_amount',
        'status',
        'payment_method',
        'payment_status',
        'payment_proof',
        'payment_reference',
        'delivery_method',
        'delivery_address',
        'notes',
        'confirmed_at',
        'completed_at',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    // Methods
    public function markAsConfirmed()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function requiresPaymentProof()
    {
        return in_array($this->payment_method, ['gcash', 'digital_wallet']);
    }

    public function isPaymentVerified()
    {
        return $this->payment_status === 'paid' && $this->verified_at !== null;
    }

    public function getPaymentProofUrlAttribute()
    {
        if (!$this->payment_proof) {
            return null;
        }

        // Handle both with and without 'public/' prefix
        $path = str_replace('public/', '', $this->payment_proof);
        
        // Generate URL using Laravel's storage helper
        return url('storage/' . $path);
    }
}

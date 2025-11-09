<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_phone',
        'total_amount',
        'payment_method',
        'staff_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'reference_id')
            ->where('reference_type', 'transaction');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
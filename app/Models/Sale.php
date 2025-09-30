<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'date',
        'user_id',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'payment_method',
        'prescription_file'
    ];

    protected $casts = [
        'date' => 'datetime',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            // We don't need to calculate total here as it's handled by the form
            // and stored in total_amount
        });

        static::created(function ($sale) {
            // Stock movements are now handled by SaleItem model
        });
    }
}

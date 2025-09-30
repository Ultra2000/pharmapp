<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'date',
        'user_id',
        'reason',
        'unit_price'
    ];

    protected $casts = [
        'date' => 'datetime',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2'
    ];

    const TYPE_IN = 'entrée';
    const TYPE_OUT = 'sortie';

    const REASON_PURCHASE = 'achat';
    const REASON_SALE = 'vente';
    const REASON_LOSS = 'perte';
    const REASON_ADJUSTMENT = 'ajustement';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function reasonOptions(): array
    {
        return [
            self::REASON_PURCHASE => 'Achat',
            self::REASON_SALE => 'Vente',
            self::REASON_LOSS => 'Perte',
            self::REASON_ADJUSTMENT => 'Ajustement'
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_IN => 'Entrée',
            self::TYPE_OUT => 'Sortie'
        ];
    }
}

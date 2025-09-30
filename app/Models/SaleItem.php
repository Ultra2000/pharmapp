<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Calcul automatique du prix total de la ligne
            $item->total_price = $item->quantity * $item->unit_price;
        });

        static::created(function ($item) {
            // Déduction de la quantité du stock du produit
            $product = $item->product;
            $product->stock -= $item->quantity;
            $product->save();

            // Enregistrement du mouvement de stock pour traçabilité
            StockMovement::create([
                'product_id' => $item->product_id,
                'type' => StockMovement::TYPE_OUT,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'reason' => StockMovement::REASON_SALE,
                'date' => $item->sale->date,
                'user_id' => $item->sale->user_id
            ]);
        });
    }
}
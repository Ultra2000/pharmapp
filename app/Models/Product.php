<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'dci', 'dosage', 'form', 'barcode', 'purchase_price',
        'sale_price', 'stock', 'min_stock', 'expiry_date', 'lot_number', 'supplier_id', 'image'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'sale_product')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }
}

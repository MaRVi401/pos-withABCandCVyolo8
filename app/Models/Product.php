<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'product_id';
    public $timestamps = false; // Memakai created_at bawaan DB schema

    protected $fillable = [
        'category_id',
        'sku',
        'product_name',
        'buy_price',
        'sell_price',
        'stock_quantity',
        'min_stock_alert',
        'created_at',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'product_id', 'product_id');
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'product_id', 'product_id');
    }

    public function promoBundles()
    {
        return $this->belongsToMany(PromoBundle::class, 'promo_bundle_items', 'product_id', 'bundle_id')
                    ->withPivot('quantity');
    }
}
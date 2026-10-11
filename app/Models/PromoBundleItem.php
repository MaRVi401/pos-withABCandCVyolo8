<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoBundleItem extends Model
{
    use HasFactory;

    protected $table = 'promo_bundle_items';
    protected $primaryKey = 'bundle_item_id';
    public $timestamps = false;

    protected $fillable = [
        'bundle_id',
        'product_id',
        'quantity',
    ];

    public function promoBundle()
    {
        return $this->belongsTo(PromoBundle::class, 'bundle_id', 'bundle_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
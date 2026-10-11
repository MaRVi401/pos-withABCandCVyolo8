<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoBundle extends Model
{
    use HasFactory;

    protected $table = 'promo_bundles';
    protected $primaryKey = 'bundle_id';
    public $timestamps = false;

    protected $fillable = [
        'bundle_name',
        'normal_price',
        'discount_amount',
        'promo_price',
        'is_active',
        'created_at',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promo_bundle_items', 'bundle_id', 'product_id')
                    ->withPivot('quantity');
    }

    public function items()
    {
        return $this->hasMany(PromoBundleItem::class, 'bundle_id', 'bundle_id');
    }
}
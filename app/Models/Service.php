<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_min',
        'price',
        'estimated_product_cost',
        'commission_type',
        'commission_value',
        'color_hex',
        'description',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'estimated_product_cost' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot(['quantity', 'is_per_session']);
    }

    public function calculateProductCost(): float
    {
        $this->loadMissing('products');
        $cost = 0;
        foreach ($this->products as $product) {
            $cost += $product->purchase_price * $product->pivot->quantity;
        }
        return $cost;
    }

    public function calculateCommission(float $price): float
    {
        if (!$this->commission_type || !$this->commission_value) {
            return 0;
        }

        return match ($this->commission_type) {
            'percentage' => $price * ($this->commission_value / 100),
            'fixed' => $this->commission_value,
            default => 0,
        };
    }
}

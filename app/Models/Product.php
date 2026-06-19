<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'expiry_date',
        'purchase_price',
        'quantity',
        'min_stock',
        'supplier',
        'sale_price',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class)->withPivot(['quantity', 'is_per_session']);
    }

    public function appointments()
    {
        return $this->belongsToMany(Appointment::class)->withPivot('quantity', 'unit_price');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->min_stock > 0 && $this->quantity <= $this->min_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    public function addStock(int $quantity, ?string $notes = null): StockMovement
    {
        $this->increment('quantity', $quantity);

        return $this->stockMovements()->create([
            'type'     => 'in',
            'quantity' => $quantity,
            'notes'    => $notes,
            'user_id'  => auth()->id(),
        ]);
    }

    public function removeStock(int $quantity, ?string $notes = null): ?StockMovement
    {
        if ($this->quantity < $quantity) {
            return null;
        }

        $this->decrement('quantity', $quantity);

        return $this->stockMovements()->create([
            'type'     => 'out',
            'quantity' => $quantity,
            'notes'    => $notes,
            'user_id'  => auth()->id(),
        ]);
    }
}

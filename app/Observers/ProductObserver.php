<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Product;

class ProductObserver
{
    public function created(Product $product)
    {
        ActivityLog::log('created', $product, "Produto '{$product->name}' foi cadastrado.", null, $product->toArray());
    }

    public function updated(Product $product)
    {
        $old = $product->getOriginal();
        $changes = [];
        foreach ($product->getChanges() as $key => $value) {
            if ($key !== 'updated_at') {
                $changes[] = "$key: {$old[$key]} → $value";
            }
        }
        if ($changes) {
            ActivityLog::log('updated', $product, "Produto '{$product->name}' foi atualizado: " . implode(', ', $changes), $old, $product->toArray());
        }
    }

    public function deleted(Product $product)
    {
        ActivityLog::log('deleted', $product, "Produto '{$product->name}' foi removido.", $product->toArray(), null);
    }
}

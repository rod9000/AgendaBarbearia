<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Customer;

class CustomerObserver
{
    public function created(Customer $customer)
    {
        ActivityLog::log('created', $customer, "Cliente '{$customer->name}' foi cadastrado.", null, $customer->toArray());
    }

    public function updated(Customer $customer)
    {
        $old = $customer->getOriginal();
        $changes = [];
        foreach ($customer->getChanges() as $key => $value) {
            if ($key !== 'updated_at') {
                $changes[] = "$key: {$old[$key]} → $value";
            }
        }
        if ($changes) {
            ActivityLog::log('updated', $customer, "Cliente '{$customer->name}' foi atualizado: " . implode(', ', $changes), $old, $customer->toArray());
        }
    }

    public function deleted(Customer $customer)
    {
        ActivityLog::log('deleted', $customer, "Cliente '{$customer->name}' foi removido.", $customer->toArray(), null);
    }
}

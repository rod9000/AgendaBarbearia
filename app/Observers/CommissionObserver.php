<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Commission;

class CommissionObserver
{
    public function created(Commission $commission)
    {
        ActivityLog::log('created', $commission, "Comissão de R\$ " . number_format($commission->value, 2, ',', '.') . " criada.", null, $commission->toArray());
    }

    public function updated(Commission $commission)
    {
        $old = $commission->getOriginal();
        $changes = [];
        foreach ($commission->getChanges() as $key => $value) {
            if ($key !== 'updated_at') {
                $changes[] = "$key: {$old[$key]} → $value";
            }
        }
        if ($changes) {
            ActivityLog::log('updated', $commission, "Comissão #{$commission->id} atualizada: " . implode(', ', $changes), $old, $commission->toArray());
        }
    }

    public function deleted(Commission $commission)
    {
        ActivityLog::log('deleted', $commission, "Comissão #{$commission->id} removida.", $commission->toArray(), null);
    }
}

<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Service;

class ServiceObserver
{
    public function created(Service $service)
    {
        ActivityLog::log('created', $service, "Procedimento '{$service->name}' foi cadastrado.", null, $service->toArray());
    }

    public function updated(Service $service)
    {
        $old = $service->getOriginal();
        $changes = [];
        foreach ($service->getChanges() as $key => $value) {
            if ($key !== 'updated_at') {
                $changes[] = "$key: {$old[$key]} → $value";
            }
        }
        if ($changes) {
            ActivityLog::log('updated', $service, "Procedimento '{$service->name}' foi atualizado: " . implode(', ', $changes), $old, $service->toArray());
        }
    }

    public function deleted(Service $service)
    {
        ActivityLog::log('deleted', $service, "Procedimento '{$service->name}' foi removido.", $service->toArray(), null);
    }
}

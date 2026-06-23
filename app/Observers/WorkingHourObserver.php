<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\WorkingHour;

class WorkingHourObserver
{
    public function created(WorkingHour $workingHour)
    {
        ActivityLog::log('created', $workingHour, "Horário de trabalho cadastrado.", null, $workingHour->toArray());
    }

    public function updated(WorkingHour $workingHour)
    {
        $old = $workingHour->getOriginal();
        $changes = [];
        foreach ($workingHour->getChanges() as $key => $value) {
            if ($key !== 'updated_at') {
                $changes[] = "$key: {$old[$key]} → $value";
            }
        }
        if ($changes) {
            ActivityLog::log('updated', $workingHour, "Horário de trabalho atualizado: " . implode(', ', $changes), $old, $workingHour->toArray());
        }
    }

    public function deleted(WorkingHour $workingHour)
    {
        ActivityLog::log('deleted', $workingHour, "Horário de trabalho removido.", $workingHour->toArray(), null);
    }
}

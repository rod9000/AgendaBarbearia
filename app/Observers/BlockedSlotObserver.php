<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\BlockedSlot;

class BlockedSlotObserver
{
    public function created(BlockedSlot $blockedSlot)
    {
        ActivityLog::log('created', $blockedSlot, "Horário bloqueado criado.", null, $blockedSlot->toArray());
    }

    public function updated(BlockedSlot $blockedSlot)
    {
        $old = $blockedSlot->getOriginal();
        $changes = [];
        foreach ($blockedSlot->getChanges() as $key => $value) {
            if ($key !== 'updated_at') {
                $changes[] = "$key: {$old[$key]} → $value";
            }
        }
        if ($changes) {
            ActivityLog::log('updated', $blockedSlot, "Horário bloqueado atualizado: " . implode(', ', $changes), $old, $blockedSlot->toArray());
        }
    }

    public function deleted(BlockedSlot $blockedSlot)
    {
        ActivityLog::log('deleted', $blockedSlot, "Horário bloqueado removido.", $blockedSlot->toArray(), null);
    }
}

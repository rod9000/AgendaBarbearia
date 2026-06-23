<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\LoyaltyReward;

class LoyaltyRewardObserver
{
    public function created(LoyaltyReward $reward)
    {
        ActivityLog::log('created', $reward, "Recompensa '{$reward->name}' cadastrada.", null, $reward->toArray());
    }

    public function updated(LoyaltyReward $reward)
    {
        $old = $reward->getOriginal();
        $changes = [];
        foreach ($reward->getChanges() as $key => $value) {
            if ($key !== 'updated_at') {
                $changes[] = "$key: {$old[$key]} → $value";
            }
        }
        if ($changes) {
            ActivityLog::log('updated', $reward, "Recompensa '{$reward->name}' atualizada: " . implode(', ', $changes), $old, $reward->toArray());
        }
    }

    public function deleted(LoyaltyReward $reward)
    {
        ActivityLog::log('deleted', $reward, "Recompensa '{$reward->name}' removida.", $reward->toArray(), null);
    }
}

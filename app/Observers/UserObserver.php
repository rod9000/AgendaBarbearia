<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\User;

class UserObserver
{
    public function created(User $user)
    {
        ActivityLog::log('created', $user, "Usuário '{$user->name}' foi cadastrado.", null, $user->toArray());
    }

    public function updated(User $user)
    {
        $old = $user->getOriginal();
        $changes = [];
        foreach ($user->getChanges() as $key => $value) {
            if ($key !== 'updated_at' && $key !== 'remember_token' && $key !== 'password') {
                $changes[] = "$key: {$old[$key]} → $value";
            }
        }
        if ($changes) {
            ActivityLog::log('updated', $user, "Usuário '{$user->name}' foi atualizado: " . implode(', ', $changes), $old, $user->toArray());
        }
    }

    public function deleted(User $user)
    {
        ActivityLog::log('deleted', $user, "Usuário '{$user->name}' foi removido.", $user->toArray(), null);
    }
}

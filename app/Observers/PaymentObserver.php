<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Payment;

class PaymentObserver
{
    public function created(Payment $payment)
    {
        ActivityLog::log('created', $payment, "Pagamento de R\$ " . number_format($payment->amount, 2, ',', '.') . " registrado.", null, $payment->toArray());
    }

    public function updated(Payment $payment)
    {
        $old = $payment->getOriginal();
        $changes = [];
        foreach ($payment->getChanges() as $key => $value) {
            if ($key !== 'updated_at') {
                $changes[] = "$key: {$old[$key]} → $value";
            }
        }
        if ($changes) {
            ActivityLog::log('updated', $payment, "Pagamento #{$payment->id} atualizado: " . implode(', ', $changes), $old, $payment->toArray());
        }
    }

    public function deleted(Payment $payment)
    {
        ActivityLog::log('deleted', $payment, "Pagamento #{$payment->id} removido.", $payment->toArray(), null);
    }
}

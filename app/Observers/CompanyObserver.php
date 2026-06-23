<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Company;

class CompanyObserver
{
    public function created(Company $company)
    {
        ActivityLog::log('created', $company, "Empresa '{$company->name}' cadastrada.", null, $company->toArray());
    }

    public function updated(Company $company)
    {
        $old = $company->getOriginal();
        $changes = [];
        foreach ($company->getChanges() as $key => $value) {
            if ($key !== 'updated_at') {
                $changes[] = "$key: {$old[$key]} → $value";
            }
        }
        if ($changes) {
            ActivityLog::log('updated', $company, "Empresa '{$company->name}' atualizada: " . implode(', ', $changes), $old, $company->toArray());
        }
    }

    public function deleted(Company $company)
    {
        ActivityLog::log('deleted', $company, "Empresa '{$company->name}' removida.", $company->toArray(), null);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTrial
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->company) {
            return $next($request);
        }

        if ($user->company->isTrialExpired()) {
            return redirect()->route('trial.expired');
        }

        return $next($request);
    }
}

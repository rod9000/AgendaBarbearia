<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPagePermission
{
    public static function availablePages(): array
    {
        return [
            'admin.dashboard'        => 'Dashboard',
            'admin.appointments.*'   => 'Agenda',
            'admin.customers.*'      => 'Clientes',
            'admin.services.*'       => 'Serviços',
            'admin.products.*'       => 'Produtos',
            'admin.sales.*'          => 'Vendas',
            'admin.financial.*'      => 'Financeiro',
            'admin.commissions.*'    => 'Comissões',
            'admin.settings.*'       => 'Horários',
            'admin.loyalty.*'        => 'Fidelidade',
        ];
    }

    public static function defaultPages(): array
    {
        return ['admin.dashboard', 'admin.sales.*'];
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->isAdmin()) {
            return $next($request);
        }

        $route = $request->route();

        if (!$route || !$route->getName()) {
            return $next($request);
        }

        $routeName = $route->getName();

        $allowedPages = $user->pagePermissions()->pluck('page')->toArray();

        foreach ($allowedPages as $pattern) {
            if ($routeName === $pattern || str_starts_with($routeName, rtrim($pattern, '*'))) {
                return $next($request);
            }

            if (str_ends_with($pattern, '.*')) {
                $prefix = rtrim($pattern, '.*');
                if (str_starts_with($routeName, $prefix)) {
                    return $next($request);
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Acesso não autorizado a esta página.'], 403);
        }

        abort(403, 'Acesso não autorizado a esta página.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MigrationController extends Controller
{
    public function run()
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403);
        }

        Artisan::call('migrate --force');

        return response('<pre>' . Artisan::output() . '</pre>');
    }
}

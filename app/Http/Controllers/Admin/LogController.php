<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($model = $request->get('model')) {
            $query->where('model_type', 'like', "%{$model}%");
        }

        if ($search = $request->get('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        $logs = $query->paginate(30);

        return view('admin.logs.index', compact('logs'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReceiveWebhook;
use Illuminate\Http\Request;

class WebhookLogController extends Controller
{
    public function index(Request $request)
    {
        $webhooks = ReceiveWebhook::query()
            ->when($request->event, fn($q, $event) => $q->where('event', $event))
            ->when($request->phone, fn($q, $phone) => $q->where('sender_phone', 'like', "%{$phone}%"))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $events = ReceiveWebhook::distinct()->pluck('event')->filter()->sort()->values();
        $total = ReceiveWebhook::count();

        return view('admin.settings.webhook-logs', compact('webhooks', 'events', 'total'));
    }

    public function show(ReceiveWebhook $webhook)
    {
        return view('admin.settings.webhook-detail', compact('webhook'));
    }
}

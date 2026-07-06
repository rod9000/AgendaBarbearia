<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\LoyaltyController;
use App\Http\Controllers\Admin\MigrationController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\EvolutionController;
use App\Http\Controllers\Admin\BotController;
use App\Http\Controllers\Admin\BotMessagesController;
use App\Http\Controllers\Admin\BotMenuController;
use App\Http\Controllers\Admin\BlockedNumberController;
use App\Http\Controllers\Admin\WebhookLogController;

Route::get('/', function () {
    if (auth()->check()) {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('admin.appointments.index');
        }
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

Route::get('/trial-expired', function () {
    return view('trial.expired');
})->name('trial.expired');

Route::middleware(['auth', 'trial', 'pagePermission'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    Route::resource('services', ServiceController::class)->except(['show']);
    Route::post('services/sync', [ServiceController::class, 'sync'])->name('services.sync');

    Route::get('settings/working-hours', [SettingsController::class, 'workingHours'])->name('settings.working-hours');
    Route::post('settings/working-hours', [SettingsController::class, 'workingHoursStore'])->name('settings.working-hours.store');
    Route::post('settings/working-hours/copy', [SettingsController::class, 'workingHoursCopy'])->name('settings.working-hours.copy');
    Route::get('settings/blocked-slots', [SettingsController::class, 'blockedSlots'])->name('settings.blocked-slots');
    Route::post('settings/blocked-slots', [SettingsController::class, 'blockedSlotsStore'])->name('settings.blocked-slots.store');
    Route::delete('settings/blocked-slots/{blockedSlot}', [SettingsController::class, 'blockedSlotsDestroy'])->name('settings.blocked-slots.destroy');

    Route::get('products/stock-report', [ProductController::class, 'stockReport'])->name('products.stock-report');
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::post('products/movement', [ProductController::class, 'movementStore'])->name('products.movement.store');

    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

    Route::get('financial', [FinancialController::class, 'index'])->name('financial.index');
    Route::post('financial/payments', [FinancialController::class, 'store'])->name('financial.payments.store');

    Route::get('commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::get('commissions/professional/{user}', [CommissionController::class, 'professionalStatement'])->name('commissions.professional');
    Route::post('commissions/{commission}/mark-paid', [CommissionController::class, 'markPaid'])->name('commissions.mark-paid');

    Route::get('loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
    Route::get('loyalty/create', [LoyaltyController::class, 'create'])->name('loyalty.create');
    Route::post('loyalty', [LoyaltyController::class, 'store'])->name('loyalty.store');
    Route::get('loyalty/{loyaltyReward}/edit', [LoyaltyController::class, 'edit'])->name('loyalty.edit');
    Route::put('loyalty/{loyaltyReward}', [LoyaltyController::class, 'update'])->name('loyalty.update');
    Route::delete('loyalty/{loyaltyReward}', [LoyaltyController::class, 'destroy'])->name('loyalty.destroy');
    Route::get('customers/{customer}/points', [LoyaltyController::class, 'customerPoints'])->name('loyalty.customer');
    Route::post('customers/{customer}/redeem', [LoyaltyController::class, 'redeem'])->name('loyalty.redeem');

    Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('appointments/calendar-data', [AppointmentController::class, 'calendarData'])->name('appointments.calendar-data');
    Route::post('appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::put('appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::put('appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
});

Route::middleware(['auth', 'trial', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);

    Route::get('logs', [LogController::class, 'index'])->name('logs.index');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::get('reports/commissions', [ReportController::class, 'commissionReport'])->name('reports.commissions');
    Route::get('reports/commissions/csv', [ReportController::class, 'exportCommissionCsv'])->name('reports.commissions.csv');
    Route::get('reports/financial/csv', [ReportController::class, 'exportFinancialCsv'])->name('reports.financial.csv');

    Route::get('backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('backup/run', [BackupController::class, 'run'])->name('backup.run');
    Route::get('backup/download/{filename}', [BackupController::class, 'download'])->name('backup.download');

    Route::get('migrate', [MigrationController::class, 'run'])->name('migrate');

    Route::get('settings/whatsapp', [SettingsController::class, 'whatsapp'])->name('settings.whatsapp');
    Route::post('settings/whatsapp', [SettingsController::class, 'whatsappStore'])->name('settings.whatsapp.store');

    Route::get('settings/evolution', [EvolutionController::class, 'index'])->name('settings.evolution');
    Route::post('settings/evolution', [EvolutionController::class, 'store'])->name('settings.evolution.store');
    Route::post('settings/evolution/connect', [EvolutionController::class, 'connect'])->name('settings.evolution.connect');
    Route::get('settings/evolution/status', [EvolutionController::class, 'status'])->name('settings.evolution.status');
    Route::post('settings/evolution/disconnect', [EvolutionController::class, 'disconnect'])->name('settings.evolution.disconnect');
    Route::post('settings/evolution/set-webhook', [EvolutionController::class, 'setWebhook'])->name('settings.evolution.set-webhook');

    Route::get('settings/company', [App\Http\Controllers\Admin\CompanySettingsController::class, 'index'])->name('settings.company');
    Route::post('settings/company', [App\Http\Controllers\Admin\CompanySettingsController::class, 'store'])->name('settings.company.store');

    Route::get('settings/bot', [BotController::class, 'index'])->name('settings.bot');
    Route::post('settings/bot', [BotController::class, 'store'])->name('settings.bot.store');

    Route::get('bot-menu', [BotMenuController::class, 'index'])->name('bot-menu.index');
    Route::post('bot-menu', [BotMenuController::class, 'store'])->name('bot-menu.store');
    Route::put('bot-menu/{menuItem}', [BotMenuController::class, 'update'])->name('bot-menu.update');
    Route::delete('bot-menu/{menuItem}', [BotMenuController::class, 'destroy'])->name('bot-menu.destroy');
    Route::post('bot-menu/reorder', [BotMenuController::class, 'reorder'])->name('bot-menu.reorder');

    Route::get('settings/blocked-numbers', [BlockedNumberController::class, 'index'])->name('blocked-numbers.index');
    Route::post('settings/blocked-numbers', [BlockedNumberController::class, 'store'])->name('blocked-numbers.store');
    Route::delete('settings/blocked-numbers/{blockedNumber}', [BlockedNumberController::class, 'destroy'])->name('blocked-numbers.destroy');

    Route::get('webhook-logs', [WebhookLogController::class, 'index'])->name('webhook-logs.index');
    Route::get('webhook-logs/{webhook}', [WebhookLogController::class, 'show'])->name('webhook-logs.show');

    Route::get('bot-messages', [BotMessagesController::class, 'index'])->name('bot-messages.index');
    Route::get('bot-messages/{conversation}', [BotMessagesController::class, 'show'])->name('bot-messages.show');
    Route::post('bot-messages/{conversation}/send', [BotMessagesController::class, 'send'])->name('bot-messages.send');
    Route::post('bot-messages/start', [BotMessagesController::class, 'startConversation'])->name('bot-messages.start');
    Route::post('bot-messages/sync', [BotMessagesController::class, 'sync'])->name('bot-messages.sync');
});

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/agendar', [App\Http\Controllers\PublicController::class, 'booking'])->name('public.booking');
    Route::get('/agendar/slots', [App\Http\Controllers\PublicController::class, 'getSlots'])->name('public.slots');
    Route::get('/agendar/buscar-cliente', [App\Http\Controllers\PublicController::class, 'searchCustomer'])->name('public.search-customer');
    Route::post('/agendar', [App\Http\Controllers\PublicController::class, 'store'])->name('public.booking.store');
    Route::get('/agendar/sucesso', [App\Http\Controllers\PublicController::class, 'sucesso'])->name('public.sucesso');
    Route::get('/reagendar/{token}', [App\Http\Controllers\PublicController::class, 'reagendar'])->name('public.reagendar');
    Route::post('/reagendar/{token}', [App\Http\Controllers\PublicController::class, 'reagendarStore'])->name('public.reagendar.store');
    Route::get('/confirmar/{token}', [App\Http\Controllers\PublicController::class, 'confirmar'])->name('public.confirmar');
Route::get('/cancelar/{token}', [App\Http\Controllers\PublicController::class, 'cancelar'])->name('public.cancelar');
Route::post('/cancelar/{token}', [App\Http\Controllers\PublicController::class, 'cancelarStore'])->name('public.cancelar.store');
});

Route::get('/dashboard', function () {
    if (auth()->user() && !auth()->user()->isAdmin()) {
        return redirect()->route('admin.appointments.index');
    }
    return redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';

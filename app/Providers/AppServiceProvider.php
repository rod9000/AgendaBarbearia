<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Observers\AppointmentObserver;
use App\Observers\CustomerObserver;
use App\Observers\ProductObserver;
use App\Observers\ServiceObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Schema::defaultStringLength(191);
        Customer::observe(CustomerObserver::class);
        Appointment::observe(AppointmentObserver::class);
        Service::observe(ServiceObserver::class);
        Product::observe(ProductObserver::class);
        User::observe(UserObserver::class);
    }
}

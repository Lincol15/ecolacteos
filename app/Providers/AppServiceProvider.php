<?php

namespace App\Providers;

use App\Models\MilkDelivery;
use App\Models\Payment;
use App\Models\QualityReport;
use App\Observers\MilkDeliveryObserver;
use App\Observers\PaymentObserver;
use App\Observers\QualityReportObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Model Observers
        MilkDelivery::observe(MilkDeliveryObserver::class);
        Payment::observe(PaymentObserver::class);
        QualityReport::observe(QualityReportObserver::class);
    }
}

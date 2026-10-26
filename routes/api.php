<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CollectionRouteController;
use App\Http\Controllers\Api\V1\CollectorController;
use App\Http\Controllers\Api\V1\CollectorPaymentController;
use App\Http\Controllers\Api\V1\ComplaintController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\MilkDeliveryController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PlantController;
use App\Http\Controllers\Api\V1\ProducerController;
use App\Http\Controllers\Api\V1\QualityReportController;
use App\Http\Controllers\Api\V1\SanctionController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
| API REST para la app móvil (Kotlin Multiplatform). Prefijo final: /api/v1.
| Autenticación por token Bearer de Sanctum; la autorización por rol reutiliza el middleware "role" de la web.
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login')->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', 'role', 'throttle:120,1'])->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        // Productores
        Route::middleware('role:admin,gerente,acopiador,control_calidad')->group(function () {
            Route::get('/producers', [ProducerController::class, 'index'])->name('producers.index');
            Route::get('/producers/{producer}', [ProducerController::class, 'show'])->name('producers.show');
        });
        Route::middleware('role:admin')->group(function () {
            Route::post('/producers', [ProducerController::class, 'store'])->name('producers.store');
            Route::put('/producers/{producer}', [ProducerController::class, 'update'])->name('producers.update');
            Route::patch('/producers/{producer}/status', [ProducerController::class, 'updateStatus'])->name('producers.update-status');
        });

        // Recolecciones (entregas de leche)
        Route::get('/deliveries', [MilkDeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/{delivery}', [MilkDeliveryController::class, 'show'])->name('deliveries.show');
        Route::middleware('role:acopiador,admin,gerente')->group(function () {
            Route::post('/deliveries', [MilkDeliveryController::class, 'store'])->name('deliveries.store');
            Route::patch('/deliveries/{delivery}', [MilkDeliveryController::class, 'update'])->name('deliveries.update');
        });
        Route::patch('/deliveries/{delivery}/status', [MilkDeliveryController::class, 'updateStatus'])
            ->name('deliveries.update-status')->middleware('role:admin,gerente');
        Route::patch('/deliveries/{delivery}/receive', [MilkDeliveryController::class, 'receive'])
            ->name('deliveries.receive')->middleware('role:trabajador_planta,control_calidad,admin,gerente');

        // Rutas de recolección
        Route::middleware('role:acopiador,admin,gerente')->group(function () {
            Route::get('/routes', [CollectionRouteController::class, 'index'])->name('routes.index');
            Route::get('/routes/{route}', [CollectionRouteController::class, 'show'])->name('routes.show');
        });
        Route::patch('/route-stops/{stop}', [CollectionRouteController::class, 'updateStop'])
            ->name('route-stops.update')->middleware('role:acopiador');

        // Análisis de calidad (LACTOMAT)
        Route::get('/quality-reports', [QualityReportController::class, 'index'])
            ->name('quality-reports.index')->middleware('role:control_calidad,admin,gerente,productor');
        Route::post('/quality-reports', [QualityReportController::class, 'store'])
            ->name('quality-reports.store')->middleware('role:control_calidad,admin,gerente');

        // Pagos a productores (solo lectura)
        Route::get('/payments', [PaymentController::class, 'index'])
            ->name('payments.index')->middleware('role:productor,admin,gerente');

        // Avisos
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

        // Quejas y reclamos
        Route::get('/complaints', [ComplaintController::class, 'index'])
            ->name('complaints.index')->middleware('role:productor,admin,gerente');
        Route::post('/complaints', [ComplaintController::class, 'store'])
            ->name('complaints.store')->middleware('role:productor');
        Route::patch('/complaints/{complaint}', [ComplaintController::class, 'update'])
            ->name('complaints.update')->middleware('role:admin');

        // Panel de gestión (administrador y gerente)
        Route::get('/dashboard', DashboardController::class)->name('dashboard')->middleware('role:admin,gerente');
        Route::get('/config', [ConfigController::class, 'index'])->name('config.index')->middleware('role:admin,gerente');
        Route::patch('/quality-reports/{qualityReport}', [QualityReportController::class, 'update'])
            ->name('quality-reports.update')->middleware('role:admin,gerente');
        Route::get('/sanctions', [SanctionController::class, 'index'])->name('sanctions.index')->middleware('role:admin,gerente');
        Route::get('/payments/preview', [PaymentController::class, 'preview'])->name('payments.preview')->middleware('role:admin,gerente');
        Route::get('/collector-payments', [CollectorPaymentController::class, 'index'])
            ->name('collector-payments.index')->middleware('role:admin,gerente,acopiador');

        // Planta: lectura para planta y gestión; registro para planta y administrador (igual que la web)
        Route::prefix('plant')->name('plant.')->group(function () {
            Route::middleware('role:trabajador_planta,admin,gerente')->group(function () {
                Route::get('/products', [PlantController::class, 'products'])->name('products');
                Route::get('/ingredients', [PlantController::class, 'ingredients'])->name('ingredients');
                Route::get('/recipes', [PlantController::class, 'recipes'])->name('recipes');
                Route::get('/batches', [PlantController::class, 'batches'])->name('batches');
                Route::get('/sales', [PlantController::class, 'sales'])->name('sales');
            });
            Route::middleware('role:trabajador_planta,admin')->group(function () {
                Route::post('/recipes', [PlantController::class, 'storeRecipe'])->name('recipes.store');
                Route::post('/production', [PlantController::class, 'storeProduction'])->name('production.store');
                Route::post('/sales', [PlantController::class, 'storeSale'])->name('sales.store');
            });
        });

        // Solo administrador (gestión completa, igual que la web)
        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update-status');

            Route::get('/collectors/{collector}/assignment', [CollectorController::class, 'assignment'])->name('collectors.assignment');
            Route::put('/collectors/{collector}/assignment', [CollectorController::class, 'updateAssignment'])->name('collectors.update-assignment');
            Route::patch('/collectors/{collector}/vehicle', [CollectorController::class, 'updateVehicle'])->name('collectors.update-vehicle');

            Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
            Route::put('/config/{group}', [ConfigController::class, 'updateGroup'])->name('config.update-group');

            Route::post('/sanctions', [SanctionController::class, 'store'])->name('sanctions.store');
            Route::patch('/sanctions/{sanction}', [SanctionController::class, 'update'])->name('sanctions.update');

            Route::post('/payments/generate', [PaymentController::class, 'generate'])->name('payments.generate');
            Route::patch('/payments/{payment}/mark-paid', [PaymentController::class, 'markPaid'])->name('payments.mark-paid');
            Route::post('/collector-payments/generate', [CollectorPaymentController::class, 'generate'])->name('collector-payments.generate');
            Route::patch('/collector-payments/{collectorPayment}/mark-paid', [CollectorPaymentController::class, 'markPaid'])
                ->name('collector-payments.mark-paid');
        });
    });
});

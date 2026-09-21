<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CollectorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\ProducerController;
use App\Http\Controllers\QualityController;
use Illuminate\Support\Facades\Route;

Route::middleware('redirect.role')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/catalog', [HomeController::class, 'catalog'])->name('catalog');
    Route::get('/product/{slug}', [HomeController::class, 'productShow'])->name('product.show');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    Route::post('/contact', [HomeController::class, 'contactStore'])->name('contact.store')->middleware('throttle:10,1');
    Route::get('/login', [HomeController::class, 'login'])->name('login');
    Route::post('/login', [HomeController::class, 'authenticate'])->name('authenticate')->middleware('throttle:5,1');
});

Route::post('/logout', [HomeController::class, 'logout'])->name('logout')->middleware('auth');

// Rutas de solo lectura (y registrar entrega) compartidas entre Admin y Gerente.
Route::middleware(['auth', 'role:admin,gerente'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/producers', [AdminController::class, 'producers'])->name('producers');

    Route::get('/deliveries', [AdminController::class, 'deliveries'])->name('deliveries');
    Route::get('/deliveries/create', [AdminController::class, 'deliveryCreate'])->name('deliveries-create');
    Route::post('/deliveries/store', [AdminController::class, 'deliveryStore'])->name('deliveries-store');

    Route::get('/quality', [AdminController::class, 'quality'])->name('quality');

    Route::get('/production', [AdminController::class, 'production'])->name('production');

    Route::get('/ingredients', [AdminController::class, 'ingredients'])->name('ingredients');

    Route::get('/sales', [AdminController::class, 'sales'])->name('sales');

    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
});

// Rutas exclusivas de Administrador (gestión completa).
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'userCreate'])->name('users-create');
    Route::post('/users/store', [AdminController::class, 'userStore'])->name('user-store');
    Route::get('/users/{user}/edit', [AdminController::class, 'userEdit'])->name('users-edit');
    Route::put('/users/{user}/update', [AdminController::class, 'userUpdate'])->name('user-update');

    Route::get('/producers/{producer}/edit', [AdminController::class, 'producerEdit'])->name('producers-edit');
    Route::put('/producers/{producer}/update', [AdminController::class, 'producerUpdate'])->name('producer-update');

    Route::get('/collectors', [AdminController::class, 'collectors'])->name('collectors');
    Route::get('/collectors/{collector}', [AdminController::class, 'collectorShow'])->name('collectors-show');

    Route::get('/production/create', [AdminController::class, 'productionCreate'])->name('production-create');
    Route::post('/production/store', [AdminController::class, 'productionStore'])->name('production-store');

    Route::post('/ingredients/store', [AdminController::class, 'ingredientStore'])->name('ingredient-store');
    Route::post('/ingredients/adjust', [AdminController::class, 'ingredientAdjust'])->name('ingredient-adjust');

    Route::get('/inventory', [AdminController::class, 'inventory'])->name('inventory');

    Route::get('/sales/create', [AdminController::class, 'salesCreate'])->name('sales-create');
    Route::post('/sales/store', [AdminController::class, 'salesStore'])->name('sales-store');

    Route::get('/payments/process', [AdminController::class, 'paymentsProcess'])->name('payments-process');
    Route::post('/payments/generate', [AdminController::class, 'paymentsGenerate'])->name('payments-generate');
    Route::put('/payments/{payment}/mark-paid', [AdminController::class, 'paymentMarkPaid'])->name('payment-mark-paid');

    Route::get('/routes', [AdminController::class, 'routes'])->name('routes');
    Route::get('/routes/create', [AdminController::class, 'routesCreate'])->name('routes-create');
    Route::post('/routes/store', [AdminController::class, 'routesStore'])->name('routes-store');
    Route::get('/routes/{route}/edit', [AdminController::class, 'routesEdit'])->name('routes-edit');
    Route::put('/routes/{route}/update', [AdminController::class, 'routesUpdate'])->name('routes-update');

    Route::get('/price', [AdminController::class, 'price'])->name('price');
    Route::put('/price/update', [AdminController::class, 'priceUpdate'])->name('price-update');

    Route::get('/config', [AdminController::class, 'config'])->name('config');
    Route::post('/config/store', [AdminController::class, 'configStore'])->name('config-store');
    Route::put('/config/{config}/update', [AdminController::class, 'configUpdate'])->name('config-update');

    Route::get('/sanctions', [AdminController::class, 'sanctions'])->name('sanctions');
    Route::get('/sanctions/create', [AdminController::class, 'sanctionCreate'])->name('sanctions-create');
    Route::post('/sanctions/store', [AdminController::class, 'sanctionStore'])->name('sanctions-store');
    Route::put('/sanctions/{sanction}/update', [AdminController::class, 'sanctionUpdate'])->name('sanction-update');

    Route::get('/complaints', [AdminController::class, 'complaints'])->name('complaints');
    Route::get('/complaints/{complaint}', [AdminController::class, 'complaintShow'])->name('complaints-show');
    Route::put('/complaints/{complaint}/update', [AdminController::class, 'complaintUpdate'])->name('complaint-update');

    Route::get('/notifications', [AdminController::class, 'notifications'])->name('notifications');
    Route::get('/notifications/create', [AdminController::class, 'notificationsCreate'])->name('notifications-create');
    Route::post('/notifications/store', [AdminController::class, 'notificationsStore'])->name('notifications-store');
});

Route::middleware(['auth', 'role:productor'])->prefix('producer')->name('producer.')->group(function () {
    Route::get('/dashboard', [ProducerController::class, 'dashboard'])->name('dashboard');
    Route::get('/deliveries', [ProducerController::class, 'deliveries'])->name('deliveries');
    Route::get('/quality', [ProducerController::class, 'quality'])->name('quality');
    Route::get('/payments', [ProducerController::class, 'payments'])->name('payments');
    Route::get('/payments/{payment}', [ProducerController::class, 'paymentShow'])->name('payment-show');

    Route::get('/complaints', [ProducerController::class, 'complaints'])->name('complaints');
    Route::get('/complaints/create', [ProducerController::class, 'complaintsCreate'])->name('complaints-create');
    Route::post('/complaints/store', [ProducerController::class, 'complaintsStore'])->name('complaints-store');
    Route::get('/complaints/{complaint}', [ProducerController::class, 'complaintShow'])->name('complaints-show');

    Route::get('/notifications', [ProducerController::class, 'notifications'])->name('notifications');
    Route::put('/notifications/{notification}/read', [ProducerController::class, 'notificationRead'])->name('notification-read');

    Route::get('/profile', [ProducerController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [ProducerController::class, 'profileUpdate'])->name('profile-update');
});

Route::middleware(['auth', 'role:acopiador'])->prefix('collector')->name('collector.')->group(function () {
    Route::get('/dashboard', [CollectorController::class, 'dashboard'])->name('dashboard');
    Route::get('/routes', [CollectorController::class, 'routes'])->name('routes');
    Route::get('/routes/{route}', [CollectorController::class, 'routeShow'])->name('route-show');
    Route::put('/stops/{stop}/update', [CollectorController::class, 'routeUpdateStop'])->name('route-update-stop');

    Route::get('/deliveries', [CollectorController::class, 'deliveries'])->name('deliveries');
    Route::get('/deliveries/create', [CollectorController::class, 'deliveryCreate'])->name('delivery-create');
    Route::post('/deliveries/store', [CollectorController::class, 'deliveryStore'])->name('delivery-store');

    Route::get('/producers', [CollectorController::class, 'producers'])->name('producers');
    Route::get('/journal', [CollectorController::class, 'journal'])->name('journal');

    Route::get('/profile', [CollectorController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [CollectorController::class, 'profileUpdate'])->name('profile-update');
});

Route::middleware(['auth', 'role:control_calidad,admin,gerente'])->prefix('quality')->name('quality.')->group(function () {
    Route::get('/dashboard', [QualityController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [QualityController::class, 'reports'])->name('reports');
    Route::get('/reports/create', [QualityController::class, 'reportCreate'])->name('report-create');
    Route::post('/reports/store', [QualityController::class, 'reportStore'])->name('report-store');
    Route::post('/reports/ocr-scan', [QualityController::class, 'reportOcrScan'])->name('report-ocr-scan');
    Route::get('/reports/{report}', [QualityController::class, 'reportShow'])->name('report-show');

    Route::get('/profile', [QualityController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [QualityController::class, 'profileUpdate'])->name('profile-update');
});

Route::middleware(['auth', 'role:trabajador_planta'])->prefix('plant')->name('plant.')->group(function () {
    Route::get('/dashboard', [PlantController::class, 'dashboard'])->name('dashboard');

    Route::get('/batches', [PlantController::class, 'batches'])->name('batches');
    Route::get('/batches/{batch}', [PlantController::class, 'batchShow'])->name('batch-show');
    Route::put('/batches/{batch}/status', [PlantController::class, 'batchUpdateStatus'])->name('batch-update-status');

    Route::get('/production', [PlantController::class, 'production'])->name('production');
    Route::get('/production/create', [PlantController::class, 'productionCreate'])->name('production-create');
    Route::post('/production/store', [PlantController::class, 'productionStore'])->name('production-store');
    Route::get('/production/recipes/create', [PlantController::class, 'recipeCreate'])->name('recipe-create');
    Route::post('/production/recipes/store', [PlantController::class, 'recipeStore'])->name('recipe-store');

    Route::get('/sales', [PlantController::class, 'sales'])->name('sales');
    Route::get('/sales/create', [PlantController::class, 'salesCreate'])->name('sales-create');
    Route::post('/sales/store', [PlantController::class, 'salesStore'])->name('sales-store');

    Route::get('/inventory', [PlantController::class, 'inventory'])->name('inventory');
    Route::post('/inventory/adjust', [PlantController::class, 'inventoryAdjust'])->name('inventory-adjust');

    Route::get('/profile', [PlantController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [PlantController::class, 'profileUpdate'])->name('profile-update');
});

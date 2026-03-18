<?php

use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TraceabilityController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

    Route::get('/masters/units', [UnitController::class, 'index'])->name('units.index');
    Route::post('/masters/units', [UnitController::class, 'store'])->name('units.store');
    Route::get('/masters/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::post('/masters/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::get('/masters/plants', [PlantController::class, 'index'])->name('plants.index');
    Route::post('/masters/plants', [PlantController::class, 'store'])->name('plants.store');
    Route::get('/masters/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/masters/items', [ItemController::class, 'store'])->name('items.store');

    Route::get('/po', [PurchaseOrderController::class, 'index'])->name('po.index');
    Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
    Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');

    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');

    Route::get('/receiving', [GoodsReceiptController::class, 'index'])->name('receiving.index');
    Route::post('/receiving', [GoodsReceiptController::class, 'store'])->name('receiving.store');

    Route::get('/traceability', [TraceabilityController::class, 'index'])->name('traceability.index');
    Route::get('/reports/outstanding', [ReportController::class, 'outstanding'])->name('reports.outstanding');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

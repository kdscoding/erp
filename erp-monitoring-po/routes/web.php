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
    Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring');

    Route::middleware('role:admin|purchasing|purchasing_manager')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{id}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::patch('/suppliers/{id}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');

        Route::get('/masters/units', [UnitController::class, 'index'])->name('units.index');
        Route::post('/masters/units', [UnitController::class, 'store'])->name('units.store');
        Route::get('/masters/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::post('/masters/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/masters/plants', [PlantController::class, 'index'])->name('plants.index');
        Route::post('/masters/plants', [PlantController::class, 'store'])->name('plants.store');
        Route::get('/masters/items', [ItemController::class, 'index'])->name('items.index');
        Route::post('/masters/items', [ItemController::class, 'store'])->name('items.store');
        Route::get('/masters/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/masters/items/{id}', [ItemController::class, 'update'])->name('items.update');
        Route::patch('/masters/items/{id}/toggle-status', [ItemController::class, 'toggleStatus'])->name('items.toggle-status');

        Route::get('/po', [PurchaseOrderController::class, 'index'])->name('po.index');
        Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
        Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');
        Route::get('/po/{id}', [PurchaseOrderController::class, 'show'])->name('po.show');
        Route::patch('/po/items/{itemId}/schedule', [PurchaseOrderController::class, 'updateItemSchedule'])->name('po.items.schedule');
        Route::post('/po/items/{itemId}/cancel', [PurchaseOrderController::class, 'cancelItem'])->name('po.items.cancel');
        Route::post('/po/items/{itemId}/force-close', [PurchaseOrderController::class, 'forceCloseItem'])->name('po.items.force-close');
        Route::post('/po/{id}/cancel', [PurchaseOrderController::class, 'cancelPo'])->name('po.cancel');

        Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
        Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');

        Route::get('/reports/outstanding', [ReportController::class, 'outstanding'])->name('reports.outstanding');
    });

    Route::middleware('role:admin|warehouse')->group(function () {
        Route::get('/receiving', [GoodsReceiptController::class, 'index'])->name('receiving.index');
        Route::post('/receiving', [GoodsReceiptController::class, 'store'])->name('receiving.store');
    });

    Route::middleware('role:admin|viewer|compliance|purchasing|purchasing_manager|warehouse')->group(function () {
        Route::get('/traceability', [TraceabilityController::class, 'index'])->name('traceability.index');
        Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit.index');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

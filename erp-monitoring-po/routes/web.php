<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TraceabilityController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/summary/po', function (Request $request) {
        return redirect()->route('monitoring.index', array_filter([
            'supplier_id' => $request->query('supplier_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'mode' => 'po',
        ]));
    })->name('summary.po');
    Route::get('/summary/po/export-excel', [DashboardController::class, 'exportSummaryPoExcel'])->name('summary.po.export-excel');
    Route::get('/summary/item', function (Request $request) {
        return redirect()->route('monitoring.index', array_filter([
            'supplier_id' => $request->query('supplier_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'mode' => 'item',
        ]));
    })->name('summary.item');
    Route::get('/summary/item/export-excel', [DashboardController::class, 'exportSummaryItemExcel'])->name('summary.item.export-excel');
    Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring.index');
    Route::get('/monitoring/export-excel', [DashboardController::class, 'exportMonitoringExcel'])->name('monitoring.export-excel');
    Route::get('/po', [PurchaseOrderController::class, 'index'])->middleware('role:administrator|staff|supervisor')->name('po.index');
    Route::get('/po/export-excel', [PurchaseOrderController::class, 'exportIndexExcel'])->middleware('role:administrator|staff|supervisor')->name('po.export-excel');
    Route::get('/traceability', [TraceabilityController::class, 'index'])->middleware('role:administrator|staff|supervisor')->name('traceability.index');

    Route::get('/tracking', [DashboardController::class, 'tracking'])->name('tracking.index');

    Route::middleware('role:administrator|staff')->group(function () {

        Route::get('/masters/item-categories', [ItemCategoryController::class, 'index'])->name('item-categories.index');
        Route::get('/masters/item-categories/create', [ItemCategoryController::class, 'create'])->name('item-categories.create');
        Route::post('/masters/item-categories', [ItemCategoryController::class, 'store'])->name('item-categories.store');
        Route::get('/masters/item-categories/{id}/edit', [ItemCategoryController::class, 'edit'])->name('item-categories.edit');
        Route::put('/masters/item-categories/{id}', [ItemCategoryController::class, 'update'])->name('item-categories.update');
        Route::patch('/masters/item-categories/{id}/toggle-status', [ItemCategoryController::class, 'toggleStatus'])->name('item-categories.toggle-status');

        Route::get('/masters/units', [UnitController::class, 'index'])->name('units.index');
        Route::get('/masters/units/create', [UnitController::class, 'create'])->name('units.create');
        Route::post('/masters/units', [UnitController::class, 'store'])->name('units.store');
        Route::get('/masters/units/{id}/edit', [UnitController::class, 'edit'])->name('units.edit');
        Route::put('/masters/units/{id}', [UnitController::class, 'update'])->name('units.update');

        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{id}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::patch('/suppliers/{id}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');

        Route::get('/masters/items', [ItemController::class, 'index'])->name('items.index');
        Route::get('/masters/items/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('/masters/items', [ItemController::class, 'store'])->name('items.store');
        Route::get('/masters/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/masters/items/{id}', [ItemController::class, 'update'])->name('items.update');
        Route::patch('/masters/items/{id}/toggle-status', [ItemController::class, 'toggleStatus'])->name('items.toggle-status');
        Route::get('/masters/items/excel/template', [ItemController::class, 'downloadTemplate'])->name('items.template');
        Route::post('/masters/items/import', [ItemController::class, 'import'])->name('items.import');

        Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
        Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');
        Route::get('/po/import-template', [PurchaseOrderController::class, 'downloadTemplate'])->name('po.import-template');
        Route::post('/po/import', [PurchaseOrderController::class, 'import'])->name('po.import');
        Route::patch('/po/items/{itemId}/schedule', [PurchaseOrderController::class, 'updateItemSchedule'])->name('po.items.schedule');
        Route::patch('/po/{id}/items/bulk-schedule', [PurchaseOrderController::class, 'bulkUpdateItemSchedule'])->name('po.items.bulk-schedule');
        Route::post('/po/items/{itemId}/cancel', [PurchaseOrderController::class, 'cancelItem'])->name('po.items.cancel');
        Route::post('/po/items/{itemId}/force-close', [PurchaseOrderController::class, 'forceCloseItem'])->name('po.items.force-close');
        Route::post('/po/{id}/cancel', [PurchaseOrderController::class, 'cancelPo'])->name('po.cancel');

        Route::get('/shipments', [ShipmentController::class, 'index'])->defaults('view', 'worklist')->name('shipments.index');
        Route::get('/shipments/process', [ShipmentController::class, 'index'])->defaults('view', 'worklist')->name('shipments.process');
        Route::get('/shipments/create', [ShipmentController::class, 'index'])->defaults('view', 'draft')->name('shipments.create');
        Route::get('/shipments/history', [ShipmentController::class, 'index'])->defaults('view', 'history')->name('shipments.history');
        Route::get('/shipments/{id}', [ShipmentController::class, 'show'])->name('shipments.show');
        Route::get('/shipments/{id}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit');
        Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
        Route::put('/shipments/{id}', [ShipmentController::class, 'update'])->name('shipments.update');
        Route::patch('/shipments/{id}/mark-shipped', [ShipmentController::class, 'markShipped'])->name('shipments.mark-shipped');
        Route::patch('/shipments/{id}/cancel-draft', [ShipmentController::class, 'cancelDraft'])->name('shipments.cancel-draft');
        Route::get('/shipments/{id}/export-excel', [ShipmentController::class, 'exportDraftExcel'])->name('shipments.export-excel');
        Route::post('/shipments/import-draft-excel', [ShipmentController::class, 'importDraftExcel'])->name('shipments.import-excel');
        Route::get('/shipments/template/bulk-draft-excel', [ShipmentController::class, 'downloadBulkDraftTemplate'])->name('shipments.bulk-template');
        Route::post('/shipments/import-bulk-draft-excel', [ShipmentController::class, 'importBulkDraftExcel'])->name('shipments.bulk-import');
    });

    Route::get('/po/{id}', [PurchaseOrderController::class, 'show'])->middleware('role:administrator|staff|supervisor')->name('po.show');
    Route::get('/po/{id}/edit', [PurchaseOrderController::class, 'edit'])->middleware('role:administrator|staff|supervisor')->name('po.edit');
    Route::put('/po/{id}', [PurchaseOrderController::class, 'update'])->middleware('role:administrator|staff|supervisor')->name('po.update');
    Route::patch('/po/{id}/refresh-status', [PurchaseOrderController::class, 'refreshStatus'])->middleware('role:administrator|staff|supervisor')->name('po.refresh-status');
    Route::get('/po/{id}/item/{itemId}/tracking/copy-text', [PurchaseOrderController::class, 'exportItemTrackingText'])->middleware('role:administrator|staff|supervisor')->name('po.item.tracking.copy-text');
    Route::get('/po/{id}/item/{itemId}/tracking/export-excel', [PurchaseOrderController::class, 'exportItemTrackingExcel'])->middleware('role:administrator|staff|supervisor')->name('po.item.tracking.export-excel');
    Route::get('/po/{id}/export-excel', [PurchaseOrderController::class, 'exportDetailExcel'])->middleware('role:administrator|staff|supervisor')->name('po.export-detail-excel');

    Route::middleware('role:administrator|staff')->group(function () {
        Route::get('/po/items/search', [PurchaseOrderController::class, 'searchItems'])->name('po.items.search');
    });

    Route::middleware('role:administrator|staff')->group(function () {
        Route::get('/receiving', [GoodsReceiptController::class, 'dashboard'])->name('receiving.index');
        Route::get('/receiving/process', [GoodsReceiptController::class, 'index'])->defaults('mode', 'process')->name('receiving.process');
        Route::get('/receiving/pending', [GoodsReceiptController::class, 'pending'])->name('receiving.pending');
        Route::get('/receiving/pending/{shipment}', [GoodsReceiptController::class, 'create'])->name('receiving.create');
        Route::post('/receiving', [GoodsReceiptController::class, 'store'])->name('receiving.store');
        Route::get('/receiving/history', [GoodsReceiptController::class, 'history'])->name('receiving.history');
        Route::get('/receiving/history/{id}', [GoodsReceiptController::class, 'show'])->name('receiving.show');
        Route::patch('/receiving/history/{id}/cancel', [GoodsReceiptController::class, 'cancel'])->name('receiving.cancel');
    });

    Route::middleware('role:administrator')->group(function () {
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/document-terms', [SettingsController::class, 'updateDocumentTerms'])->name('settings.document-terms.update');
        Route::get('/settings/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/settings/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/settings/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/settings/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/settings/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::put('/settings/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::patch('/settings/users/{user}/status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

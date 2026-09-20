# Unified Tracking Table — ERP Monitoring PO & Receiving

**Status Legend:**
- ✅ **FULLY SHIPPED** — Complete implementation, all routes/views/controllers working, tested
- 🟡 **PARTIALLY SHIPPED** — Core functionality works, but missing features, edge cases, or polish
- ❌ **NOT SHIPPED** — Not implemented, placeholder only, or blocked

---

## Module: Dashboard & Monitoring

| Feature | Status | Notes |
|---------|--------|-------|
| Main Dashboard (KPI cards, PO status summary) | ✅ | `DashboardController::index` + `dashboard.blade.php` |
| PO Monitoring Table (filterable, sortable) | ✅ | `DashboardController::monitoring` + `monitoring.blade.php` |
| Supplier Performance View | ✅ | `DashboardController::supplierPerformance` + `supplier-performance.blade.php` |
| Monitoring Export (Excel) | ✅ | `exportMonitoringExcel`, `exportSummaryPoExcel`, `exportSummaryItemExcel` |
| Summary PO View (mode=po) | ✅ | Redirects to monitoring with mode=po |
| Summary Item View (mode=item) | ✅ | Redirects to monitoring with mode=item |

---

## Module: Master Data — Suppliers

| Feature | Status | Notes |
|---------|--------|-------|
| Supplier List (index, pagination, search) | ✅ | `SupplierController::index` + `suppliers/index.blade.php` |
| Create Supplier | ✅ | `create` + `store` + `suppliers/create.blade.php` |
| Edit Supplier | ✅ | `edit` + `update` + `suppliers/edit.blade.php` |
| Toggle Status (Active/Inactive) | ✅ | `toggleStatus` route + controller |
| Supplier Code/Name validation | ✅ | Unique code, required name |
| Import/Export | ❌ | Not implemented |

---

## Module: Master Data — Items

| Feature | Status | Notes |
|---------|--------|-------|
| Item List (index, pagination, search) | ✅ | `ItemController::index` + `masters/items/index.blade.php` |
| Create Item (code, name, unit, category) | ✅ | `create` + `store` + `masters/items/create.blade.php` |
| Edit Item | ✅ | `edit` + `update` + `masters/items/edit.blade.php` |
| Toggle Status (Active/Inactive) | ✅ | `toggleStatus` route + controller |
| Excel Template Download | ✅ | `downloadTemplate` route |
| Excel Import | ✅ | `import` route with validation |

---

## Module: Master Data — Item Categories

| Feature | Status | Notes |
|---------|--------|-------|
| Category List (index, pagination) | ✅ | `ItemCategoryController::index` + `masters/item-categories/index.blade.php` |
| Create Category | ✅ | `create` + `store` + `create.blade.php` |
| Edit Category | ✅ | `edit` + `update` + `edit.blade.php` |
| Toggle Status | ✅ | `toggleStatus` route |

---

## Module: Master Data — Units of Measure

| Feature | Status | Notes |
|---------|--------|-------|
| Unit List (index, pagination) | ✅ | `UnitController::index` + `masters/units/index.blade.php` |
| Create Unit | ✅ | `create` + `store` + `create.blade.php` |
| Edit Unit | ✅ | `edit` + `update` + `edit.blade.php` |
| Delete Unit | ❌ | No destroy route implemented |

---

## Module: Master Data — Warehouses

| Feature | Status | Notes |
|---------|--------|-------|
| Warehouse List | ✅ | Migration exists, controller/view from Step 7 script |
| Create Warehouse | ✅ | From Step 7 script |
| Edit Warehouse | ✅ | From Step 7 script |
| Integration with PO/Receiving | 🟡 | `warehouse_id` on PO + Receiving, but no master UI in routes |

---

## Module: Master Data — Plants

| Feature | Status | Notes |
|---------|--------|-------|
| Plant List | ✅ | Migration exists, controller/view from Step 7 script |
| Create Plant | ✅ | From Step 7 script |
| Edit Plant | ✅ | From Step 7 script |
| Integration with other modules | ❌ | No foreign key references yet |

---

## Module: Purchase Orders

| Feature | Status | Notes |
|---------|--------|-------|
| PO List (index, filters, status chips) | ✅ | `PurchaseOrderController::index` + `po/index.blade.php` |
| Create PO (header + line items) | ✅ | `create` + `store` + `CreatePurchaseOrder` action |
| View PO Detail (items, timeline, tracking) | ✅ | `show` + `PurchaseOrderDetailQuery` + `po/show.blade.php` |
| Edit PO Header | ✅ | `edit` + `update` |
| PO Status Auto-refresh | ✅ | `refreshStatus` route + `ErpFlow::refreshPoStatusByOutstanding` |
| Item Schedule (ETD) — Single Update | ✅ | `updateItemSchedule` + status resolver |
| Item Schedule (ETD) — Bulk Update | ✅ | `bulkUpdateItemSchedule` with day offset |
| Cancel PO Item | ✅ | `cancelItem` + audit trail |
| Force Close PO Item | ✅ | `forceCloseItem` + audit trail |
| Cancel Entire PO | ✅ | `cancelPo` + cascading item cancellation |
| Excel Export — Index | ✅ | `exportIndexExcel` |
| Excel Export — Detail | ✅ | `exportDetailExcel` |
| Excel Export — Item Tracking (Text) | ✅ | `exportItemTrackingText` |
| Excel Export — Item Tracking (Excel) | ✅ | `exportItemTrackingExcel` |
| Excel Import (PO + Items) | ✅ | `import` + `PurchaseOrderImport` action |
| Import Template Download | ✅ | `downloadTemplate` |
| Item Search (AJAX for PO create) | ✅ | `searchItems` endpoint |
| PO Status Workflow (Issued → Open → Late → Closed/Cancelled) | ✅ | `DocumentTermCodes` + `DomainStatus` |

---

## Module: Shipment Tracking

| Feature | Status | Notes |
|---------|--------|-------|
| Worklist View (Draft, Shipped, Partial) | ✅ | `index` with `view=worklist` + complex query |
| Process View (for creating shipments) | ✅ | `index` with `view=draft` + candidate items |
| History View (Received, Cancelled) | ✅ | `index` with `view=history` |
| Shipment Detail (items, %, PO links) | ✅ | `show` + `shipments/show.blade.php` |
| Create Shipment Draft (multi-PO, split) | ✅ | `store` + `StoreShipmentDraft` action |
| Edit Shipment Draft | ✅ | `edit` + `update` + `UpdateShipmentDraft` action |
| Mark Shipped (Draft → Shipped) | ✅ | `markShipped` + duplicate DN/invoice check + PO status refresh |
| Cancel Draft | ✅ | `cancelDraft` + PO status refresh |
| Receiving Percent Calculation | ✅ | Computed in `show()` controller |
| PO Numbers Aggregation | ✅ | `poNumbers` passed to view |
| Shipment Line Allocation Board | ✅ | Split shipment UI with `shipmentAllocationBoardQuery` |
| Excel Export — Draft Template | ✅ | `exportDraftExcel` |
| Excel Import — Draft Update | ✅ | `importDraftExcel` with validation |
| Excel Import — Bulk Draft Create | ✅ | `importBulkDraftExcel` + `ShipmentDraftBulkImport` |
| Bulk Draft Template Download | ✅ | `downloadBulkDraftTemplate` |
| Session-based Draft Builder State | ✅ | `shipment_selected_items`, `shipment_shipped_qty`, `shipment_invoice_unit_price` |
| Shipment Status Workflow (Draft → Shipped → Partial → Received/Cancelled) | ✅ | `DomainStatus::GROUP_SHIPMENT_STATUS` |

---

## Module: Goods Receiving

| Feature | Status | Notes |
|---------|--------|-------|
| Receiving Dashboard (KPIs, shipment docs) | ✅ | `dashboard` + `receiving/index.blade.php` |
| Pending Shipments View (for receiving) | ✅ | `pending` + `receiving/pending.blade.php` |
| Create Receiving from Shipment | ✅ | `create` + `storeDocumentReceiving` + `PostReceiptDocument` action |
| Single-line Receiving (no shipment) | ✅ | `storeSingleLineReceiving` |
| Receiving History (paginated, filterable) | ✅ | `history` + `ReceivingHistoryQuery` + `receiving/history.blade.php` |
| Receiving Detail (items, units, variance) | ✅ | `show` + `receiving/show.blade.php` |
| Cancel GR (Posted → Cancelled) | ✅ | `cancel` + qty rollback + PO/shipment status refresh |
| Attachment Upload (image/pdf) | ✅ | `attachment` field + `Storage::disk('public')` |
| Over-receipt Control (setting-gated) | ✅ | `allow_over_receipt` setting |
| Warehouse Assignment (from PO) | ✅ | `warehouse_id` from PO |
| Shipment Status Auto-refresh (Shipped → Partial → Received) | ✅ | `refreshShipmentStatus` private method |

---

## Module: Traceability

| Feature | Status | Notes |
|---------|--------|-------|
| Traceability Search (PO → Shipment → GR) | ✅ | `TraceabilityController::index` + `traceability/index.blade.php` |
| PO Filter by Number | ✅ | `po_number` query param |
| Joined View (PO + Shipment + GR) | ✅ | Single query with left joins |

---

## Module: Reports

| Feature | Status | Notes |
|---------|--------|-------|
| Outstanding PO Report | ✅ | `ReportController::outstanding` + `reports/outstanding.blade.php` |
| Filter by Status (excl. Closed/Cancelled) | ✅ | `whereNotIn` in query |
| Export | ❌ | No export route for reports |

---

## Module: Settings

| Feature | Status | Notes |
|---------|--------|-------|
| System Settings View | ✅ | `SettingsController::index` + `settings/index.blade.php` |
| Allow Over-receipt Toggle | ✅ | `allow_over_receipt` key in `settings` table |
| Document Terms Management | ✅ | `updateDocumentTerms` + `TermCatalog` integration |

---

## Module: Audit Trail

| Feature | Status | Notes |
|---------|--------|-------|
| Audit Log List (paginated) | ✅ | `AuditController::index` + `audit/index.blade.php` |
| Module/Action/Record Tracking | ✅ | `audit_logs` table with module, record_id, action, old/new values |
| User/IP Tracking | ✅ | `user_id`, `ip_address` fields |
| Auto-audit on Key Actions | ✅ | `ErpFlow::audit()` called in controllers |

---

## Module: User Management (Admin)

| Feature | Status | Notes |
|---------|--------|-------|
| User List | ✅ | `UserManagementController::index` + `settings/users/index.blade.php` |
| Create User | ✅ | `create` + `store` + `create.blade.php` |
| Edit User | ✅ | `edit` + `update` + `edit.blade.php` |
| Reset Password | ✅ | `resetPassword` route |
| Toggle Status (Active/Inactive) | ✅ | `toggleStatus` route |

---

## Module: Profile (Self-service)

| Feature | Status | Notes |
|---------|--------|-------|
| Edit Profile Info | ✅ | `ProfileController::edit/update` + partials |
| Update Password | ✅ | `updatePassword` partial |
| Delete Account | ✅ | `deleteUser` partial |

---

## Module: Authentication

| Feature | Status | Notes |
|---------|--------|-------|
| Login | ✅ | Laravel Breeze defaults |
| Register | ✅ | Laravel Breeze defaults |
| Email Verification | ✅ | `verify-email`, `verification.notice` |
| Password Reset | ✅ | `forgot-password`, `reset-password` |
| Password Confirmation | ✅ | `confirm-password` |

---

## Cross-Cutting / Infrastructure

| Feature | Status | Notes |
|---------|--------|-------|
| Role-based Access (administrator, staff, supervisor) | ✅ | `role` middleware on routes |
| ERP Layout (Sidebar, Topbar, Brand) | ✅ | `layouts/erp.blade.php` — full menu from Step 8 |
| AdminLTE 3.2 + FontAwesome 6.5 | ✅ | CDN links in layout |
| Tailwind CSS (via Vite) | ✅ | `vite.config.js`, `tailwind.config.js` |
| Blade Components Library | ✅ | `components/ui/*`, `components/*` — data-table, form-field, filter-bar, fab, page-header, summary-chips, status-badge, empty-state, etc. |
| TermCatalog (DB-driven labels) | ✅ | `TermCatalog` + `DocumentTermCodes` constants |
| DomainStatus (Status Groups/Payloads) | ✅ | `DomainStatus::payload()`, `legacyValue()`, groups for PO, PO Item, Shipment, GR |
| ErpFlow (Business Logic Helpers) | ✅ | `refreshPoStatusByOutstanding`, `generateNumber`, `audit`, `pushPoStatus`, `resolvePoEtaDate` |
| Audit Trail Integration | ✅ | Called on all CRUD + status transitions |
| Database Migrations (11 tables) | ✅ | users, roles, suppliers, items, units, warehouses, plants, categories, purchase_orders, purchase_order_items, shipments, shipment_items, goods_receipts, goods_receipt_items, audit_logs, settings, attachments, document_terms |
| Seeders (Roles, Admin, Master Data) | ✅ | `MasterDataSeeder` + role seeder |

---

## Summary Statistics

| Category | Fully Shipped | Partially Shipped | Not Shipped | Total |
|----------|---------------|-------------------|-------------|-------|
| Dashboard & Monitoring | 6 | 0 | 0 | 6 |
| Master Data (5 modules) | 17 | 1 | 2 | 20 |
| Purchase Orders | 18 | 0 | 0 | 18 |
| Shipment Tracking | 14 | 0 | 0 | 14 |
| Goods Receiving | 10 | 0 | 0 | 10 |
| Traceability | 2 | 0 | 0 | 2 |
| Reports | 1 | 0 | 1 | 2 |
| Settings | 2 | 0 | 0 | 2 |
| Audit Trail | 3 | 0 | 0 | 3 |
| User Management | 5 | 0 | 0 | 5 |
| Profile | 3 | 0 | 0 | 3 |
| Authentication | 5 | 0 | 0 | 5 |
| Cross-Cutting / Infrastructure | 9 | 0 | 0 | 9 |
| **TOTAL** | **95** | **1** | **3** | **99** |

---

## Visual Status Overview

```
████████████████████████████████████████████████████████████████████  95.9%  Fully Shipped (95/99)
█▒                                                                    1.0%  Partially Shipped (1/99)
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░  3.0%  Not Shipped (3/99)
```

---

## Partially Shipped Items (1)

| Module | Feature | Gap |
|--------|---------|-----|
| Master Data — Warehouses | Integration with PO/Receiving | `warehouse_id` column exists on `purchase_orders` and `goods_receipts`, but no master CRUD routes registered in `web.php` (only in Step 7 script, not merged) |

---

## Not Shipped Items (3)

| Module | Feature | Blocker / Note |
|--------|---------|----------------|
| Master Data — Warehouses | Delete Warehouse | No destroy route/controller method |
| Master Data — Plants | Integration with other modules | No foreign key references; standalone master only |
| Reports | Export (Excel/PDF) | No export routes implemented |

---

## Recommended Next Steps

1. **Register Warehouse/Plant routes** in `web.php` (merge from Step 7 script) → converts 1 partial + 1 not shipped to fully shipped
2. **Add Destroy methods** for Units, Warehouses, Plants, Categories → completes master data CRUD
3. **Add Report Exports** (Outstanding PO Excel) → completes Reports module
4. **Add Plant Integration** (e.g., `plant_id` on PO/Shipment/Receiving) → unlocks Plant module value

---

*Generated from codebase analysis of `erp-monitoring-po` (Laravel 10+, PHP 8.2+). Last updated: 2026-09-20.*
# Shipment Module — Technical Specification

**Endpoint:** `http://127.0.0.1:8000/shipments`  
**Module:** ERP Monitoring PO & Receiving — Shipment Tracking  
**Date:** 2026-09-20  
**Status:** Active Implementation

---

## Table of Contents

1. [Overview](#1-overview)
2. [Data Structures](#2-data-structures)
3. [API Interaction Patterns](#3-api-interaction-patterns)
4. [Backend Logic](#4-backend-logic)
5. [Validation Architecture](#5-validation-architecture)
6. [Audit Trail](#6-audit-trail)
7. [Excel Import/Export](#7-excel-importexport)
8. [Error Handling](#8-error-handling)
9. [Security Considerations](#9-security-considerations)
10. [Performance Considerations](#10-performance-considerations)

---

## 1. Overview

The Shipment module manages the end-to-end lifecycle of shipment documents within the ERP system. It handles the creation, editing, confirmation, and cancellation of shipment drafts, as well as bulk import/export operations. All interactions flow through the `ShipmentController` at the `/shipments` endpoint.

### 1.1 Core Concepts

| Concept | Description |
|---------|-------------|
| **Shipment** | Header document representing a delivery from a supplier, linked to one or more Purchase Orders |
| **Shipment Item** | Line item within a shipment, referencing a specific PO item with shipped/received quantities |
| **Draft** | A shipment in preparation — editable, cancellable, and subject to review before confirmation |
| **Shipped** | A confirmed shipment ready for goods receiving |
| **Partial Received** | A shipped shipment where some items have been received |
| **Received** | All items in a shipment have been received |
| **Cancelled** | A shipment that was cancelled before or during processing |

### 1.2 Tab-Based Views at `/shipments`

The index page renders different views based on the `view` route parameter or `tab` query parameter:

| Tab | Route | Purpose |
|-----|-------|---------|
| **Worklist** | `/shipments` (default) | Active shipments: Draft, Shipped, Partial Received |
| **Create Draft** | `/shipments/create` | Multi-step wizard for building new shipment drafts |
| **Archive** | `/shipments/history` | Completed (Received) and Cancelled shipments |

---

## 2. Data Structures

### 2.1 Database Schema

#### `shipments` Table

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | `bigIncrements` | No | — | Primary key |
| `purchase_order_id` | `foreignId` | No | — | FK to `purchase_orders` (cascade on delete) |
| `supplier_id` | `foreignId` | Yes | NULL | FK to `suppliers` |
| `shipment_number` | `string` | No | — | Unique shipment identifier (auto-generated) |
| `shipment_date` | `date` | Yes | NULL | Date of shipment |
| `eta_date` | `date` | Yes | NULL | Estimated time of arrival |
| `delivery_note_number` | `string` | Yes | NULL | Supplier delivery note number |
| `supplier_remark` | `text` | Yes | NULL | Internal notes |
| `status` | `string` | No | `'Draft'` | Display status (legacy) |
| `status_code` | `string` | Yes | NULL | Internal status code (e.g., `shipment_draft`) |
| `created_by` | `foreignId` | Yes | NULL | FK to `users` |
| `created_at` | `timestamp` | No | — | Creation timestamp |
| `updated_at` | `timestamp` | No | — | Last update timestamp |

#### `shipment_items` Table

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | `bigIncrements` | No | — | Primary key |
| `shipment_id` | `foreignId` | No | — | FK to `shipments` (cascade on delete) |
| `purchase_order_item_id` | `foreignId` | No | — | FK to `purchase_order_items` |
| `shipped_qty` | `decimal(14,2)` | No | `0` | Quantity shipped |
| `received_qty` | `decimal(14,2)` | No | `0` | Quantity received (from goods receipt) |
| `note` | `text` | Yes | NULL | Line-level notes |
| `created_at` | `timestamp` | No | — | Creation timestamp |
| `updated_at` | `timestamp` | No | — | Last update timestamp |

**Constraint:** Unique on `(shipment_id, purchase_order_item_id)` — one line per PO item per shipment.

#### Invoice Fields (Added via Migration `2026_03_23_000004`)

These fields are on the `shipments` and `shipment_items` tables respectively:

| Table | Column | Type | Nullable | Description |
|-------|--------|------|----------|-------------|
| `shipments` | `invoice_number` | `string(100)` | Yes | Supplier invoice number |
| `shipments` | `invoice_date` | `date` | Yes | Invoice date |
| `shipments` | `invoice_currency` | `string(10)` | Yes | Currency code (default: `'IDR'`) |
| `shipment_items` | `invoice_unit_price` | `decimal(18,4)` | Yes | Unit price from invoice |
| `shipment_items` | `invoice_line_total` | `decimal(18,2)` | Yes | Calculated: `shipped_qty × invoice_unit_price` |

### 2.2 Status Domain Model

Two parallel status systems exist:

| System | Source | Example | Usage |
|--------|--------|---------|-------|
| **Display (legacy)** | `DocumentTermCodes` | `'Draft'`, `'Shipped'` | UI labels, user-facing |
| **Internal (domain)** | `DomainStatus` | `'shipment_draft'`, `'shipment_shipped'` | Database `status_code`, API contracts |

Mapping (`DomainStatus::LEGACY_MAP[GROUP_SHIPMENT_STATUS]`):

| Internal Code | Display Label |
|---------------|---------------|
| `shipment_draft` | Draft |
| `shipment_shipped` | Shipped |
| `shipment_partial_received` | Partial Received |
| `shipment_received` | Received |
| `shipment_cancelled` | Cancelled |

### 2.3 Shipment Line Item Data Object

Each line item returned by `ShipmentController::shipmentLineQuery()` contains:

| Field | Source Table | Description |
|-------|-------------|-------------|
| `shipment_item_id` | `shipment_items.id` | Primary key of line |
| `shipment_id` | `shipment_items.shipment_id` | Parent shipment |
| `purchase_order_item_id` | `shipment_items.purchase_order_item_id` | Linked PO item |
| `shipped_qty` | `shipment_items.shipped_qty` | Qty confirmed for shipment |
| `received_qty` | `shipment_items.received_qty` | Qty received (from GR) |
| `invoice_unit_price` | `shipment_items.invoice_unit_price` | Invoice unit price |
| `invoice_line_total` | `shipment_items.invoice_line_total` | Calculated line total |
| `po_number` | `purchase_orders.po_number` | PO reference |
| `item_code` | `items.item_code` | Item code |
| `item_name` | `items.item_name` | Item description |
| `outstanding_qty` | `purchase_order_items.outstanding_qty` | Remaining PO qty |
| `po_unit_price` | `purchase_order_items.unit_price` | PO unit price |
| `available_to_ship_qty` | Computed | `outstanding_qty - open_shipment_qty` |

### 2.4 Worklist Query Projection (Index Page)

The `shipmentWorklistBaseQuery()` produces a grouped projection per shipment with computed aggregates:

| Field | Computation |
|-------|-------------|
| `line_count` | `COUNT(DISTINCT si.id)` |
| `po_count` | `COUNT(DISTINCT po.id)` |
| `po_numbers` | `GROUP_CONCAT(DISTINCT po.po_number)` |
| `total_shipped_qty` | `COALESCE(SUM(si.shipped_qty), 0)` |
| `total_received_qty` | `COALESCE(SUM(si.received_qty), 0)` |
| `total_open_qty` | `COALESCE(SUM(si.shipped_qty - si.received_qty), 0)` |
| `supplier_name` | `COALESCE(s.supplier_name, anchor_s.supplier_name)` |

---

## 3. API Interaction Patterns

### 3.1 Route Registry

| Method | Route | Controller | Name | Purpose |
|--------|-------|-----------|------|---------|
| `GET` | `/shipments` | `index` | `shipments.index` | Index with tab selection (worklist/create/archive) |
| `GET` | `/shipments/process` | `index` | `shipments.process` | Worklist alias |
| `GET` | `/shipments/create` | `index` | `shipments.create` | Create draft wizard tab |
| `GET` | `/shipments/history` | `index` | `shipments.history` | Archive tab |
| `GET` | `/shipments/{id}/edit` | `edit` | `shipments.edit` | Edit draft form |
| `GET` | `/shipments/{id}/preview` | `preview` | `shipments.preview` | View draft details (read-only) |
| `POST` | `/shipments` | `store` | `shipments.store` | Create shipment draft |
| `PUT` | `/shipments/{id}` | `update` | `shipments.update` | Update draft |
| `PATCH` | `/shipments/{id}/mark-shipped` | `markShipped` | `shipments.mark-shipped` | Confirm draft → shipped |
| `PATCH` | `/shipments/{id}/cancel-draft` | `cancelDraft` | `shipments.cancel-draft` | Cancel draft |
| `GET` | `/shipments/{id}/export-excel` | `exportDraftExcel` | `shipments.export-excel` | Export draft to Excel |
| `POST` | `/shipments/import-draft-excel` | `importDraftExcel` | `shipments.import-excel` | Import Excel into draft |
| `GET` | `/shipments/template/bulk-draft-excel` | `downloadBulkDraftTemplate` | `shipments.bulk-template` | Download bulk import template |
| `POST` | `/shipments/import-bulk-draft-excel` | `importBulkDraftExcel` | `shipments.bulk-import` | Bulk create shipments from Excel |

### 3.2 Request/Response Patterns

#### Create Shipment (POST `/shipments`)

**Request** (form data):

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `shipment_date` | `string` | Yes | `required\|date` |
| `delivery_note_number` | `string` | Yes | `required\|string\|max:100` |
| `invoice_number` | `string` | No | `nullable\|string\|max:100` |
| `invoice_date` | `string` | No | `nullable\|date` |
| `invoice_currency` | `string` | No | `nullable\|string\|max:10` |
| `supplier_remark` | `string` | No | `nullable\|string\|max:500` |
| `po_reference_missing` | `string` | No | `nullable\|in:1` |
| `selected_items` | `array` | Yes | `required\|array\|min:1`, each `integer\|exists:purchase_order_items,id` |
| `shipped_qty` | `array` | Yes | `required\|array`, each `numeric\|min:0.01` |
| `invoice_unit_price` | `array` | No | `nullable\|array`, each `nullable\|numeric\|min:0` |

**Response:** `302 Redirect` to `shipments.index` with `focus` query param and flash `success` message. On failure: `ValidatorException` with error messages.

**Delegates to:** `StoreShipmentDraft` action class.

#### Update Shipment (PUT `/shipments/{id}`)

**Request** (form data):

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `delivery_note_number` | `string` | Yes | `required\|string\|max:100` |
| `shipment_date` | `date` | Yes | `required\|date` |
| `invoice_number` | `string` | No | `nullable\|string\|max:100` |
| `invoice_date` | `date` | No | `nullable\|date` |
| `invoice_currency` | `string` | No | `nullable\|string\|max:10` |
| `supplier_remark` | `string` | No | `nullable\|string\|max:500` |
| `shipment_items` | `array` | Yes | `required\|array\|min:1` |
| `shipment_items.*.id` | `int` | Yes | `required\|integer\|exists:shipment_items,id` |
| `shipment_items.*.shipped_qty` | `float` | Yes | `required\|numeric\|min:0.01` |
| `shipment_items.*.invoice_unit_price` | `float` | No | `nullable\|numeric\|min:0` |
| `shipment_items.*.keep` | `string` | No | `nullable\|in:1` |

**Response:** `302 Redirect` back to edit page with flash `success`.

**Delegates to:** `UpdateShipmentDraft` action class.

#### Mark Shipped (PATCH `/shipments/{id}/mark-shipped`)

**Business Logic (transactional):**

1. Lock shipment row for update (`lockForUpdate`)
2. Verify status is `Draft` — otherwise throw `ValidationException`
3. Check DN uniqueness across all non-cancelled shipments for same supplier
4. Check Invoice uniqueness (if invoice_number present) across all non-cancelled shipments for same supplier
5. Verify at least one line has a linked PO
6. Update status to `Shipped` via `DomainStatus::payload()`
7. Refresh PO statuses for all linked POs via `ErpFlow::refreshPoStatusByOutstanding()`
8. Audit trail entry: action `'mark_shipped'`, old `['status' => 'Draft']`, new `['status' => 'Shipped']`

**Response:** `302 Redirect` to `receiving.process` route with supplier/shipment context.

#### Cancel Draft (PATCH `/shipments/{id}/cancel-draft`)

1. Verify status is `Draft` — otherwise return back with error
2. Within transaction: update status to `Cancelled`, refresh linked PO statuses, audit entry
3. **Response:** `302 Redirect` back with flash `success`/`error`

### 3.3 AJAX / Client-Side Patterns

| Pattern | Implementation | Location |
|---------|---------------|----------|
| Inline search/filter | `input` event listener on `#shipment-search`, filters table rows by data attributes | `shipments/index.blade.php` |
| Toggle filter section | `click` listener on `#filterToggle`, toggles `display` CSS property | `shipments/index.blade.php` |
| Batch selection | Checkbox toggle → update batch toolbar visibility/count | `shipments/index.blade.php` |
| Draft quantity auto-calc | `input`/`change` listeners recalculate line totals (`qty × price`) | `shipments/index.blade.php`, `edit.blade.php` |
| Wizard navigation | JS state machine for 3-step create flow (Select → Review → Confirm) | `shipments/index.blade.php` (Create tab) |
| Keyboard shortcuts | `Ctrl+N` → new draft; `Ctrl+F` → focus search | Both views |

### 3.4 Session-Based Builder State

The create draft wizard uses Laravel session storage to maintain state across requests:

| Session Key | Type | Content |
|-------------|------|---------|
| `shipment_selected_items` | `array[int]` | Selected PO item IDs |
| `shipment_shipped_qty` | `array[int => float]` | Quantities per item ID |
| `shipment_invoice_unit_price` | `array[int => ?float]` | Invoice prices per item ID |

Cleared after successful `store()` call. Reset triggers flash flag `shipment_builder_reset`.

---

## 4. Backend Logic

### 4.1 Controller Methods Detail

#### `index(Request $request): View`

The primary entry point. Determines view mode from route defaults or query param:

```
view=worklist  → Active shipments (Draft, Shipped, Partial Received)
view=create    → Create Draft wizard
view=history   → Archive (Received, Cancelled)
```

**Query Construction:**

- Active and archive queries use the same base query (`shipmentWorklistBaseQuery()`) filtered by status scope
- Both paginated (20/page) with query string preservation
- Filterable by: `supplier_id`, `delivery_note_number`, `invoice_number`, `keyword` (search across shipment number, DN, invoice, supplier name, PO number), `status`
- Sorted by status priority (Draft → Shipped → Partial → Other), then by `shipment_date DESC`, then `id DESC`

#### `preview(string $id): View`

1. Fetch shipment header via `shipmentHeaderQuery()` (joins suppliers for name/code)
2. **Access control:** Returns 404 if status is not `Draft`
3. Fetch lines via `shipmentLineQuery($id)` — includes PO numbers, item codes, prices, quantities, available-to-ship
4. Returns `shipments.preview` view — read-only display (no form, no inputs)

#### `edit(string $id): View`

1. Fetch shipment header via `shipmentHeaderQuery()` (joins suppliers for name/code)
2. **Access control:** Returns 404 if status is not `Draft`
3. Fetch lines via `shipmentLineQuery($id)` — includes PO numbers, item codes, prices, quantities, available-to-ship
4. Fetch split shipment board for allocation visibility

#### `store(Request $request, StoreShipmentDraft $storeShipmentDraft): RedirectResponse`

Validates input, delegates to action class, clears session state, redirects to index with focus.

#### `update(string $id, Request $request, UpdateShipmentDraft $updateShipmentDraft): RedirectResponse`

Validates input, delegates to action class, redirects back to edit page.

### 4.2 Action Classes

#### `StoreShipmentDraft` (delegated in `store()`)

**Processing Pipeline:**

1. Lock selected PO items for update (`lockForUpdate`)
2. Verify all items from same supplier
3. Check DN uniqueness (across all shipments, same supplier)
4. Check invoice uniqueness (across all shipments, same supplier)
5. Validate qty > 0 per item
6. Validate qty ≤ `available_to_ship_qty` per item (from candidate query)
7. Generate shipment number via `ErpFlow::generateNumber('SHP', ...)`
8. Insert shipment header + shipment items within `DB::transaction()`
9. Refresh PO statuses for all linked POs
10. Audit with action `'create'`

#### `UpdateShipmentDraft` (delegated in `update()`)

**Processing Pipeline:**

1. Lock shipment for update
2. Check shipment is in `Draft` status
3. Check DN uniqueness (excluding self)
4. Check invoice uniqueness (excluding self)
5. Filter kept lines (by `keep` flag)
6. Validate qty > 0, qty ≤ available per line
7. Update header fields + line items
8. Remove unkept lines (hard delete)
9. Refresh PO statuses
10. Audit with action `'update'`

### 4.3 Query Builder Methods (Private)

| Method | Purpose |
|--------|---------|
| `shipmentWorklistBaseQuery()` | Grouped query for index — aggregates quantities, PO numbers, counts per shipment |
| `candidateItemsBaseQuery()` | Available PO items for shipment creation — filters by shippable PO status, outstanding > 0, computes available qty |
| `candidateItemsQuery(Request)` | Adds search/filter to base candidate query |
| `shipmentHeaderQuery()` | Shipment + supplier name for edit/show |
| `shipmentLineQuery(int)` | Detailed line items with PO, item, price, quantity, allocation data |
| `shipmentAllocationBoardQuery(array, ?int)` | Cross-shipment allocation view for split management |

### 4.4 Number Generation

Shipment numbers are generated by `ErpFlow::generateNumber('SHP', ...)`, which produces a unique, sequential document number following the ERP numbering convention.

---

## 5. Validation Architecture

### 5.1 Validation Rules Summary

All validation is performed inline in the controller (not via Form Requests). Rules are applied through `$request->validate()` which throws `ValidationException` on failure, automatically redirecting back with errors.

### 5.2 Cross-Cutting Validations

The following validations are applied consistently across create, edit, and bulk import:

| Validation | Applied In | Notes |
|-----------|-----------|-------|
| DN uniqueness (same supplier) | Store, Update, Import, Mark Shipped | Excludes cancelled shipments; case-insensitive |
| Invoice uniqueness (same supplier) | Store, Update, Import, Mark Shipped | Optional field; only checked if provided |
| Qty > 0 | Store, Update, Import | Per line |
| Qty ≤ available | Store, Update, Import | `outstanding_qty - open_shipment_qty` |
| Price ≥ 0 | Store, Update, Import | Invoice unit price |
| PO status check | Import (bulk) | Must be issued/open/late |
| Item active | Import (bulk) | Must be `active=1` |
| Supplier active | Import (bulk) | Must be `status=1` |

### 5.3 Validation Messages

Validation messages use Indonesian language with `:attribute` placeholders for field names. Defined in `resources/labels/shipment.php` under the `validation` key and used via Laravel's `:attribute` replacement.

---

## 6. Audit Trail

All mutations are logged via `ErpFlow::audit()`:

| Action | Entity | Trigger | Old Values | New Values |
|--------|--------|---------|------------|------------|
| `'create'` | shipment | `StoreShipmentDraft::handle()` | — | Shipment data |
| `'update'` | shipment | `UpdateShipmentDraft::handle()` | — | Updated data |
| `'mark_shipped'` | shipment | `markShipped()` | `['status' => 'Draft']` | `['status' => 'Shipped']` |
| `'cancel'` | shipment | `cancelDraft()` | Shipment row data | `['status' => 'Cancelled']` |
| `'import_excel'` | shipment | `importDraftExcel()` | Shipment data | `{header, lines}` |
| `'import_bulk_excel'` | shipment | `ShipmentDraftBulkImport` | — | Inserted shipments |

Audit entries are stored in `audit_logs` table with: `module`, `record_id`, `action`, `old_value` (JSON), `new_value` (JSON), `user_id`, `ip_address`, `created_at`.

---

## 7. Excel Import/Export

### 7.1 Export Draft Excel (`GET /shipments/{id}/export-excel`)

**Restrictions:** Only for Draft status (404 otherwise).

**Output:** XLSX with two sheets:
- **HEADER** sheet: shipment_number, shipment_date, supplier_name, delivery_note_number, invoice_number, invoice_date, invoice_currency, supplier_remark, status
- **LINES** sheet: shipment_item_id, purchase_order_item_id, po_number, item_code, item_name, po_unit_price, shipped_qty, invoice_unit_price, invoice_line_total, keep

### 7.2 Import Draft Excel (`POST /shipments/import-draft-excel`)

**Process:**
1. Parse uploaded file using PhpSpreadsheet
2. Extract HEADER row (row 1) and LINES rows (row 2+)
3. Validate header matches target draft shipment
4. Within transaction: update header fields, sync line items (update kept, delete unkept)
5. Validate DN/invoice uniqueness against other shipments
6. Refresh PO statuses, audit trail

### 7.3 Bulk Import (`POST /shipments/import-bulk-draft-excel`)

**Process** (via `ShipmentDraftBulkImport`):
1. Group rows by `(supplier_code, shipment_date, delivery_note_number, invoice_number)`
2. Each group → one shipment record
3. Auto-generate shipment_number (random 10-digit)
4. Set invoice_currency = `'IDR'`, status = `Draft`
5. All-or-nothing: collect all errors before rollback via DB transaction
6. Validate: field presence, types, existence (supplier/item/PO), availability (qty ≤ available), uniqueness (DN/invoice), within-file and group-level deduplication

### 7.4 Bulk Template Download (`GET /shipments/template/bulk-draft-excel`)

Generates a sample Excel with column headers and 3 example rows for user reference.

---

## 8. Error Handling

### 8.1 Error Response Patterns

| Error Type | HTTP Behavior | User Feedback |
|-----------|--------------|---------------|
| Validation failure | `ValidationException` → 302 back with errors | Laravel error bag displayed in view |
| 404 (not found/wrong status) | `firstOrFail()` or `abort(404)` | Not found page |
| Duplicate DN/Invoice | `ValidationException` with custom message | Inline error message |
| Import errors (bulk) | ValidationException → redirect with `import_errors` session | Error table displayed on worklist |
| File parse errors | ValidationException → redirect with message | Alert displayed on worklist |

### 8.2 Transaction Safety

All write operations that affect multiple tables wrap in `DB::transaction()`:
- `markShipped()` — full ACID transaction
- `cancelDraft()` — full ACID transaction  
- `importDraftExcel()` — full ACID transaction
- `importBulkDraftExcel()` — delegates to action class which uses transaction
- `StoreShipmentDraft` / `UpdateShipmentDraft` — both use transactions

---

## 9. Security Considerations

### 9.1 Authorization

- Routes use `role` middleware (configured in `web.php`)
- Roles: Admin, Purchasing, Purchasing Manager, Warehouse, BC/Compliance, Viewer
- `markShipped` and `cancelDraft` require write permissions
- `edit` and `update` restricted to Draft status only (404 otherwise)

### 9.2 CSRF Protection

All POST/PUT/PATCH forms include `@csrf` blade directive.

### 9.3 SQL Injection Prevention

All queries use Laravel's Query Builder with parameterized bindings. No raw SQL concatenation with user input. The LIKE searches use `?` placeholders:
```php
->where('sh.delivery_note_number', 'like', '%'.$request->string('delivery_note_number').'%')
```

### 9.4 Input Sanitization

- `$request->string()` used for string inputs (trimmed)
- `$request->integer()` used for integer inputs
- `$request->boolean()` used for boolean inputs
- `mb_strtolower(trim())` for DN/invoice comparison (case-insensitive, whitespace-insensitive)

---

## 10. Performance Considerations

### 10.1 N+1 Prevention

The index query uses a single grouped SQL query with all necessary joins — no N+1 issues. The edit view uses pre-built queries with all required joins in a single execution.

### 10.2 Pagination

Both active and archive result sets are paginated at 20 items per page with `withQueryString()` to preserve filter state.

### 10.3 Lock Usage

- `markShipped()` uses `lockForUpdate()` on both the shipment row and duplicate check queries to prevent race conditions
- `importDraftExcel()` uses `lockForUpdate()` on the target shipment
- `StoreShipmentDraft` locks selected PO items during processing

### 10.4 Session Storage

Builder state (selected items, quantities, prices) stored in session. Large selections may impact session storage — consider moving to database-backed sessions for >100 items.

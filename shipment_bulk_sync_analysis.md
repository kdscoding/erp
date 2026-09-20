# Shipment Bulk Import ↔ Manual Input/Edit Synchronization Analysis

## Date: 2026-09-20

---

## 1. BULK IMPORT PROCESS (ShipmentDraftBulkImport.php)

### Data Structure (flat file row):
| Column | Required | Type | Validation |
|--------|----------|------|------------|
| shipment_date | Yes | date | Valid date, parsed by Carbon |
| supplier_code | Yes | string | Must exist in suppliers table, status=1, case-insensitive |
| DN | Yes | string | Delivery note number |
| invoice_number | No | string | Nullable |
| invoice_date | No | date | Valid date if provided |
| supplier_remark | No | string | Nullable |
| po_number | Yes | string | Must match PO with item for that supplier, status IN (issued, open, late) |
| item_code | Yes | string | Must exist in items table, active=1 |
| invoice_unit_price | No | float | Non-negative if provided |
| qty_pengiriman | Yes | float | Numeric, > 0, ≤ available_to_ship_qty |

### Validation Pipeline (per row):
1. Field presence: shipment_date, supplier_code, DN, po_number, item_code, qty required
2. Type/format: dates valid via Carbon, qty numeric > 0, price non-negative
3. Existence: supplier (active), item (active), PO+item+supplier (shippable)
4. Availability: qty ≤ (outstanding_qty - open_shipment_qty - allocated_in_file)
5. Uniqueness: DN not used by other shipments (same supplier), invoice not used by other shipments (same supplier)
6. Within-file: DN not duplicated within the same file for same supplier
7. Group-level: No PO item appears twice within the same shipment group
8. All-or-nothing: All errors collected before rollback via DB transaction

### Processing Pipeline:
1. Group rows by (supplier_code | shipment_date | delivery_note_number | invoice_number)
2. Each group → one shipment record
3. Auto-generate shipment_number (random 10-digit)
4. Set invoice_currency = 'IDR'
5. Set status = SHIPMENT_DRAFT
6. Insert shipment_items (one per row in group)
7. invoice_line_total = shipped_qty × invoice_unit_price (null if price null)
8. Refresh PO statuses
9. Audit with action 'import_bulk_excel'

---

## 2. MANUAL CREATE PROCESS (create.blade.php + StoreShipmentDraft.php)

### Current Data Structure:
- Header: selected_items[], delivery_note_number, shipment_date, invoice_number, invoice_date, invoice_currency, supplier_remark, po_reference_missing
- Lines: shipped_qty[poi_id], invoice_unit_price[poi_id]

### Current Validation (controller):
- shipment_date: required|date
- delivery_note_number: required|string|max:100
- invoice_number: nullable|string|max:100
- invoice_date: nullable|date
- invoice_currency: nullable|string|max:10
- supplier_remark: nullable|string|max:500
- selected_items: required|array|min:1, each integer|exists:purchase_order_items,id
- shipped_qty: required|array
- invoice_unit_price: nullable|array

### Current Processing (StoreShipmentDraft):
1. Lock selected PO items for update
2. Verify all from same supplier
3. Check DN uniqueness
4. Check invoice uniqueness
5. Validate qty > 0 per item
6. Validate qty ≤ available_to_ship_qty per item (from candidate query)
7. Generate shipment number via ErpFlow::generateNumber('SHP', ...)
8. Insert shipment + shipment_items
9. Refresh PO statuses
10. Audit with action 'create'

---

## 3. MANUAL EDIT PROCESS (edit.blade.php + UpdateShipmentDraft.php)

### Current Data Structure:
- Header: delivery_note_number, shipment_date, invoice_number, invoice_date, invoice_currency, supplier_remark
- Lines: shipment_items[] [id, keep, shipped_qty, invoice_unit_price]

### Current Validation (controller):
- Same as create + shipment_items validation

### Current Processing (UpdateShipmentDraft):
1. Lock shipment for update
2. Check shipment is Draft
3. Check DN uniqueness (excluding self)
4. Check invoice uniqueness (excluding self)
5. Filter kept lines
6. Validate qty > 0, qty ≤ available_to_ship_qty per line
7. Update header + lines
8. Remove unkept lines
9. Refresh PO statuses
10. Audit with action 'update'

---

## 4. GAP ANALYSIS

### 4.1 Data Structure Gaps

| Aspect | Bulk Import | Manual Create | Manual Edit | Sync Needed |
|--------|------------|---------------|-------------|-------------|
| Supplier identification | supplier_code (explicit) | Implicit via selected_items | Implicit via existing shipment | Add explicit supplier_code to create form |
| Item identification | item_code (explicit) | Implicit via selected_items | Shown via line data | Add item_code display |
| PO identification | po_number (per row) | Not shown per line | Shown via line data | Add po_number to line display |
| invoice_currency | Hardcoded 'IDR' | Field with default 'IDR' | Field with existing value | Align: keep field, always default 'IDR' |
| Date validation | Carbon parse | Laravel date rule | Laravel date rule | Align: use Carbon parse |

### 4.2 Validation Gaps (Critical)

| Validation | Bulk | Manual Create | Manual Edit | Sync Needed |
|-----------|------|---------------|-------------|-------------|
| Supplier active | ✅ status=1 | ❌ Not checked | ❌ Not checked | ADD to both |
| Item active | ✅ active=1 | ❌ Not checked at submit | ❌ Not checked | ADD to both |
| PO open status | ✅ issued/open/late | ❌ Not re-checked | ❌ Not re-checked | ADD to both |
| PO outstanding > 0 | ✅ | ⚠️ Via candidate query only | ⚠️ Via existing data | ADD explicit check |
| Available qty formula | ✅ outstanding - open shipments | ⚠️ From query, different formula | ⚠️ From query, different formula | SYNC formula |
| DN uniqueness (DB) | ✅ | ✅ | ✅ | Already synced |
| Invoice uniqueness (DB) | ✅ | ✅ | ✅ | Already synced |
| Qty > 0 | ✅ | ✅ Via action | ✅ Via action | Already synced |
| Price ≥ 0 | ✅ | ❌ Not checked | ❌ Not checked | ADD to both |
| Invoice date format | ✅ Carbon | ✅ Laravel date | ✅ Laravel date | Minor sync |

### 4.3 Processing Logic Gaps

| Aspect | Bulk | Manual Create | Manual Edit | Sync Needed |
|--------|------|---------------|-------------|-------------|
| Shipment number | Random 10-digit | ErpFlow generateNumber | N/A (existing) | Document difference (keep ErpFlow for manual) |
| invoice_currency | Always 'IDR' | Default 'IDR' | Existing value | Keep field, ensure 'IDR' default |
| invoice_line_total | round(qty × price, 2) | round(qty × price, 2) | round(qty × price, 2) | Already synced ✅ |
| Audit action | 'import_bulk_excel' | 'create' | 'update' | Keep different (appropriate) |
| Transaction wrapping | ✅ DB::transaction | ✅ DB::transaction | ✅ DB::transaction | Already synced ✅ |
| PO status refresh | ✅ | ✅ | ✅ | Already synced ✅ |
| All-or-nothing rollback | ✅ | ✅ | ✅ | Already synced ✅ |

### 4.4 UI/UX Gaps

| Aspect | Bulk Import Template | Manual Create | Manual Edit | Sync Needed |
|--------|---------------------|---------------|-------------|-------------|
| Supplier field | supplier_code text | Supplier select | Supplier display | Add supplier_code display |
| Item field | item_code text | Not editable | Not editable | Add item_code display |
| PO field | po_number text | Not shown per line | Not shown per line | Add po_number display |
| Bulk import on edit | N/A | N/A | Only single-row import modal | Add bulk import support |
| Error display | Row-by-row table | Laravel errors | Laravel errors | Add row-style errors |
| Template download | ✅ | ❌ | ❌ | Add to create page |

---

## 5. REDESIGN PLAN

### 5.1 StoreShipmentDraft.php Changes
1. Add supplier validation (active, exists by supplier_id)
2. Add item validation (active) for each selected item
3. Add PO validation (open status, outstanding > 0) for each item
4. Add price ≥ 0 validation
5. Use same available-to-ship formula as bulk import
6. Add supplier_code to audit data

### 5.2 UpdateShipmentDraft.php Changes
1. Add item active check per line
2. Add PO open status check per line
3. Add price ≥ 0 validation
4. Use same available-to-ship formula as bulk import
5. Add supplier_code validation

### 5.3 ShipmentController.php Changes
1. Update store() validation rules to match bulk import
2. Update update() validation rules to match bulk import
3. Add validation for supplier active status
4. Add validation for price ≥ 0

### 5.4 create.blade.php Changes
1. Add supplier_code display field
2. Add item_code and po_number to line items table
3. Add template download button
4. Add price ≥ 0 client validation
5. Add shipment_date format hint
6. Add bulk import option on create tab
7. Show validation errors matching bulk format

### 5.5 edit.blade.php Changes
1. Add supplier_code display
2. Add item_code and po_number to line items
3. Add bulk import capability (file upload)
4. Add price ≥ 0 client validation
5. Add available-to-ship info per line
6. Show validation errors matching bulk format

### 5.6 shipment.php Labels Changes
1. Add validation messages matching bulk import
2. Add field labels for new fields
3. Add price validation messages

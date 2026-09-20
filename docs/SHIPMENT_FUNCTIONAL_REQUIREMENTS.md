# Shipment Module — Functional Requirements

**Endpoint:** `http://127.0.0.1:8000/shipments`  
**Module:** ERP Monitoring PO & Receiving — Shipment Tracking  
**Date:** 2026-09-20  
**Status:** Design Specification

---

## Table of Contents

1. [Overview](#1-overview)
2. [Shipment States](#2-shipment-states)
3. [State Transition Rules](#3-state-transition-rules)
4. [Functional Requirements — Worklist](#4-functional-requirements--worklist)
5. [Functional Requirements — Create Draft](#5-functional-requirements--create-draft)
6. [Functional Requirements — Edit Draft](#6-functional-requirements--edit-draft)
7. [Functional Requirements — Mark Shipped](#7-functional-requirements--mark-shipped)
8. [Functional Requirements — Cancel Draft](#8-functional-requirements--cancel-draft)
9. [Functional Requirements — Import/Export](#9-functional-requirements--importexport)
10. [Shipment Draft Preview — Functional Specs](#10-shipment-draft-preview--functional-specs)
11. [Functional Requirements — View/Preview Draft](#11-functional-requirements--viewpreview-draft)
12. [Audit & Traceability](#12-audit--traceability)
13. [Non-Functional Requirements](#13-non-functional-requirements)

---

## 1. Overview

This document defines the functional requirements for the Shipment module, covering all operations accessible through the `/shipments` endpoint. The module manages shipment documents from creation through receiving, including draft management, confirmation, cancellation, and bulk operations.

---

## 2. Shipment States

### 2.1 State Definitions

| State | Code | Description | Editable |
|-------|------|-------------|----------|
| **Draft** | `shipment_draft` / `'Draft'` | Shipment being prepared; items selected from POs, quantities and invoice details being entered | Yes (edit, cancel) |
| **Shipped** | `shipment_shipped` / `'Shipped'` | Draft confirmed and locked; ready for goods receiving | No (receive only) |
| **Partial Received** | `shipment_partial_received` / `'Partial Received'` | Shipped shipment where some items have been received | No (continue receiving) |
| **Received** | `shipment_received` / `'Received'` | All items received; shipment complete | No (archive) |
| **Cancelled** | `shipment_cancelled` / `'Cancelled'` | Shipment cancelled before or during processing | No (archive) |

### 2.2 State Visual Representation

| State | Badge Color | Icon | Timeline Default |
|-------|------------|------|-----------------|
| Draft | Gray / `--stage-waiting` | 📝 | Collapsed: "Draft shipment — timeline will appear after confirmation." |
| Shipped | Blue / `--stage-shipped` | 🚚 | Expanded with milestone events |
| Partial Received | Orange / `--stage-partial` | 📦 | Expanded with receiving progress |
| Received | Green / `--stage-closed` | ✅ | Full timeline, all milestones complete |
| Cancelled | Red / `--stage-cancelled` | ❌ | Timeline showing cancellation event |

### 2.3 State Metadata

Each shipment stores both display status and internal code:

```php
// Display (user-facing)
'status' => 'Draft', 'Shipped', 'Partial Received', 'Received', 'Cancelled'

// Internal (domain/API)
'status_code' => 'shipment_draft', 'shipment_shipped', 'shipment_partial_received', 'shipment_received', 'shipment_cancelled'
```

Status updates via `DomainStatus::payload()` produce both fields atomically:
```php
// Example: Mark Shipped
[
    'status' => 'Shipped',           // legacy display value
    'status_code' => 'shipment_shipped',  // internal code
]
```

---

## 3. State Transition Rules

### 3.1 Transition Matrix

| From \ To | Draft | Shipped | Partial Received | Received | Cancelled |
|-----------|-------|---------|-----------------|----------|-----------|
| **Draft** | — | ✅ mark_shipped | ❌ | ❌ | ✅ cancelDraft |
| **Shipped** | ❌ | — | ✅ Receiving process (auto) | ✅ Receiving complete (auto) | ❌ |
| **Partial Received** | ❌ | — | — | ✅ Receiving complete (auto) | ❌ |
| **Received** | ❌ | ❌ | ❌ | — | ❌ |
| **Cancelled** | ❌ | ❌ | ❌ | ❌ | — |

### 3.2 Transition Rules Detail

#### Draft → Shipped (`markShipped`)

**Preconditions (ALL required):**

| # | Rule | Error Message |
|---|------|---------------|
| 1 | Status must be Draft | "Hanya shipment Draft yang bisa dikonfirmasi menjadi Shipped." |
| 2 | DN unique across non-cancelled shipments (same supplier) | "Delivery note {DN} sudah dipakai oleh shipment {other}." |
| 3 | Invoice unique if present (same supplier, non-cancelled) | "Invoice {INV} sudah dipakai oleh shipment {other}." |
| 4 | At least one line has a linked PO | "Shipment belum memiliki item yang bisa dikirim." |

**Side Effects:**
- Status updated to Shipped (with `status_code`)
- `updated_at` refreshed
- All linked PO statuses refreshed via `ErpFlow::refreshPoStatusByOutstanding()`
- Audit entry recorded

**Post-action:** Redirect to receiving process page with supplier/shipment context.

#### Draft → Cancelled (`cancelDraft`)

**Preconditions:**

| # | Rule | Error Message |
|---|------|---------------|
| 1 | Status must be Draft | "Hanya shipment Draft yang bisa dibatalkan." |

**Side Effects:**
- Status updated to Cancelled
- All linked PO statuses refreshed
- Audit entry recorded

**Post-action:** Redirect back to previous page.

#### Shipped → Partial Received (Automatic)

Triggered by goods receiving process when partial items are received:
- `ShipmentController` private method `refreshShipmentStatus` recalculates based on received vs shipped quantities
- Receiving creates/updates `goods_receipts` and `goods_receipt_items` records

#### Partial Received → Received (Automatic)

Triggered when all remaining items are received:
- Same `refreshShipmentStatus` method
- All `shipment_items.received_qty` = `shipped_qty`

---

## 4. Functional Requirements — Worklist

### FR-WL-001: Display Active Shipments

**ID:** FR-WL-001  
**Priority:** P0  
**Description:** The worklist view MUST display all shipments with status Draft, Shipped, or Partial Received.  
**Acceptance Criteria:**
- Shipments grouped by status priority: Draft first, then Shipped, then Partial Received
- Within each group, sorted by `shipment_date DESC`, then `id DESC`
- Results paginated at 20 per page
- Each row shows: Shipment number, date, supplier, PO numbers, DN, Invoice, Status badge, Progress (received/shipped/open), Sparkline visualization, Actions

### FR-WL-002: Filter Worklist

**ID:** FR-WL-002  
**Priority:** P0  
**Description:** Users MUST be able to filter shipments by supplier, delivery note, invoice, keyword, and status.  
**Acceptance Criteria:**
- Filter bar collapsible via toggle button
- Supplier filter: dropdown populated from suppliers table
- DN/Invoice/Keyword: text search across multiple fields (shipment number, DN, invoice, supplier name, PO number)
- Status filter: dropdown with all shipment statuses
- Active filters persist in query string (pagination, sorting)
- Reset button clears all filters

### FR-WL-003: Search Worklist

**ID:** FR-WL-003  
**Priority:** P0  
**Description:** Keyword search MUST filter rows in real-time (client-side).  
**Acceptance Criteria:**
- Typing in search box filters table rows without page reload
- Search matches: shipment number, supplier name, PO numbers, delivery note, invoice number, status
- Case-insensitive matching

### FR-WL-004: Batch Operations

**ID:** FR-WL-004  
**Priority:** P1  
**Description:** Users MUST be able to select multiple shipments and perform batch operations.  
**Acceptance Criteria:**
- Select-all checkbox in table header toggles all visible rows
- Batch toolbar appears when ≥1 row selected (sticky)
- Toolbar shows count of selected items
- Batch actions: Mark Shipped, Export, Cancel
- All batch operations disabled until selection exists

### FR-WL-005: Archive View

**ID:** FR-WL-005  
**Priority:** P1  
**Description:** The archive tab MUST display Received and Cancelled shipments.  
**Acceptance Criteria:**
- Filtered to status IN (Received, Cancelled)
- Shows summary chips: Completed count, Cancelled count
- Table same structure as worklist but without edit/ship/cancel actions

### FR-WL-006: Context-Sensitive Actions

**ID:** FR-WL-006  
**Priority:** P0  
**Description:** Action buttons MUST be specific to shipment status.  
**Acceptance Criteria (per status):**

| Status | Actions Available |
|--------|-------------------|
| Draft | Preview (→ preview page), Edit (→ edit page), Export (Excel), Import (modal), Mark Shipped (with confirm), Cancel (with confirm) |
| Shipped | Receive (→ receiving.process route) |
| Partial Received | Receive (→ receiving.process route), Export |
| Received (Archive) | View (→ detail) |
| Cancelled (Archive) | View (→ detail) |

---

## 5. Functional Requirements — Create Draft

### FR-CD-001: Select Items from Candidates

**ID:** FR-CD-001  
**Priority:** P0  
**Description:** Users MUST be able to select PO items to include in a shipment draft.  
**Acceptance Criteria:**
- Items displayed from candidate query: PO items with outstanding qty > 0, shippable PO status (issued/open/late), active items
- Each row shows: Supplier, PO, Item, PO price, Outstanding, Allocated, Available, ETD
- Only items with `available_to_ship_qty > 0` are selectable (others disabled/greyed)
- Checkbox selection; multi-select supported
- Selected items tracked in session across requests

### FR-CD-002: Split Shipment Visibility

**ID:** FR-CD-002  
**Priority:** P1  
**Description:** When items selected, MUST show allocation board per item across existing shipments.  
**Acceptance Criteria:**
- Per-item: outstanding qty, open in other shipments, current draft qty, remaining after draft
- Visual progress cards showing each existing shipment's progress on that item
- Draft current row highlighted as "Planned"
- Prevents over-allocation visibility

### FR-CD-003: Enter Document Information

**ID:** FR-CD-003  
**Priority:** P0  
**Description:** Users MUST enter document details for the shipment.  
**Acceptance Criteria:**
- Required: Delivery Note Number, Shipment Date (defaults to today)
- Optional: Invoice Number, Invoice Date, Currency (default: IDR), Supplier Remark, PO Reference Missing flag
- Supplier auto-populated from selected items (non-editable)
- All fields validated per defined rules

### FR-CD-004: Enter Line Quantities and Prices

**ID:** FR-CD-004  
**Priority:** P0  
**Description:** Users MUST set shipped quantities and optional invoice prices per line.  
**Acceptance Criteria:**
- Qty input: number field, minimum 0.01, maximum = available_to_ship_qty
- Price input: number field, minimum 0, optional
- Total per line auto-calculated: `qty × price` (real-time, on input/change)
- Format: IDR with 2 decimal places
- Remove button per line to exclude item from draft

### FR-CD-005: Multi-Step Wizard Validation

**ID:** FR-CD-005  
**Priority:** P0  
**Description:** Wizard MUST enforce step-by-step flow.  
**Acceptance Criteria:**
- Step 1 → Step 2: Only if ≥1 item selected (alert if none)
- Step 2 → Step 3: Always allowed (Back available)
- Step 3: Save button only enabled if items present
- Step indicators show: Active (green underline), Completed (dark green), Pending (grey)
- Wizard progress text updates per step

### FR-CD-006: Save Draft

**ID:** FR-CD-006  
**Priority:** P0  
**Description:** Submit MUST create shipment with status Draft and clear builder state.  
**Acceptance Criteria:**
- Transactional: all inserts succeed or none
- Generates unique shipment number via `ErpFlow::generateNumber('SHP', ...)`
- Sets status = Draft, status_code = shipment_draft
- Inserts shipment header + all shipment items
- Refreshes linked PO statuses
- Records audit entry (action: 'create')
- Clears session keys: `shipment_selected_items`, `shipment_shipped_qty`, `shipment_invoice_unit_price`
- Sets flash `shipment_builder_reset`
- Redirects to worklist with `focus` param highlighting new shipment
- On validation failure: returns errors without creating records

---

## 6. Functional Requirements — Edit Draft

### FR-ED-001: Restrict Editing to Draft Status

**ID:** FR-ED-001  
**Priority:** P0  
**Description:** Edit page MUST return 404 for non-Draft shipments.  
**Acceptance Criteria:**
- `edit()` method checks `$shipment->status === DocumentTermCodes::SHIPMENT_DRAFT`
- Non-Draft → `abort(404)`
- Prevents modification of confirmed/cancelled shipments

### FR-ED-002: Edit Header Information

**ID:** FR-ED-002  
**Priority:** P0  
**Description:** Users MUST be able to edit all header fields on the edit page.  
**Acceptance Criteria:**
- Editable fields: Delivery Note, Shipment Date, Invoice Number, Invoice Date, Currency, Supplier Remark
- Non-editable: Supplier (display only)
- All fields retain existing values as defaults

### FR-ED-003: Edit Line Items

**ID:** FR-ED-003  
**Priority:** P0  
**Description:** Users MUST be able to edit quantities and invoice prices on existing lines.  
**Acceptance Criteria:**
- Keep checkbox per line (soft delete: unchecking removes line on save)
- Qty input: editable, validated ≤ available_to_ship_qty
- Price input: editable, validated ≥ 0
- Auto-total recalculates on change
- Remove per-line button (equivalent to unchecking keep)

### FR-ED-004: Update Draft

**ID:** FR-ED-004  
**Priority:** P0  
**Description:** Save MUST update draft within transaction with full validation.  
**Acceptance Criteria:**
- Validates: DN uniqueness (excl. self), Invoice uniqueness (excl. self), qty > 0, qty ≤ available, price ≥ 0
- Updates header fields
- Filters to kept lines; updates qty/price for kept lines
- Deletes unkept lines
- Refreshes linked PO statuses
- Records audit entry (action: 'update')
- Redirects back with success message

### FR-ED-005: Preview Before Save (Edit Page)

**ID:** FR-ED-005  
**Priority:** P1  
**Description:** Edit page MUST provide Preview button to review changes before saving.  
**Acceptance Criteria:**
- Preview button in sticky action bar
- Recommended: Opens read-only review modal/page showing current draft values
- Non-editable display of all header and line data
- Action: Navigate to preview; Cancel to return; Edit link to edit
- Does NOT modify data

### FR-ED-006: Import Excel into Draft

**ID:** FR-ED-006  
**Priority:** P1  
**Description:** Users MUST be able to import an Excel file to update an existing draft.  
**Acceptance Criteria:**
- File upload: .xlsx or .xls only
- Must have HEADER and LINES sheets
- Header values validated against target draft (shipment number, supplier)
- Line items: validate shipment_item_id exists, qty > 0, qty ≤ available, price ≥ 0
- Transactional: all updates or none
- Removes unkept lines (matches import logic)
- Audit entry recorded

---

## 7. Functional Requirements — Mark Shipped

### FR-MS-001: Confirm Draft as Shipped

**ID:** FR-MS-001  
**Priority:** P0  
**Description:** Users with appropriate permissions MUST confirm Draft shipments as Shipped.  
**Acceptance Criteria:**
- Button visible only for Draft status shipments
- Transactional processing with row-level locking
- All preconditions validated (status, DN/invoice uniqueness, PO references)
- Status updated atomically to Shipped
- Linked PO statuses refreshed automatically
- Audit entry with before/after values
- Redirect to receiving process page
- Confirmation dialog recommended before execution

### FR-MS-002: Batch Mark Shipped

**ID:** FR-MS-002  
**Priority:** P1  
**Description:** Users MUST be able to mark multiple Draft shipments as shipped via batch toolbar.  
**Acceptance Criteria:**
- Available from batch toolbar when ≥1 Draft shipment selected
- Each shipment validated independently within shared transaction
- Success: all shipped; Failure: rolled back with error message
- Partial success NOT allowed (all-or-nothing)

---

## 8. Functional Requirements — Cancel Draft

### FR-CD-CANCEL-001: Cancel Draft

**ID:** FR-CD-CANCEL-001  
**Priority:** P0  
**Description:** Users MUST be able to cancel Draft shipments.  
**Acceptance Criteria:**
- Cancel button visible for Draft status
- Confirmation required ("Batalkan draft?")
- Validates status is Draft (error otherwise)
- Transactional: update status, refresh PO statuses, audit entry
- Redirect back with success/error message

---

## 9. Functional Requirements — Import/Export

### FR-IE-001: Export Draft to Excel

**ID:** FR-IE-001  
**Priority:** P1  
**Description:** Users MUST export Draft shipments to Excel.  
**Acceptance Criteria:**
- Restricted to Draft status (404 otherwise)
- Two-sheet output: HEADER (metadata), LINES (items)
- Auto-sized columns
- Auto-download with filename: `shipment-draft-{shipment_number}.xlsx`
- Temp file deleted after download

### FR-IE-002: Import Excel into Draft

**ID:** FR-IE-002  
**Priority:** P1  
**Description:** Users MUST import Excel files to update Draft shipments.  
**Acceptance Criteria:**
- Validates: shipment_id exists, Draft status, file type
- Validates header matches target draft
- Validates each line: shipment_item_id valid, qty valid, price non-negative
- Transactional update with error collection
- Redirects back with success or error message

### FR-IE-003: Bulk Import Drafts

**ID:** FR-IE-003  
**Priority:** P1  
**Description:** Users MUST bulk create shipments from Excel.  
**Acceptance Criteria:**
- Download template available (with sample data)
- Import groups rows by (supplier_code, shipment_date, DN, invoice)
- Each group → one shipment with auto-generated number
- All validations per row (presence, type, existence, availability, uniqueness)
- All errors collected before rollback (all-or-nothing)
- Error display: table showing row, column, message
- Success message: count of created shipments

### FR-IE-004: Bulk Import Template

**ID:** FR-IE-004  
**Priority:** P2  
**Description:** Template download MUST be available on Create Draft page and Worklist.  
**Acceptance Criteria:**
- Template includes column headers + 3 sample rows
- Columns match `ShipmentDraftBulkImport::COLUMNS`

---

## 11. Functional Requirements — View/Preview Draft

### FR-PV-001: Preview Draft from Worklist

**ID:** FR-PV-001  
**Priority:** P0  
**Description:** Users MUST be able to view a read-only preview of any Draft shipment from the Worklist.  
**Acceptance Criteria:**
- A **Preview** button (eye icon `<i class="fas fa-eye">`) with `title="Preview"` appears in the Actions column for every Draft shipment row
- Clicking Preview navigates to `/shipments/{id}/preview`
- Preview page displays in read-only mode — no input fields, no Edit buttons (except navigation to Edit)
- Section title displays the shipment number (e.g., `1823625913`)
- Section width fits content — not full page width
- Shipment number appears only in the section title (not duplicated as a page heading)
- No breadcrumb navigation on preview page
- Header info displayed as horizontal table (label | value | label | value columns)
- Preview shows: supplier, status, dates, DN, invoice, currency, remark, line items table (PO, item, qty, price, total), aggregated summary (total qty, received, open, grand total)
- Preview inherits the same data source as Edit page (`shipmentHeaderQuery` + `shipmentLineQuery`)
- Access returns 404 if shipment status is not Draft
- URL must be shareable/bookmarkable

### FR-PV-002: Preview Page Navigation

**ID:** FR-PV-002  
**Priority:** P1  
**Description:** Preview page MUST provide navigation back to worklist and to Edit page.  
**Acceptance Criteria:**
- Back button returns to `/shipments` (worklist)
- Edit button navigates to `/shipments/{id}/edit`
- Export button downloads draft Excel
- Cancel button (Draft only) cancels the draft with confirmation

---

## 10. Shipment Draft Preview — Functional Specs

### 10.1 Preview Purpose

The Shipment Draft Preview provides a read-only, comprehensive review of all shipment details before final submission (saving as Draft) or conversion (marking as Shipped). It prevents data entry errors by displaying computed values, aggregated totals, and allocation status clearly.

### 10.2 Preview Contexts

| Context | Route/Trigger | Scope |
|---------|--------------|-------|
| Step 2 — Review & Edit | `/shipments?tab=create` Step 2 | Items + documents before save |
| Step 3 — Confirm | `/shipments?tab=create` Step 3 | Final non-editable summary |
| Edit Page — Preview Button | `/shipments/{id}/edit` → Preview | Current draft review before update |
| Mark Shipped Confirm | Worklist → Mark Shipped | Data verified before status change |

### 10.3 Preview Data Requirements

The Preview MUST display ALL of the following information:

#### A. Header Information

| Field | Source | Editable in Preview |
|-------|--------|-------------------|
| Shipment Number | `shipments.shipment_number` | No (auto-generated) |
| Status | `shipments.status` + badge | No |
| Supplier Name + Code | `suppliers` join | No |
| Delivery Note Number | `shipments.delivery_note_number` | No (confirm in Step 3) |
| Shipment Date | `shipments.shipment_date` | No (confirm in Step 3) |
| Invoice Number | `shipments.invoice_number` | No (confirm in Step 3) |
| Invoice Date | `shipments.invoice_date` | No |
| Currency | `shipments.invoice_currency` | No |
| Supplier Remark | `shipments.supplier_remark` | No |

#### B. Line Items

| Column | Calculation | Notes |
|--------|-------------|-------|
| PO Number | `purchase_orders.po_number` | Links to PO detail |
| Item Code | `items.item_code` | — |
| Item Name | `items.item_name` | — |
| Shipped Qty | `shipment_items.shipped_qty` | User-entered |
| Received Qty | `shipment_items.received_qty` | From receiving (0 for Draft) |
| Open Qty | `shipped_qty - received_qty` | Computed |
| Invoice Unit Price | `shipment_items.invoice_unit_price` | User-entered (nullable) |
| Invoice Line Total | `shipped_qty × invoice_unit_price` | Computed |
| PO Outstanding | `purchase_order_items.outstanding_qty` | Informational |
| Available to Ship | Computed | Informational |

#### C. Aggregated Totals

| Metric | Calculation |
|--------|-------------|
| Total Lines | `COUNT(shipment_items)` |
| Total Shipped Qty | `SUM(shipped_qty)` |
| Total Open Qty | `Σ(shipped_qty - received_qty)` |
| Total Invoice Amount | `Σ(invoice_line_total)` |
| PO Count | `COUNT(DISTINCT po.po_number)` |
| Average Line Qty | `SUM(shipped_qty) / COUNT(lines)` |

#### D. Allocation Status (Create Context)

| Information | Purpose |
|-------------|---------|
| Split shipment board | Shows per-item allocation across existing shipments |
| Remaining after draft | Alerts user if allocation would exhaust PO outstanding |
| Progress visualization | Shows existing shipment progress per item |

### 10.4 Draft Preview Interaction Rules

| Rule | ID | Description |
|------|-----|-------------|
| PRV-001 | Read-only | Preview displays data only; no edits from preview surface |
| PRV-002 | Navigation | Edit link/button returns user to editable form |
| PRV-003 | Confirmation | "Mark Shipped" action requires separate confirmation from preview |
| PRV-004 | Completeness | All fields from the shipment document MUST be visible |
| PRV-005 | Real-time totals | In Step 2 (Review & Edit), totals update as user changes quantities/prices |
| PRV-006 | Status awareness | Preview layout adjusts based on shipment status (expanded/collapsed sections) |
| PRV-007 | Empty handling | Empty tables show graceful empty state with icon and message |
| PRV-008 | Print | Preview should support Print (Ctrl+P) for physical record |

### 10.5 Draft Preview for Each Shipment Status

| Status | Preview Behavior | Special Elements |
|--------|-----------------|-----------------|
| **Draft** | Full editable from Step 2; read-only from Step 3 and Preview modal | "Simpan Draft" CTA; "Mark Shipped" CTA after save; allocation board visible |
| **Shipped** | Read-only; frozen data | "Receive" CTA; receiving progress; timeline expanded |
| **Partial Received** | Read-only; progress visible | "Continue Receiving" CTA; progress bar; receiving gap highlighted |
| **Received** | Read-only; archive view | "View Audit" CTA; all quantities finalized |
| **Cancelled** | Read-only; cancellation visible | "View Audit" CTA; cancellation reason if recorded |

### 10.6 Error Display in Preview Context

When the backend error occurs (e.g., undefined variable from controller data gap), the Preview MUST:

| Scenario | Display |
|----------|---------|
| Shipment not found | Error page with retry and navigation options |
| Missing computed data | Graceful degradation: show "-" or "Data unavailable" instead of crashing |
| Validation errors during save | Return to form with inline error messages |
| Permission denied | Redirect to login or unauthorized page |

---

## 11. Audit & Traceability

### 11.1 Audit Events

| Event | Action Key | Data Captured |
|-------|-----------|---------------|
| Draft created | `'create'` | Shipment data |
| Draft updated | `'update'` | Shipment data changes |
| Draft cancelled | `'cancel'` | Before: full shipment; After: status = Cancelled |
| Marked shipped | `'mark_shipped'` | Before: status = Draft; After: status = Shipped |
| Excel imported | `'import_excel'` | Before: shipment; After: {header, lines} |
| Bulk imported | `'import_bulk_excel'` | Inserted shipment records |

### 11.2 Audit Data Model

| Column | Type | Description |
|--------|------|-------------|
| `module` | `string` | `'shipments'` |
| `record_id` | `bigInteger` | Shipment ID |
| `action` | `string` | Action key (see table above) |
| `old_value` | `json/nullable` | Before state |
| `new_value` | `json/nullable` | After state |
| `user_id` | `foreignId` | Who performed the action |
| `ip_address` | `string` | Client IP |
| `created_at` | `timestamp` | When it happened |

### 11.3 Traceability Chain

```
Purchase Order → Purchase Order Items → Shipment → Shipment Items → Goods Receipt → Goods Receipt Items
```

All links are via foreign keys. The `shipmentAllocationBoardQuery` enables cross-shipment visibility for allocation tracking.

---

## 12. Non-Functional Requirements

### 12.1 Performance

| Requirement | Target |
|-------------|--------|
| Page load (worklist) | < 2 seconds with 1000+ records |
| Search/filter response | < 500ms client-side |
| Draft creation | < 1 second (transaction time) |
| Excel export | < 3 seconds for 100 lines |
| Excel import (100 rows) | < 5 seconds (processing + validation) |

### 12.2 Reliability

| Requirement | Implementation |
|-------------|---------------|
| Transaction safety | All write operations wrapped in `DB::transaction()` |
| Row-level locking | `lockForUpdate()` for status transitions and duplicate checks |
| All-or-nothing | Validation errors collected before execution; single rollback on any failure |
| Session recovery | Builder state persists across page refreshes and navigation |

### 12.3 Scalability

| Consideration | Approach |
|---------------|----------|
| Large shipments | Sticky table headers, horizontal scroll |
| Many shipments | Pagination (20/page), indexed queries |
| Concurrent editing | Optimistic locking via `lockForUpdate` on critical operations |
| Excel imports | PhpSpreadsheet with memory management |

### 12.4 Compatibility

| Requirement | Specification |
|-------------|--------------|
| Browser | Modern evergreen browsers (Chrome, Firefox, Edge, Safari) |
| Backend | Laravel 10+, PHP 8.2+ |
| Database | MySQL (primary), SQLite (supported via driver detection) |
| Excel format | .xlsx, .xls (PhpSpreadsheet) |

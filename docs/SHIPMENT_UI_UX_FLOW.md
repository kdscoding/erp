# Shipment Module — UI/UX Flow Specification

**Endpoint:** `http://127.0.0.1:8000/shipments`  
**Focus:** Shipment Draft Preview Feature  
**Date:** 2026-09-20  
**Status:** Design Specification

---

## Table of Contents

1. [Overview](#1-overview)
2. [User Roles & Entry Points](#2-user-roles--entry-points)
3. [Core User Flows](#3-core-user-flows)
4. [Shipment Draft Preview — Deep Dive](#4-shipment-draft-preview--deep-dive)
5. [Page Specifications](#5-page-specifications)
6. [Modal Specifications](#6-modal-specifications)
7. [Interaction Design](#7-interaction-design)
8. [Error Prevention & Validation UX](#8-error-prevention--validation-ux)
9. [Responsive Behavior](#9-responsive-behavior)
10. [Accessibility](#10-accessibility)

---

## 1. Overview

The Shipment module at `/shipments` provides a tab-based interface for managing shipment documents throughout their lifecycle. The **Shipment Draft Preview** is a critical feature that allows users to review all granular details of a shipment before final submission or conversion from Draft to Shipped status.

### 1.1 Design Principles

| Principle | Application |
|-----------|-------------|
| **Progressive Disclosure** | Hide advanced details behind expandable sections; show summary first |
| **Confirmation Before Commitment** | Multi-step wizard with review step before saving; confirmation modal before marking shipped |
| **Error Prevention** | Inline validation, quantity limits shown, real-time totals calculation |
| **Context Preservation** | Session-based builder state survives page navigations |
| **Status Clarity** | Color-coded badges + text labels, never color-only indicators |

### 1.2 Design System

The UI follows the **LEMON** design language defined in `layouts/erp.blade.php`:

| Token | Value | Usage |
|-------|-------|-------|
| `--lemon-yellow` | `#f1d93b` | Accent, alerts |
| `--lemon-green` | `#9ecb3c` | Primary actions, progress fill |
| `--lemon-green-deep` | `#6f9628` | Links, active states |
| `--lemon-ink` | `#304218` | Primary text |
| `--lemon-olive` | `#566d2a` | Secondary text |
| `--lemon-bg` | `#f7f8ea` | Background |
| `--lemon-line` | `#dfe6b8` | Borders |

---

## 2. User Roles & Entry Points

### 2.1 Roles

| Role | Permissions |
|------|------------|
| Admin | Full CRUD, mark shipped, cancel, import/export |
| Purchasing | Create drafts, edit, import/export, mark shipped |
| Purchasing Manager | Review, approve mark-shipped, cancel |
| Warehouse | View, receive shipments |
| BC/Compliance | View, audit |
| Viewer | Read-only access |

### 2.2 Entry Points

| Entry Point | Route | User Action |
|-------------|-------|-------------|
| Dashboard → Shipments Worklist | `/shipments` (default) | Monitor active shipments |
| Dashboard → Quick Create | Via shortcut or menu | Start new shipment |
| PO Detail → Create Shipment | Cross-module link | Convert PO items to shipment |
| Worklist → Edit Draft | Click Edit action on Draft | Revise existing draft |

---

## 3. Core User Flows

### 3.1 Flow A: Create Shipment Draft (Multi-Step Wizard)

```
┌─────────────────────────────────────────────────────────────────┐
│  STEP 1: Select Items                                           │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ Filter by Supplier / Search by Item/PO/Supplier             ││
│  │ [Table of candidate PO items with available quantities]     ││
│  │ ☐ PO-001 / ITM001 / Sisa Kirim: 500  ...                  ││
│  │ ☐ PO-002 / ITM003 / Sisa Kirim: 300  ...                  ││
│  │                                                             ││
│  │ [Tambahkan ke Draft →]   [Clear Check]                     ││
│  └─────────────────────────────────────────────────────────────┘│
│                        │                                        │
│                        ▼                                        │
│  STEP 2: Review & Edit (DRAFT PREVIEW LAYER 1)                  │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ Split Shipment Board (allocation per item)                  ││
│  │ Header: Supplier, DN, Date, Invoice, Currency, Remark       ││
│  │ Line Items: PO, Item, Qty Draft, Harga Inv, Total          ││
│  │ [Real-time totals calculation]                              ││
│  │                                                             ││
│  │ [Next →]  [Back]                                           ││
│  └─────────────────────────────────────────────────────────────┘│
│                        │                                        │
│                        ▼                                        │
│  STEP 3: Confirm & Save (DRAFT PREVIEW LAYER 2)                 │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ Summary Chips: Lines | Total Qty | Total Invoice | POs     ││
│  │ Full preview table with all line items                      ││
│  │ [Simpan Draft Shipment]  [← Back]                          ││
│  └─────────────────────────────────────────────────────────────┘│
│                        │                                        │
│                   [Submit]                                       │
│                        ▼                                        │
│  ✓ Draft Created — Redirect to Worklist with focus             │
└─────────────────────────────────────────────────────────────────┘
```

### 3.2 Flow B: Edit Existing Draft

```
┌─────────────────────────────────────────────────────────────────┐
│  Shipment Edit Page (Draft only — 404 otherwise)                │
│                                                                 │
│  SHP-00042  [Draft]                                             │
│  Supplier: IndoFood · Kode: SUP001                              │
│                                                                 │
│  ┌─ Header Information ─────────────────────────────────┐       │
│  │ DN, Tanggal, Invoice, Currency, Catatan              │       │
│  └──────────────────────────────────────────────────────┘       │
│                                                                 │
│  ┌─ Allocation Overview ──────────────────────────────┐         │
│  │ Per-item: Outstanding | Open Lain | Draft | Max    │         │
│  └──────────────────────────────────────────────────────┘         │
│                                                                 │
│  ┌─ Line Items (Editable) ────────────────────────────┐         │
│  │ Keep | PO | Item | Qty | Harga Inv | Total | Max  │         │
│  │ [Real-time totals recalculation]                    │         │
│  │ [Remove line]                                       │         │
│  └──────────────────────────────────────────────────────┘         │
│                                                                 │
│  [Preview]  [Simpan]  [Cancel]  [Back]                          │
└─────────────────────────────────────────────────────────────────┘
```

### 3.3 Flow C: Mark Shipped (Draft → Shipped)

```
┌─────────────────────────────────────────────────────────────────┐
│  Pre-Check (within DB transaction):                             │
│  1. Status must be Draft                                        │
│  2. DN not used by other active shipments (same supplier)       │
│  3. Invoice not used by other active shipments (same supplier)  │
│  4. At least one line has a PO reference                        │
│                                                                 │
│  ┌─ Confirmation ──────────────────────────────────────┐        │
│  │ "Mark this shipment as shipped?"                    │        │
│  │                                                     │        │
│  │ Shipment:  SHP-00042                                │        │
│  │ Supplier:  IndoFood Supplies                        │        │
│  │ DN:        DN-4421                                  │        │
│  │ Lines:     5 items, Total Qty: 1,250               │        │
│  │ POs:       PO-0089, PO-0091, PO-0095 (3 POs)      │        │
│  │                                                     │        │
│  │ [Cancel]  [Confirm & Ship]                          │        │
│  └──────────────────────────────────────────────────────┘        │
│                        │                                        │
│                   [Confirmed]                                    │
│                        ▼                                        │
│  ✓ Redirected to Receiving Process page                         │
│  ✓ PO statuses refreshed automatically                          │
│  ✓ Audit trail entry recorded                                   │
└─────────────────────────────────────────────────────────────────┘
```

### 3.4 Flow D: Worklist Monitoring

```
┌─────────────────────────────────────────────────────────────────┐
│  Worklist Tab (Default)                                         │
│  [Worklist]  [Create Draft]  [Archive]                          │
│                                                                 │
│  Filter: [Supplier▼] [DN] [Invoice] [Keyword] [Status▼] [🔍]   │
│                                                                 │
│  Table: Shipment | Supplier | PO | DN | Invoice | Status |     │
│         Progress | Actions                                      │
│                                                                 │
│  SHP-00042  IndoFood  PO-0089  DN-4421  INV-8821  [Draft]     │
│             420/1000  Open 580  [Edit] [Ship] [Cancel]          │
│                                                                 │
│  SHP-00043  IndoFood  PO-0091  DN-4422  INV-8822  [Shipped]   │
│             750/1000  Open 250  [Receive] [Export]              │
│                                                                 │
│  SHP-00044  SariFood  PO-0095  DN-4430  -         [Partial]   │
│             300/500   Open 200  [Receive] [Export]              │
│                                                                 │
│  ✓ 5 items per page  Pagination: Prev 1 2 3 Next              │
└─────────────────────────────────────────────────────────────────┘
```

---

## 4. Shipment Draft Preview — Deep Dive

The Shipment Draft Preview is the central feature for reviewing shipment details before final submission or conversion. It exists in three layers:

### 4.1 Preview Layer 1: Step 2 of Create Wizard (In-Flow Review)

**Location:** `/shipments/create` (Create Draft tab) → Step 2 "Review & Edit"

**Purpose:** Allow users to verify item allocation and document information before saving.

**Components:**

#### A. Split Shipment Board

A table showing allocation impact per item across existing shipments:

| Column | Data | Visual |
|--------|------|--------|
| Item | Item code + name + PO | `doc-number` / `doc-meta` |
| PO Outstanding | Total outstanding from PO | Numeric |
| Dialokasikan di Shipment Lain | Qty already allocated to other shipments | Numeric |
| Draft Saat Ini | Qty in current draft | Numeric, highlighted |
| Sisa Setelah Draft | `outstanding - open_other - current_draft` | Numeric, green if > 0 |
| Milestone | Existing shipment progress per item | Progress cards with status badges |

**Key UX Feature:** Each row includes a **progress visualization** showing how the item is distributed across existing shipments vs. the current draft. This prevents over-allocation.

#### B. Document Information Card

A form card with document fields:

| Field | Editable | Default | Validation |
|-------|----------|---------|------------|
| Supplier | No (disabled) | From selected items | — |
| No Delivery Note | Yes | Empty | Required |
| Tanggal | Yes | Today | Required, valid date |
| No Invoice | Yes | Empty | Optional |
| Tgl Invoice | Yes | Empty | Optional, valid date |
| Currency | Yes | 'IDR' | Max 10 chars |
| Catatan | Yes | Empty | Optional, max 500 |
| PO Reference Missing | Yes (checkbox) | Unchecked | Optional |

#### C. Line Items Table

Editable table with real-time calculation:

| Column | Content | Behavior |
|--------|---------|----------|
| PO | PO number | Static |
| Item | Code + name | Static |
| Harga PO | Unit price from PO | Static |
| Sisa Bisa Dikirim | Available quantity | Static (informational) |
| Qty Draft | Input (number, step 0.01) | Editable; max = available; real-time total calc |
| Harga Invoice | Input (number, step 0.0001) | Editable; min 0; real-time total calc |
| Total Invoice | Computed | Read-only; auto-recalculates on qty/price change |
| Aksi | Remove button | Removes item from draft |

**Real-Time Calculation:**
```javascript
// On any qty or price input change:
// line_total = qty × price (formatted as IDR)
// All totals across all lines are recalculated simultaneously
```

### 4.2 Preview Layer 2: Step 3 of Create Wizard (Final Confirmation)

**Location:** `/shipments/create` (Create Draft tab) → Step 3 "Confirm & Save"

**Purpose:** Provide a consolidated, non-editable summary for final verification.

**Components:**

#### A. Summary Chips (KPI)

| Chip | Value | Source |
|------|-------|--------|
| Line Items | Count of selected items | `selected_items.count()` |
| Total Qty | Sum of all drafted quantities | `Σ shipped_qty` |
| Total Invoice | Sum of all line totals | `Σ invoice_line_total` |
| PO References | Distinct PO count | `Π po_number.unique()` |
| Supplier | Name from selected items | Static |
| Delivery Note | From form input | Form value |

Visual: Centered grid with large value (1.2rem/800 weight) and small uppercase label (0.72rem). Green bordered box (`2px solid --lemon-green`) with gradient background.

#### B. Final Preview Table

Non-editable table mirroring Step 2 data but with cleaner presentation:

| Column | Content |
|--------|---------|
| PO | PO number |
| Item | Item code + name |
| Qty Draft | Final drafted quantity |
| Harga Invoice | Final invoice unit price |
| Total Invoice | `qty × price` |

### 4.3 Preview Layer 3: Edit Page Preview Button

**Location:** `/shipments/{id}/edit` → Sticky action bar

**Behavior:** Clicking **Preview** (currently present at `edit.blade.php:132`) triggers a JavaScript confirmation dialog ("Simpan?") that submits the form. In the recommended implementation, this should open a non-editable preview modal/page showing all current values.

**Recommended Enhancement:**

```
┌─ Shipment Draft Preview ──────────────────────────────┐
│  SHP-00042  [Draft Badge]                              │
│                                                       │
│  ┌─ Header ───────────────────────────────────────┐   │
│  │ Supplier:  IndoFood Supplies (SUP001)           │   │
│  │ DN:        DN-4421                              │   │
│  │ Date:      15-09-2026                           │   │
│  │ Invoice:   INV-8821 (2026-09-10, IDR)          │   │
│  │ Remark:   Standard delivery                     │   │
│  └────────────────────────────────────────────────┘   │
│                                                       │
│  ┌─ Lines (5 items, Total: Rp 75,000,000) ──────┐   │
│  │ PO      | Item    | Qty  | Price   | Total    │   │
│  │ PO-0089 | ITM001  | 300  | 50,000  | 15,000,000│  │
│  │ PO-0089 | ITM002  | 200  | 80,000  | 16,000,000│  │
│  │ PO-0091 | ITM003  | 300  | 120,000 | 36,000,000│  │
│  │ PO-0095 | ITM004  | 150  | 40,000  | 6,000,000 │  │
│  │ PO-0095 | ITM005  | 100  | 25,000  | 2,500,000 │  │
│  │ ──────────────────────────────────────        │   │
│  │ TOTALS                        | 500 | 75jt   │   │
│  └────────────────────────────────────────────────┘   │
│                                                       │
│  [✕ Close]  [🔗 Go to Edit]                          │
└───────────────────────────────────────────────────────┘
```

### 4.4 Preview State Behavior by Shipment Status

| Status | Preview Behavior |
|--------|-----------------|
| **Draft** | Full preview — all fields editable from preview, "Mark Shipped" CTA visible |
| **Shipped** | Read-only preview — confirm shipment data frozen; "Receive" CTA visible |
| **Partial Received** | Read-only preview with receiving progress; "Continue Receiving" CTA |
| **Received** | Read-only archive view; "View Audit" CTA |
| **Cancelled** | Read-only with cancellation reason; "View Audit" CTA |

---

## 5. Page Specifications

### 5.1 Worklist Page (`/shipments`)

#### 5.1.1 Action Buttons (Draft Rows)

Each Draft row in the worklist table has the following action buttons in order (left to right):

| Button | Icon | Route | Behavior |
|--------|------|-------|----------|
| **Preview** | `<i class="fas fa-eye">` | `shipments.preview` | Opens read-only detail page in new tab. No confirmation needed. |
| **Edit** | `<i class="fas fa-edit">` | `shipments.edit` | Opens edit form for modifying draft details. |
| **Export** | `<i class="fas fa-download">` | `shipments.export-excel` | Downloads draft as Excel file. |
| **Import** | `<i class="fas fa-upload">` | Modal | Opens import Excel modal for the draft. |
| **Ship** | `<i class="fas fa-truck">` | `shipments.mark-shipped` | POST form, confirmation required. |
| **Cancel** | `<i class="fas fa-times">` | `shipments.cancel-draft` | POST form, confirmation required. |

#### Navigation Tabs

| Tab | Route | Badge Content | Active Style |
|-----|-------|--------------|--------------|
| Worklist | `/shipments?tab=worklist` | Active shipment count | Green underline |
| Create Draft | `/shipments?tab=create` | — | Green underline |
| Archive | `/shipments?tab=history` | Archived count | Green underline |

#### Worklist Filters (Collapsible)

| Filter | Type | Placeholder |
|--------|------|-------------|
| Supplier | Select | Semua Supplier |
| Delivery Note | Text | No surat jalan |
| Invoice | Text | No invoice |
| Keyword | Text | Shipment / PO / supplier |
| Status | Select (dynamic) | Semua Status |

#### Table Columns

| Column | Width | Content | Sortable |
|--------|-------|---------|----------|
| Select | 36px | Checkbox for batch | No |
| Shipment | Auto | Number + date | Via search |
| Supplier | Auto | Name | Via search |
| PO | Auto | Comma-separated numbers + count | Via search |
| Delivery Note | Auto | DN number | Via search |
| Invoice | Auto | Invoice number | Via search |
| Status | Auto | Badge | Via filter |
| Progress | Auto | Received/Shipped + Open + sparkline | No |
| Actions | Auto | Context-specific buttons | No |

#### Action Buttons (Context-Sensitive)

| Status | Actions |
|--------|---------|
| **Draft** | Edit, Export, Import (modal), Mark Shipped, Cancel |
| **Shipped** | Receive (→ receiving process) |
| **Partial** | Receive (→ receiving process), Export |
| **Received** | (in Archive) View |
| **Cancelled** | (in Archive) View |

#### Batch Toolbar (Appears on Selection)

Position: Sticky at top of results when checkboxes selected.

```
[n items selected] | [Mark Shipped] [Export] [Cancel] | [Clear]
```

Disabled state until ≥1 row selected.

### 5.2 Create Draft Wizard (`/shipments?tab=create`)

#### Step 1: Select Items

- Supplier filter (disabled once items selected)
- Keyword search (item code, name, PO, supplier)
- Candidate table with availability indicators
- Allocation board for selected items (split shipment view)
- "Tambahkan ke Draft →" / "Clear Check" actions

#### Step 2: Review & Edit

- Split shipment board (allocation visibility)
- Document information form (editable)
- Line items table (editable quantities/prices, auto-calculated totals)
- Navigation: Back / Next →

#### Step 3: Confirm & Save

- Summary chips (KPI overview)
- Final preview table (non-editable)
- Navigation: ← Back / Simpan Draft Shipment
- Sticky action bar with save button

### 5.3 Edit Draft Page (`/shipments/{id}/edit`)

- Header: Shipment number (h2), subtitle with supplier info
- Info boxes: Shipment number, Supplier (name + code), Status
- Header information form (DN, dates, invoice, currency, remark)
- Allocation overview table (outstanding, open other, current draft, max)
- Editable line items table (keep checkbox, qty, price, auto-total)
- Sticky action bar: [Preview] [Simpan] [Back]

### 5.4 Archive Page (`/shipments?tab=history`)

- Summary chips: Completed count, Cancelled count
- Table: Same as worklist but filtered to Received/Cancelled
- No batch actions (archival only)

### 5.5 Preview Draft Page (`/shipments/{id}/preview`)

- **Access:** Only accessible for Draft status shipments; returns 404 otherwise
- **Page Head:** Action buttons only (Refresh, Export, Edit, Cancel, Back) — no title
- **Section Title:** Shipment number (e.g., `1823625913`) — section width fits content
- **Informasi Header section** (horizontal table):
  - Row 1: Tanggal Shipment | dd-mm-yyyy | No Delivery Note | DN-xxx
  - Row 2 (conditional): No Invoice | INV-xxx | Tgl Invoice | dd-mm-yyyy
  - Row 3 (conditional): Currency | IDR
  - Row 4 (conditional): Catatan | remark (span 3 columns)
- **Line items section:** Read-only table — No, PO, Item, Nama Item, Harga PO, Qty Kirim, Harga Inv, Total, Maks; footer row shows totals
- **Ringkasan (Summary) section:** Total Qty Kirim, Total Received, Open, Grand Total in summary boxes
- **Action buttons:** Refresh, Export, Edit, Cancel (Draft only), Back to Worklist
- No form inputs, no editable fields — purely display
- No breadcrumb navigation
- URL is bookmarkable/shareable

---

## 6. Modal Specifications

### 6.1 Import Draft Excel Modal

**Trigger:** Upload icon on Draft row in worklist  
**Content:**

```
┌─ Import Excel SHP-00042 ─────────────────┐
│                                          │
│  Upload file hasil export setelah diedit.│
│                                          │
│  File Excel: [________________] [Browse] │
│                                          │
│  [Tutup]  [Import]                       │
└──────────────────────────────────────────┘
```

**Validation on import:**
- File must be .xlsx or .xls
- Sheet HEADER and LINES required
- Header must match target draft (shipment number, supplier)
- DN/invoice uniqueness enforced
- Qty and price validation per line

### 6.2 Bulk Import Error Display

When bulk import has errors, displayed as a table below the filter section:

| Baris | Kolom | Kesalahan |
|-------|-------|-----------|
| 3 | qty_pengiriman | Qty melebihi qty tersedia |
| 7 | invoice_number | Invoice sudah dipakai |

### 6.3 Confirmation Modal (Recommended for Mark Shipped)

**Trigger:** Mark Shipped button on Draft row  
**Content:** Summary of shipment data with confirmation prompt

This modal should be implemented to prevent accidental confirmation of shipment.

---

## 7. Interaction Design

### 7.1 Button Hierarchy

| Level | Style | Usage |
|-------|-------|-------|
| **Primary** | Solid `--lemon-green` fill | Save, Mark Shipped, Import |
| **Secondary** | `--lemon-green-deep` outline | Edit, Export, Preview |
| **Danger** | `--lemon-line` outline + red text | Cancel, Remove |
| **Ghost** | Transparent + muted | Back, Clear, Reset |

### 7.2 State Transitions & Visual Feedback

| Action | Feedback |
|--------|----------|
| Hover on table row | Background tint `rgba(48,66,24,.03)` |
| Hover on related card | `translateY(-1px)` + shadow lift |
| Progress bar fill | Animated 150ms ease-out on page load |
| Wizard step change | Fade animation (250ms) |
| Action success | Flash message (green for success, red for error) |
| Quantity change | Real-time total recalculation |
| Filter toggle | Smooth section appear/disappear |

### 7.3 Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl+N` | Navigate to new draft creation |
| `Ctrl+F` | Focus search input |
| `Escape` | Close modals/dropdowns |
| `Ctrl+P` | Print current view |

### 7.4 Real-Time Feedback Patterns

#### Quantity Validation (Inline)

When user enters quantity in line items:

| Condition | Visual Feedback |
|-----------|----------------|
| qty ≤ available | Normal input border |
| qty > available | Input max attribute prevents; if bypassed, server rejects with error |
| qty ≤ 0 | Client-side `min="0.01"` prevents entry |

#### Auto-Calculation

Line total recalculates on every `input` and `change` event:

```javascript
// Triggered on .draft-qty-input and .draft-price-input
// Updates corresponding .draft-line-total field
// Format: IDR with 2 decimal places
```

---

## 8. Error Prevention & Validation UX

### 8.1 Prevention Strategies

| Risk | Prevention Mechanism |
|------|---------------------|
| Over-allocation | Available-to-ship shown per line; `max` attribute on qty input; split board |
| Duplicate DN | Server-side uniqueness check; client-side error display |
| Empty shipment | `selected_items` required (min:1); disabled save button when no items |
| Invalid quantities | `min="0.01"` on inputs; server validation with clear error messages |
| Wrong status transitions | Server enforces status checks (404 for non-Draft edit, ValidationException for non-Draft ship) |
| Accidental ship | Confirmation dialog for mark-shipped; batch disable until selection |

### 8.2 Validation UX Patterns

| Pattern | Implementation |
|---------|---------------|
| Inline errors | Laravel error bag displayed under fields in red text |
| Summary errors | For bulk import: error table with row/column/message |
| Toast/Flash | Success (green) and error (red) alert boxes at top of content |
| Disabled states | Save button disabled until items selected; batch buttons disabled until rows checked |
| Confirmation | `confirm()` dialog for destructive actions (cancel, mark shipped, delete line) |

### 8.3 Empty States

| Context | Empty State Message |
|---------|---------------------|
| No items in candidate search | "Belum ada kandidat. Coba ubah filter atau kata kunci pencarian." |
| No items selected (Step 2) | "Pilih minimal satu item dari tabel kandidat di langkah 1." |
| No items in table | "Tidak ada dokumen aktif." / "Belum ada arsip." |
| No items in shipment | "No items in this shipment yet" (with add CTA for Draft) |

---

## 9. Responsive Behavior

### 9.1 Breakpoints

| Breakpoint | Layout Changes |
|------------|---------------|
| Desktop (≥1024px) | Full layout, 3-column info grid, inline actions, full table |
| Tablet (768–1023px) | 2-column info grid, stacked header, horizontal scroll tables |
| Mobile (<768px) | Single column, full-width cards, horizontal scroll tables, action buttons may collapse to icons |

### 9.2 Mobile-Specific Patterns

- Table wraps in `table-responsive` container with horizontal scroll
- Action buttons display as icon-only at narrow widths
- Wizard tabs scroll horizontally with `overflow-x: auto`
- Filter bar wraps to stacked rows
- Batch toolbar collapses to icon row

---

## 10. Accessibility

### 10.1 WCAG 2.1 AA Compliance Targets

| Requirement | Implementation |
|-------------|----------------|
| Keyboard navigation | All buttons focusable; form inputs keyboard-operable |
| Screen reader | `aria-label` on status badges; `aria-current="page"` on breadcrumb; `aria-live="polite"` for dynamic updates |
| Color independence | Status always includes text label + icon; never color-only |
| Focus indicators | 2px outline on `:focus` for all interactive elements |
| Table semantics | `scope="col"` on `<th>`; `<caption>` on tables |
| Form labels | All inputs have associated `<label>` elements |
| Error identification | `aria-invalid="true"` on invalid inputs; `aria-describedby` linked to error message |
| Language | `lang="id"` on Indonesian content; `lang="en"` on English labels |
| Skip link | "Skip to main content" at page top |

### 10.2 Contrast Ratios

| Element | Foreground | Background | Ratio | Target |
|---------|-----------|------------|-------|--------|
| Body text | `--lemon-ink` (#304218) | White | 14.5:1 | ✅ >4.5:1 |
| Secondary text | `--lemon-olive` (#566d2a) | White | 7.2:1 | ✅ >4.5:1 |
| Table header | #5f7331 | White | 6.8:1 | ✅ >4.5:1 |
| Status badge text | Various | Various | Per-badge verification needed |
| Error text | #e53e3e | White | 5.1:1 | ✅ >4.5:1 |

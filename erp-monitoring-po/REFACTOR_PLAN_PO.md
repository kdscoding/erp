# PO Module — UX Architecture & Refactoring Specification
## Modernizing Purchase Order Data Entry and Tracking Workflows

---

## Executive Summary

The current PO module (`po.index`, `po.create`, `po.show`) presents three distinct workflows — **PO creation**, **PO discovery/filtering**, and **PO monitoring/tracking** — but they are implemented as loosely coupled Blade templates with heavy inline JavaScript, redundant CDN script loads, and inconsistent UI patterns.

This specification defines a phased refactoring plan that streamlines both core workflows through:

1. **Reduced cognitive load** — consolidate fragmented UI elements (per-item modals, duplicate chips, verbose status text) into unified, progressive-disclosure components.
2. **Performance optimization** — eliminate O(N×3) modal rendering, deduplicate CDN script loads, and replace fragile DOM-walking JavaScript with server-side JSON endpoints.
3. **Workflow acceleration** — add keyboard shortcuts, inline search, clickable navigation, and persistent quick actions so users spend less time hunting for controls.
4. **Modern data visualization** — replace status-badge-only views with progress indicators, ETA-aware row highlighting, and timeline visualizations for tracking.

---

## 1. Current State Analysis

### 1.1 Architecture Overview

| Component | File | Lines | Role |
|-----------|------|-------|------|
| Controller | `app/Http/Controllers/PurchaseOrderController.php` | 402 | Orchestrates CRUD, schedule updates, cancellation, and Excel export |
| Index View | `resources/views/po/index.blade.php` | 143 | PO listing with filter form and summary chips |
| Create View | `resources/views/po/create.blade.php` | 562 | Manual PO creation form with 260 lines of inline JS |
| Show View | `resources/views/po/show.blade.php` | 662 | PO detail, item monitoring, tracking modals |
| Detail Query | `app/Queries/PurchaseOrders/PurchaseOrderDetailQuery.php` | 187 | 6-table join producing item summaries and tracking trees |
| Index Query | `app/Queries/PurchaseOrders/PurchaseOrderIndexQuery.php` | 38 | Filterable PO listing query builder |
| Status Codes | `app/Support/DocumentTermCodes.php` | 87 | Legacy display-oriented status constants |
| Domain Status | `app/Support/DomainStatus.php` | 130 | Internal/legacy status mapping layer |
| Status Helper | `app/Support/DocumentTermStatus.php` | 108 | Badge class resolution and label lookup |
| Status Badge | `resources/views/components/status-badge.blade.php` | 28 | Reusable Blade status badge |

### 1.2 Identified Pain Points

#### PO Index (`po.index`)
| # | Pain Point | Technical Detail | Impact |
|---|-----------|-----------------|--------|
| 1 | Redundant collection iteration | 5 separate `$rows->getCollection()->where('status', 'X')` calls, each O(n) | Performance degradation on pages with many rows |
| 2 | Incomplete status filter | Chips display "Delayed" but the dropdown filter omits it; hardcoded "Full"/"Partial" options alongside `TermCatalog::options()` | Filter/search mismatch, user confusion |
| 3 | Missing temporal filtering | No PO date range filter in the form or query | Cannot narrow results by time period |
| 4 | Non-interactive table | No client-side search; relies solely on server pagination | Poor discoverability for large result sets |
| 5 | Unclickable PO numbers | Users must click a separate "Detail" button | Extra click, reduced scan efficiency |
| 6 | Sparse row data | Only PO#, date, supplier, status, and action — no ETA or outstanding info | Limited at-a-glance tracking signal |

#### PO Create (`po.create`)
| # | Pain Point | Technical Detail | Impact |
|---|-----------|-----------------|--------|
| 7 | Inline JavaScript monolith | 260 lines of vanilla JS embedded in Blade, mixing item lookup, subtotal math, duplicate detection, and row management | Untestable, difficult to maintain, bloats HTML |
| 8 | Full item catalog serialization | `@json($items)` injects up to 1000 items into every page load | Large payload, no fuzzy search or debouncing |
| 9 | Duplicate CDN dependencies | Loads jQuery and Select2 via CDN at lines 302-303; layout already loads them at line 1272 | Double jQuery load, potential conflicts, wasted bandwidth |
| 10 | No keyboard ergonomics | Must click "+ Tambah Barang" to add rows; no "Enter to add" or "Ctrl+S to save" | Slower data entry, especially for power users |
| 11 | Hidden remarks field | `items[idx][remarks]` is a hidden input with no UI to edit | Important context is lost or forgotten |
| 12 | No pre-submit validation | Form submits to server and returns errors on reload | Poor feedback loop, wasted round-trips |

#### PO Show (`po.show`)
| # | Pain Point | Technical Detail | Impact |
|---|-----------|-----------------|--------|
| 13 | O(N×3) modal rendering | Each item renders 3 modals (cancel, force-close, tracking) server-side in a Blade `@foreach` | Massive HTML bloat — 150+ modal DOM nodes for 50 items |
| 14 | Fragile DOM-to-data round-trip | `buildTrackingRows()` reads 13 `data-*` attributes per row to rebuild timeline for copy/export | Brittle JS, redundant data attribute on every `<tr>` |
| 15 | Verbose status help text | 7-branch `@elseif` chain for status contextual help, duplicated in `po/exports/detail.blade.php` | Maintenance burden, DRY violation |
| 16 | Always-visible bulk form | Bulk ETD update form renders with all inputs `disabled` when PO is final | Visual noise, reduced scannability |
| 17 | Crowded action column | 3 separate controls per item (ETD input, Cancel, Force Close) always rendered | Cognitive overload, especially for final items |
| 18 | No progress visualization | Summary chips show counts only; no progress bar or completion metric | Users cannot assess overall PO progress at a glance |
| 19 | Non-sortable item table | Items rendered in creation order; cannot sort by ETD, status, or quantity | Hard to prioritize follow-up on large POs |
| 20 | Scattered quick actions | Export Excel in card header; Cancel PO in sidebar; no unified action toolbar | Inconsistent interaction patterns |

---

## 2. Problem Statement

The PO module serves two primary user journeys:

**Journey A — Data Entry Operator** opens `/po/create` to record a new purchase order. The
current form requires manual item code typing, provides no autocomplete, offers no
pre-submit validation, and forces a full page reload to see errors. The inline JavaScript
is tightly coupled to the template, making future improvements risky.

**Journey B — PO Tracker** opens `/po` to find and monitor existing orders. The list
provides summary chips but no ETA column, no date filtering, and no inline search.
Clicking into a PO (`/po/{id}`) reveals a detail page that renders dozens of modal
dialogs (one set per item), making the initial page load slow and the DOM unwieldy.
Tracking data must be re-assembled by JavaScript reading `data-*` attributes — a pattern
that breaks if the table structure changes.

Both journeys are hindered by **fragmented UI patterns**, **redundant script loading**, and
**absence of at-a-glance status visualization**.

---

## 3. Solution Architecture

### 3.1 Design Principles

| Principle | Application |
|-----------|-------------|
| **Progressive disclosure** | Hide advanced controls (bulk ETD, cancel/force-close) behind expandable sections or context menus; show only what's needed for the current item state. |
| **Single source of truth** | Item tracking data should be serialized once as JSON, not duplicated across `data-*` attributes and `<td>` content. |
| **Server-first, client-enhanced** | Core actions (export, copy) handled server-side for reliability; client-side JS only for interactivity enhancements. |
| **Consistent interaction patterns** | Use the same modal pattern, button style, and status representation across all three views. |
| **Performance by design** | Eliminate O(N) anti-patterns; aim for O(1) or O(N) total operations, not O(N×K). |

### 3.2 Component Decomposition

The refactoring introduces two new reusable Blade components:

```
resources/views/components/
├── status-badge.blade.php          [existing] — visual status indicator
├── status-help.blade.php           [new]      — contextual help text per status + scope
└── item-summary.blade.php          [new]      — progress + count summary for PO items
```

**x-item-summary** component API:
```blade
<x-item-summary :counts="$itemSummary" :received="$totalReceived" :ordered="$totalOrdered" />
```
Renders: summary chips + progress bar + progress label. Reusable on `po.show` and the
monitoring dashboard.

**x-status-help** component API:
```blade
<x-status-help :status="$item->monitoring_status" scope="item" />
```
Returns contextual help text based on a lookup table in `PurchaseOrderItemStatusResolver`.

### 3.3 Frontend Stack Alignment

The project currently has a **hybrid frontend**: Vite + Alpine (configured but unused by
PO views) alongside jQuery + Bootstrap 4.6 + AdminLTE 3.2 loaded via CDN.

**Recommendation:** Maintain the jQuery/BS4 stack for Blade-driven pages to avoid a
full migration, but consolidate script loading into the layout and extract page-specific
JS into `resources/js/` modules registered through Vite. This keeps changes incremental
and low-risk.

---

## 4. Refactoring Specification

### 4.1 PO Index — List & Discovery

#### 4.1.1 Summary Chips Optimization

**Current:** 5 separate `where()` calls on the paginated collection (O(5n) per page).

**Target:** Single `groupBy` pass.

```php
// PurchaseOrderController::index()
$statusCounts = $rows->getCollection()
    ->groupBy('status')
    ->mapWithKeys(fn ($group, $key) => [DocumentTermStatus::label('po_status', $key): $group->count()])
    ->all();
```

In the template, iterate a canonical status order: `['Full', 'Partial', 'Delayed', 'Closed', 'Cancelled']`
and read `$statusCounts[$label] ?? 0`.

**Also add:** A "Delayed" chip that was previously absent from the filter dropdown.

#### 4.1.2 Status Filter Harmonization

**Current:** Hardcoded "Full Delivered"/"Partial Delivered" options mixed with
`TermCatalog::options('po_status', ['Open', 'Late', 'Closed', 'Cancelled'])`.

**Target:** Single source — `TermCatalog::options('po_status', ...)` returning all
active statuses from the `document_terms` table, falling back to the canonical set.

```blade
<select name="status" class="form-control form-control-sm">
    <option value="">Semua Status</option>
    @foreach (\App\Support\TermCatalog::options('po_status', \App\Support\DocumentTermCodes::poStatuses()) as $value => $label)
        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
    @endforeach
</select>
```

#### 4.1.3 Supplier Search

**Current:** Plain `<select>` rendering all suppliers — unusable at scale.

**Target:** Apply Select2 with search capability. The `<select>` already has the
`supplier-select` class pattern from `po.create`; reuse this consistently.

```javascript
$('.supplier-select').select2({
    width: '100%',
    placeholder: 'Semua Supplier',
    allowClear: true,
    dropdownParent: $(document.body)
});
```

#### 4.1.4 PO Date Range Filter

Add two date inputs to the filter grid. Extend `PurchaseOrderIndexQuery::base()`:

```php
->when($request->filled('date_from'), fn (Builder $q) => $q->whereDate('po.po_date', '>=', $request->input('date_from')))
->when($request->filled('date_to'),   fn (Builder $q) => $q->whereDate('po.po_date', '<=', $request->input('date_to')))
```

#### 4.1.5 Inline Table Search

Add a client-side search box above the table that filters visible rows by PO number,
supplier code, or supplier name. No server round-trip needed for the current page.

#### 4.1.6 Clickable PO Numbers & Additional Columns

- Wrap `$r->po_number` in `<a href="{{ route('po.show', $r->po_number) }}">`.
- Add an "ETA" column showing `$r->eta_date` formatted.
- Add status-based row highlighting: `table-danger` for Late/Delayed, `table-warning`
  for Open, plain for Closed/Cancelled.

#### 4.1.7 Create PO FAB

Add a bottom-right floating action button that persists across scroll, linking to
`route('po.create')`. Also wire "Create PO" into the existing Ctrl+K command palette.

---

### 4.2 PO Create — Data Entry

#### 4.2.1 Extract Inline JavaScript

Move the 260-line inline script into `resources/js/po-create.js` (Vite module):

```js
// resources/js/po-create.js
import { initItemRows } from './po-item-rows';

export function initPoCreate({ items, oldItems }) {
    const tbody = document.querySelector('#po-items-table tbody');
    // ... all existing logic, now testable and lintable
}
```

Register in Vite config:
```js
// vite.config.js — add to laravel input
input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/po-create.js']
```

In the Blade template: `@vite('resources/js/po-create.js')` + a tiny inline boot script.

#### 4.2.2 Item Code Autocomplete

Replace the free-text input with a **typeahead-style autocomplete** using a `<datalist>`
element (no external dependency, degrades gracefully):

```html
<input type="text" list="item-codes" class="form-control item-code-input" ...>
<datalist id="item-codes">
    @foreach($items as $item)
        <option value="{{ $item->item_code }} | {{ $item->item_name }} — {{ $item->unit_name }}">
    @endforeach
</datalist>
```

On selection or blur, parse the selected value and auto-fill name/unit via the pre-loaded
item map.

**For large catalogs:** Replace the datalist approach with a server-backed API endpoint:

```
GET /api/items?search={query}  →  [{"item_code": ..., "item_name": ..., "unit_name": ..., "id": ...}]
```

Add a new route in `routes/api.php` (or `web.php` with auth middleware) backed by a
simple query on the `items` table.

#### 4.2.3 Row-Level Remarks Editing

Add a small "notes" icon (fas fa-sticky-note) in each row that toggles inline editing
of `items[idx][remarks]` via a textarea that expands on click.

#### 4.2.4 Keyboard Ergonomics

- **Enter** in the item code field of the last row → add a new row.
- **Ctrl+S** → trigger form submit (intercept `keydown` globally on the create page).
- **Ctrl+N** → add new item row.

#### 4.2.5 Submit Button States

```javascript
form.addEventListener('submit', () => {
    submitBtn.prop('disabled', true).text('Menyimpan...');
});
```

Also add a **"Save and New"** button that clones the supplier/date but resets items,
allowing rapid entry of multiple POs.

#### 4.2.6 Client-Side Validation

Before submit, validate:
- At least 1 item row
- Each `item_id` is non-empty (code was resolved)
- Each `ordered_qty` ≥ 0.01
- No duplicate item codes

Show an inline alert banner above the form on failure (no server round-trip).

---

### 4.3 PO Show — Detail & Tracking

#### 4.3.1 Dynamic Modal System

**Current:** `N × 3` modals rendered in Blade loop.

**Target:** 3 modal templates rendered once, populated dynamically via JavaScript.

```html
<!-- Single modal per action type, rendered once at end of template -->
<div class="modal fade" id="itemActionModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <!-- Action, item ID, cancel reason injected dynamically -->
        </form>
    </div>
</div>
```

Action buttons on each row use a unified pattern:

```html
<button class="btn btn-sm btn-outline-danger"
    data-action="cancel-item"
    data-item-id="{{ $item->id }}"
    data-item-code="{{ $item->item_code }}"
    {{ $item->can_cancel ? '' : 'disabled' }}>
    Cancel
</button>
```

A single event listener routes all actions:

```javascript
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;

    const action = btn.dataset.action;
    const itemId = btn.dataset.itemId;

    switch (action) {
        case 'view-tracking':
            openTrackingModal(itemId);
            break;
        case 'edit-etd':
            openEtdModal(itemId);
            break;
        case 'cancel-item':
            openActionModal(itemId, 'cancel-item');
            break;
        case 'force-close':
            openActionModal(itemId, 'force-close');
            break;
    }
});
```

#### 4.3.2 Server-Side Tracking Export

Replace 86 lines of DOM-walking JS (`buildTrackingRows`, `buildTrackingCopyText`,
`buildTrackingTsv`, `copyTracking`, `exportTracking`) with server endpoints:

```
GET  /po/{po}/item/{item}/tracking/export-excel   →  TSV Excel download
GET  /po/{po}/item/{item}/tracking/copy-text     →  plain text response for clipboard
```

Controller methods render existing Blade export partials (`po.exports.tracking-copy`,
`po.exports.tracking-tsv`) — the data is already available from the query result,
no need to re-read from DOM.

#### 4.3.3 Status Help Text Lookup

Add a static method to `PurchaseOrderItemStatusResolver`:

```php
public static function statusHelpText(string $status): ?string
{
    return match ($status) {
        DocumentTermCodes::ITEM_WAITING         => 'Belum ada konfirmasi ETD dari supplier.',
        DocumentTermCodes::ITEM_CONFIRMED       => 'Sudah dikonfirmasi, menunggu pengiriman atau receiving.',
        DocumentTermCodes::ITEM_LATE            => 'ETD lewat, item belum selesai diterima.',
        DocumentTermCodes::ITEM_PARTIAL         => 'Sudah diterima sebagian, outstanding masih tersisa.',
        DocumentTermCodes::ITEM_CLOSED          => 'Item selesai. Seluruh qty PO sudah diterima.',
        DocumentTermCodes::ITEM_FORCE_CLOSED    => 'Item ditutup paksa. Outstanding dihentikan secara manual.',
        DocumentTermCodes::ITEM_CANCELLED       => 'Item dibatalkan.',
        default                                 => null,
    };
}
```

Use via the `x-status-help` component:

```blade
<x-status-help :status="$item->monitoring_status" scope="item" />
```

#### 4.3.4 Conditional Bulk ETD Form

Wrap the entire bulk update form in a conditional:

```blade
@if (!$poIsFinal)
    <div class="card-body">
        <!-- bulk update form -->
    </div>
@else
    <div class="card-body">
        <div class="alert alert-light border mb-0">
            PO sudah final. Bulk update ETD dinonaktifkan.
        </div>
    </div>
@endif
```

#### 4.3.5 Progress Bar for PO Completion

Below the item summary chips, add:

```blade
<div class="progress-bar-container">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="small text-muted">Progress Penerimaan</span>
        <span class="small fw-bold">{{ NumberFormatter::trim($received) }} / {{ NumberFormatter::trim($ordered) }} {{ $unit }}</span>
    </div>
    <div class="progress" style="height: 10px">
        <div class="progress-bar bg-success" role="progressbar"
             style="width: {{ $percent }}%"
             aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
        </div>
    </div>
</div>
```

#### 4.3.6 Consolidated Action Column

For final items (Closed, Force Closed, Cancelled), render a single muted badge instead
of 3 disabled buttons:

```blade
@if ($item->monitoring_status === 'Closed')
    <span class="badge bg-light text-muted">Final — Diterima Penuh</span>
@elseif ($item->can_update_etd || $item->can_cancel || $item->can_force_close)
    <!-- ETD input + Cancel + Force Close buttons -->
@else
    <span class="badge bg-light text-muted">Tidak dapat diubah</span>
@endif
```

#### 4.3.7 Sortable Item Table

Add `sort` and `direction` query params. Extend `PurchaseOrderDetailQuery` to accept
sort fields:

```php
// Supported: 'item_code', 'item_name', 'ordered_qty', 'received_qty',
//             'outstanding_qty', 'etd_date', 'monitoring_status'
$items = $items->orderBy($request->input('sort', 'i.item_code'),
                         $request->input('direction', 'asc'));
```

Render sortable headers as links that toggle direction:

```blade
<th scope="col">
    <a href="?sort=etd_date&direction={{ $direction === 'asc' ? 'desc' : 'asc' }}">
        ETD @if($sort === 'etd_date')<i class="fas fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>@endif
    </a>
</th>
```

#### 4.3.8 Timeline Visualization

Replace the flat table in the tracking modal with a **vertical timeline**:

```
[PO Created] ──→ [Shipment 1] ──→ [GR 1] ──→ [GR 2] ──→ [Shipment 2] ──→ [GR 3]
   15 Sep        20 Sep            22 Sep         23 Sep     30 Sep          02 Oct
   Ordered: 100  Qty Masuk: 30    Qty Masuk: 40  Qty Masuk: 20  Qty Masuk: 30  ✓ Selesai
```

Each node has a color-coded dot (green = closed/received, blue = shipped, yellow =
waiting, red = late) and a tooltip with full details. Implement using CSS `border-left`
timeline or a lightweight Alpine.js component.

#### 4.3.9 Quick Actions Toolbar

Add a sticky toolbar below the page header:

```blade
<div class="quick-actions-toolbar">
    <a href="{{ route('po.index') }}" class="btn btn-sm btn-light">Kembali ke List</a>
    <a href="{{ route('po.export-detail-excel', $po->po_number) }}" class="btn btn-sm btn-outline-success">Export Excel</a>
    @if ($poCanCancel)
        <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#cancelPoModal">Batalkan PO</button>
    @endif
    <button class="btn btn-sm btn-outline-primary" id="refreshStatusBtn">Refresh Status</button>
</div>
```

The "Refresh Status" button calls a new endpoint that triggers `ErpFlow::refreshPoStatusByOutstanding($po->id)`
and reloads the page with a success toast.

---

## 5. Implementation Roadmap

### Phase 1 — Foundation (Week 1)
Critical fixes with high impact and low risk.

| # | Task | Priority | Effort | Files |
|---|------|----------|--------|-------|
| 1 | Consolidate summary chips to single `groupBy` pass | P1 | 1d | `PurchaseOrderController.php`, `po/index.blade.php` |
| 2 | Fix status filter (use `TermCatalog::options` consistently, add "Delayed") | P1 | 1d | `po/index.blade.php` |
| 3 | Make PO number clickable in list | P1 | 2h | `po/index.blade.php` |
| 4 | Hide bulk ETD form when PO is final | P1 | 2h | `po/show.blade.php` |
| 5 | Extract status help text to `PurchaseOrderItemStatusResolver::statusHelpText()` | P1 | 1d | `PurchaseOrderItemStatusResolver.php`, `po/show.blade.php`, `po/exports/detail.blade.php` |
| 6 | Create `x-status-help` Blade component | P1 | 1d | `resources/views/components/status-help.blade.php` |

### Phase 2 — Performance & Maintainability (Week 2)
Eliminate rendering bloat and fragile JS.

| # | Task | Priority | Effort | Files |
|---|------|----------|--------|-------|
| 7 | Replace N×3 modal rendering with single dynamic modal | P1 | 3d | `po/show.blade.php`, `resources/js/po-show.js` |
| 8 | Add server-side tracking export endpoints | P1 | 2d | `PurchaseOrderController.php`, `routes/web.php` |
| 9 | Create `x-item-summary` Blade component (chips + progress bar) | P2 | 1d | `resources/views/components/item-summary.blade.php` |
| 10 | Consolidate script loading via Vite (remove duplicate jQuery CDNs) | P2 | 2d | `layouts/erp.blade.php`, `po/create.blade.php`, `po/show.blade.php`, `vite.config.js` |

### Phase 3 — Data Entry Enhancement (Week 3)
Optimize the PO creation workflow.

| # | Task | Priority | Effort | Files |
|---|------|----------|--------|-------|
| 11 | Extract create-page JS to Vite module | P2 | 2d | `resources/js/po-create.js`, `po/create.blade.php`, `vite.config.js` |
| 12 | Add item code autocomplete (datalist or API) | P2 | 2d | `po/create.blade.php`, `resources/js/po-create.js`, `routes/web.php` |
| 13 | Add keyboard shortcuts (Ctrl+S save, Enter add row) | P3 | 1d | `resources/js/po-create.js` |
| 14 | Add client-side validation + submit loading state | P3 | 1d | `resources/js/po-create.js`, `po/create.blade.php` |
| 15 | Add row-level remarks editing | P3 | 1d | `po/create.blade.php`, `resources/js/po-create.js` |
| 16 | Add "Save and New" button | P3 | 1d | `po/create.blade.php`, `PurchaseOrderController.php` |

### Phase 4 — Tracking Enhancement (Week 4)
Advanced data visualization and filtering.

| # | Task | Priority | Effort | Files |
|---|------|----------|--------|-------|
| 17 | Add progress bar to PO detail | P3 | 1d | `po/show.blade.php`, `x-item-summary` |
| 18 | Add ETA column + row highlighting to PO list | P3 | 1d | `po/index.blade.php` |
| 19 | Add PO date range filter | P2 | 1d | `po/index.blade.php`, `PurchaseOrderIndexQuery.php` |
| 20 | Add inline search to PO list table | P3 | 1d | `po/index.blade.php`, `resources/js/po-index.js` |
| 21 | Add sortable item table columns | P3 | 2d | `po/show.blade.php`, `PurchaseOrderDetailQuery.php` |
| 22 | Consolidate action column (hide for final items) | P3 | 1d | `po/show.blade.php` |
| 23 | Add timeline visualization to tracking modal | P4 | 2d | `po/show.blade.php`, `resources/js/po-show.js` |
| 24 | Add quick actions toolbar | P3 | 1d | `po/show.blade.php`, controller methods |

---

## 6. Success Metrics & Validation Criteria

| Metric | Target | Measurement |
|--------|--------|-------------|
| PO index page load time | ≤ 20% reduction | Browser DevTools → Network tab |
| PO show HTML size (50 items) | ≤ 60% reduction | Network tab → Response size |
| Create PO form submission errors | 0 server round-trips for validation | Manual test: empty form submit |
| Duplicate jQuery load | 0 occurrences | Network tab → filter `jquery` |
| Modal count in PO show | 3 (constant, not N×3) | DOM inspection |
| User task completion time (Create PO, 5 items) | ≥ 15% faster | Time-based test |
| Mobile usability score | ≥ 90 (Lighthouse) | Chrome Lighthouse |

---

## 7. Files Modified Summary

| File | Change Type | Scope |
|------|-------------|-------|
| `app/Http/Controllers/PurchaseOrderController.php` | Modified | Add tracking export methods, optimize index |
| `app/Queries/PurchaseOrders/PurchaseOrderIndexQuery.php` | Modified | Add date range filter |
| `app/Queries/PurchaseOrders/PurchaseOrderDetailQuery.php` | Modified | Add sort support |
| `app/Support/PurchaseOrderItemStatusResolver.php` | Modified | Add `statusHelpText()` |
| `resources/views/layouts/erp.blade.php` | Modified | Consolidate scripts, add `@vite` |
| `resources/views/po/index.blade.php` | Modified | New chips, search, ETA column, date filter |
| `resources/views/po/create.blade.php` | Modified | Extract JS, add autocomplete, validation |
| `resources/views/po/show.blade.php` | Modified | Dynamic modals, progress bar, timeline |
| `resources/views/po/exports/detail.blade.php` | Modified | Use `x-status-help` component |
| `resources/views/components/status-help.blade.php` | **New** | Status contextual help |
| `resources/views/components/item-summary.blade.php` | **New** | Chips + progress bar |
| `resources/js/po-create.js` | **New** | Create page JS module |
| `resources/js/po-show.js` | **New** | Show page JS module |
| `resources/js/po-index.js` | **New** | Index page JS module |
| `resources/js/po-item-rows.js` | **New** | Shared item row logic |
| `vite.config.js` | Modified | Add new entry points |
| `routes/web.php` | Modified | Add tracking export + API routes |

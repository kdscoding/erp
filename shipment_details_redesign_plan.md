# Shipment Details Page Redesign Plan

**Page:** `/shipments/{id}` (ShipmentController@show)  
**Template:** `resources/views/shipments/show.blade.php`  
**Layout:** `resources/views/layouts/erp.blade.php`  
**Scope:** Visual hierarchy, typography, spacing, color palette, component layout, readability, and overall UX.

---

## 1. Current State Analysis

### 1.1 Page Structure (Top → Bottom)

| Section | File:Line | Current Implementation |
|---|---|---|
| **Breadcrumb** | `show.blade.php:47-53` | Plain text links "Home / Shipment / {shipment_number}" at 12.5px |
| **Page Header** | `show.blade.php:55-69` | Shipment number as `<h2>` (1.05rem), subtitle, and 2-3 inline action buttons (Back / Edit / Receive) |
| **Info Grid** | `show.blade.php:71-87` | 6-column grid of `info-box` cards: Supplier, Status, Tanggal, DN, Invoice, Donut Chart (receiving %) |
| **Item Table** | `show.blade.php:89-114` | `ui-surface` card with `ui-table`: 7 columns (PO, Item, Harga Invoice, Total Invoice, Qty Dikirim, Sudah Diterima, Sisa) |
| **Timeline & Related** | `show.blade.php:116-159` | Collapsible `ui-surface` with custom CSS timeline, related doc cards (PO, GR, Audit, Supplier Perf, Tracking) |

### 1.2 Critical Technical Issues

1. **Data gap (controller:230-239):** The `show()` method only passes `$shipment` and `$lines` to the view. The template references `$receivingPercent`, `$timeline`, `$poNumbers`, and `$receiptLinks` — all with fallback defaults (`0`, `[]`, `[]`, `[]`). This means:
   - Donut chart always shows **0%** even for fully received shipments
   - Timeline section is always **empty**
   - Related document cards (PO numbers, receipts) are always **empty**
   - The entire visual value of these sections is lost

2. **Inline JavaScript:** The timeline toggle uses `onclick="this.nextElementSibling.classList.toggle('open')"` (line 119) — an anti-pattern for maintainability.

3. **No unit of measure:** The item table shows quantities (shipped, received, remainder) without displaying the unit (e.g., "kg", "pcs"). The receiving show page (`receiving/show.blade.php:88-97`) includes `{{ $item->unit_name }}` in every qty cell — this should be matched.

4. **No summary metrics:** High-level KPIs (total shipped, total received, total invoice amount, line count, PO count) are absent from the detail page, unlike the worklist which aggregates these.

### 1.3 Design System Context

The project uses a **LEMON** design language (`layouts/erp.blade.php:26-38`):

| Variable | Value | Usage |
|---|---|---|
| `--lemon-yellow` | `#f1d93b` | Accent, alerts |
| `--lemon-green` | `#9ecb3c` | Primary actions, progress-fill |
| `--lemon-green-deep` | `#6f9628` | Links, active states |
| `--lemon-ink` | `#304218` | Primary text |
| `--lemon-olive` | `#566d2a` | Secondary text |
| `--lemon-bg` | `#f7f8ea` | Background |
| `--lemon-line` | `#dfe6b8` | Borders |

**Base styles:** 12.5px body font, AdminLTE 3.2 + Tailwind CSS (`app.css`), FontAwesome 6.5.2.

**Existing component patterns:** `ui-surface`, `ui-surface-head`, `ui-surface-title`, `info-box`, `info-label`, `info-value`, `doc-number`, `doc-meta`, `summary-chip`, `page-head`, `page-actions`, `breadcrumb-nav`, `timeline`, `related-card`, `donut-chart`.

---

## 2. Visual Hierarchy

### 2.1 Current Problems

- **Title vs. subtitle insufficient contrast:** `.page-section-title` is 1.05rem / 700 weight; `.page-section-subtitle` is 0.84rem / normal weight — the 2px size difference is too subtle for reliable scanning.
- **Info-box labels are 7px** (`.info-label` at `erp.blade.php:442`) — nearly unreadable without magnification.
- **Table headers at ~10.5px** (`ui-table thead th` at line 363) are the same visual weight as body text at 12.5px.
- **Action buttons have no order:** Back, Edit, and Receive are all `btn-sm` with equal visual weight. Primary actions should be visually dominant.
- **Donut chart is orphaned in the info grid** — it sits alongside uniform label-value pairs but is a visualization widget with different visual weight and purpose.
- **No content grouping:** The info grid, item table, and timeline sections all have the same `mt-3` spacing and surface treatment, making it hard to distinguish primary content (header + items) from secondary (timeline + related docs).

### 2.2 Redesign Recommendations

#### 2.2.1 Section Hierarchy (Visual Tiers)

| Tier | Sections | Treatment |
|---|---|---|
| **1 — Document Header** | Shipment number title + status badge + action buttons | Boldest, largest text; elevated surface with subtle shadow |
| **2 — Key Summary** | Summary chips (total shipped, received, open qty, invoice total, line count) | Grid of stat cards with large values, small labels |
| **3 — Core Detail** | Header metadata (supplier, dates, DN, invoice) | Clean key-value cards or definition list |
| **4 — Line Items Table** | Item list with quantities, prices, and remaining | Full-width table, sticky header, clear column hierarchy |
| **5 — Progress & Timeline** | Receiving progress + shipment lifecycle timeline | Secondary visual treatment, collapsible by default |
| **6 — Related Actions** | Related documents, audit trail, external links | Card-based grid, lower visual weight |

#### 2.2.2 Action Button Prioritization

Restructure `page-actions` (line 60-68) into a primary/secondary hierarchy:

```
[Receive  ]  (primary, prominent — only shown for Shipped/Partial statuses)
[Edit     ]  (secondary outline)
[Back     ]  (secondary outline, muted)
[Cancel   ]  (secondary outline, danger — for Draft)
[Export   ]  (secondary outline, success — add Excel export for all statuses)
```

**Rationale:** Only one primary action per view reduces cognitive load. Secondary actions are visually de-emphasized via outline style.

#### 2.2.3 Donut Chart Relocation

Move the donut chart from the flat info-grid into a **Summary Chips** tier above the line items table, alongside text-based KPIs. This matches the pattern used in `components/item-summary.blade.php` (progress bar + label/value pair) and the `summary-chip` pattern in `erp.blade.php:242-270`.

---

## 3. Typography

### 3.1 Current State

| Element | Selector | Font Size | Weight | Color |
|---|---|---|---|---|
| Body | `body` | 12.5px | — | `--lemon-ink` (#304218) |
| Info labels | `.info-label` | **7px** | 700 | #7d866f |
| Info values | `.info-value` | 16px | 800 | #2f3c1b |
| Table headers | `.ui-table thead th` | **10.5px** | 700 | #5f7331 |
| Table body | `.table td` | — | — | inherited |
| Section titles | `.ui-surface-title` | 14px | 800 | #314216 |
| Page title | `.page-section-title` | ~17px | 800 | `--lemon-ink` |
| Page subtitle | `.page-section-subtitle` | ~14px | 400 | #74805f |

### 3.2 Problems

1. **7px info labels** — below WCAG AA minimum readability (WCAG 21 requires ≥12.5px for normal text, ≥10.5px for bold). This label is both tiny AND bold — the user's eyes must work harder.
2. **10.5px table headers** — borderline for readability on standard-DPI displays.
3. **Body font 12.5px** — functional for dense data tables but too small for headers and metadata.
4. **No systematic scale.** Font sizes are set ad-hoc per selector with no consistent modular ratio.

### 3.3 Redesign Recommendations

#### 3.3.1 Adopt a Modular Typography Scale

Introduce a consistent scale anchored to the 12.5px body baseline:

| Role | Size | Line Height | Weight | Usage |
|---|---|---|---|---|
| `display-sm` | 13px | 1.4 | 700 | Table headers, info labels |
| `body-xs` | 11px | 1.4 | 400 | Helper text, captions |
| `body-sm` | 12px | 1.4 | 400 | Secondary labels |
| `body-base` | 12.5px | 1.5 | 400 | Body text, table cells |
| `body-md` | 14px | 1.4 | 500 | Section subtitles, labels |
| `body-lg` | 15px | 1.3 | 600 | Key-value labels, card labels |
| `heading-xs` | 16px | 1.3 | 700 | Info box values, small headings |
| `heading-sm` | 18px | 1.3 | 700 | Section titles (`ui-surface-title`) |
| `heading-md` | 20px | 1.25 | 800 | Card headers, subsection titles |
| `heading-lg` | 24px | 1.15 | 800 | Page title (`page-section-title`) |

#### 3.3.2 Specific Changes

- **Info labels:** Increase from 7px → 11px (`body-xs`). Add letter-spacing reset (0.02em is sufficient at 11px). Change color to `#5f7331` (same green-ink used for table headers) for consistency.
- **Table headers:** Increase from 10.5px → 11px. Keep uppercase + letter-spacing. Add 600 weight for better scanability.
- **Page title vs. subtitle:** Increase title from ~17px → 24px. Increase subtitle from ~14px → 14px/500 weight. Ensure ≥3px size gap and ≥200 weight difference for clear visual hierarchy.
- **Font stack:** The app loads Figtree (`app.blade.php:12`) but the ERP layout uses system sans-serif. Standardize on `font-sans` (Tailwind's system font stack) for consistency across both layouts.

---

## 4. Spacing

### 4.1 Current State

- `page-shell` gap: 1rem (`erp.blade.php:207`)
- `info-grid` gap: 0.85rem (line 410)
- `ui-surface-body` padding: 1rem (line 304)
- `table-wrap` padding: 1rem (line 351)
- `table td` padding: 0.45rem 0.55rem (line 373)
- `info-box` padding: 0.95rem 1rem (line 427)
- `related-card` padding: 0.5rem 0.7rem (line 37)

### 4.2 Problems

- **Inconsistent vertical rhythm:** 1rem gaps between sections, 0.85rem inside info-grid, 0.75rem inside related cards — no systematic 4px/8px baseline grid.
- **Donut chart cramped:** The info-box containing the donut has `min-height: 96px` and `padding: .95rem 1rem` — the same as text-only boxes, but the donut (110px) is taller than the content, causing visual misalignment.
- **Table cell padding is too tight** (0.45rem ≈ 7px vertical, 8px horizontal) for 12.5px font — reduces legibility.
- **Related cards have no consistent height** — cards with icons vs. link cards stack at different heights.

### 4.3 Redesign Recommendations

#### 4.3.1 Adopt a 4px Baseline Grid

Define a spacing token system:

| Token | Value | Usage |
|---|---|---|
| `sp-3xs` | 2px | Tight inline gaps |
| `sp-2xs` | 4px | Badge padding, tight cell padding |
| `sp-xs` | 8px | Table cell vertical padding |
| `sp-sm` | 12px | Card body padding, small gaps |
| `sp-md` | 16px | Section body padding, medium gaps |
| `sp-lg` | 24px | Gap between major sections |
| `sp-xl` | 32px | Gap between tiered content blocks |

#### 4.3.2 Specific Spacing Changes

- **Between major sections:** Increase from `mt-3` (1rem) to 32px (`sp-xl`) for Tier 1→2, Tier 2→3 transitions. Use 24px (`sp-lg`) for same-tier separations (e.g., summary chips → info grid → table).
- **Table cell padding:** Increase vertical padding from 7px → 8px (`sp-xs`), horizontal from 8px → 12px (`sp-sm`). This gives ~1.5x the tap target area and reduces text crowding.
- **Info-box padding:** Standardize to 12px (`sp-sm`) vertical + 16px (`sp-md`) horizontal for better content breathing room.
- **Donut chart box:** Give its container a fixed height matching other info boxes, or remove it from the grid entirely and place it as a standalone summary element (see Section 2.2.3).
- **Related cards:** Increase gap from 4px to 6px inside `.related-card`. Add `min-height: 64px` and `aspect-ratio: auto` so all cards align even with variable content.

---

## 5. Color Palette

### 5.1 Current State

The palette uses a **yellow-green gradient family** with olive accents. Key issues:

| Issue | Current | Impact |
|---|---|---|
| **Low contrast text** | `#7d866f`, `#7a8660`, `#74805f` for secondary text | WCAG contrast ratios hover around 3:1 against the light yellowish background |
| **Multiple gradient overlays** | Background radial gradients, info-box linear gradients, button gradients, surface shadows | Visually busy, modern "neumorphic" style clashes with data-dense ERP context |
| **Status badge inconsistency** | `badge` font at 10.5px with `.bg-success`/`.bg-warning`/`.bg-danger` overrides | Hard to read at small sizes; colors pulled from Bootstrap defaults, not the lemon theme |
| **Donut chart uses CSS conic-gradient** | Hardcoded gradient on inline style — no theming support | Can't easily change color based on status (e.g., green for 100%, yellow for partial) |

### 5.2 Redesign Recommendations

#### 5.2.1 Simplify the Gradient Overload

The current design applies **three simultaneous visual effects** to surfaces:
1. Solid background (`rgba(255,255,255,.96)`)
2. Inset highlight (`inset 0 1px 0 rgba(255,255,255,.85)`)
3. Drop shadow (`0 14px 28px rgba(111,150,40,.05)`)

**Recommendation:** Reduce to **one** effect per surface:
- **Surfaces (cards):** Solid white background + 1px border (`--lemon-line`) + subtle shadow (`0 4px 12px rgba(0,0,0,.03)`). Drop the inset highlight and heavy shadow.
- **Body background:** Replace the dual radial-gradient + solid color with a single very-light `--lemon-bg` (`#f7f8ea` at 40% opacity over white) or just solid `#f7f8ea`.
- **Buttons:** Replace multi-stop gradients with a **flat fill** using `--lemon-green` (`#9ecb3c`), keeping the hover state as a slightly darker variant (`#88b93b`). This reduces visual noise and improves scanning speed.

#### 5.2.2 Establish a Color Scale Token System

| Token | Role | Value | Usage |
|---|---|---|---|
| `--color-text-primary` | Primary text | `#304218` (current `--lemon-ink`) | Headings, values, key data |
| `--color-text-secondary` | Secondary text | `#6b7f3a` | Labels, metadata, subtitles |
| `--color-text-tertiary` | Disabled/muted | `#9aa57a` | Helper text, placeholders |
| `--color-surface` | Card background | `#ffffff` | All info boxes, tables, surfaces |
| `--color-surface-subtle` | Subsurface | `#f7f8ea` (`--lemon-bg`) | Alternative row backgrounds |
| `--color-border` | Borders | `#dfe6b8` (`--lemon-line`) | All 1px borders |
| `--color-border-strong` | Strong borders | `#b6cf45` (focus green) | Active/selected states |
| `--color-accent` | Primary accent | `#9ecb3c` (`--lemon-green`) | Buttons, progress fills |
| `--color-accent-hover` | Hover | `#88b93b` | Button hover states |
| `--color-success` | Success | `#88b93b` | Received/completed status |
| `--color-warning` | Warning | `#f1d93b` (yellow) | Partial/in-progress |
| `--color-danger` | Error/Danger | `#e53e3e` | Overdue, cancelled, remainder |

**Rationale for changes:**
- `#e53e3e` (a proper red) replaces the ad-hoc `bg-danger` Bootstrap default for remainder badges — the current design uses Bootstrap's red which has no lemon-theme harmony.
- Secondary text `#6b7f3a` ensures WCAG AA contrast (7:1) against the white surface.
- All tokens map to existing `--lemon-*` variables so no new color values need to be introduced — just reorganized into a logical system.

#### 5.2.3 Status Badge & Row Color Refinement

**Current:** Remainder badge uses inline ternary logic:
```php
$rem == 0 ? 'bg-success' : ($rem < ($line->shipped_qty * 0.25) ? 'bg-warning text-dark' : 'bg-danger')
```
This is functional but the thresholds (0%, 25%) are hardcoded in the template.

**Recommendation:** Move threshold logic to a helper method or use the existing `DocumentTermStatus::badgeClasses()` system already present for status badges. Define named states:
- `0%` → Green (fully received)
- `1–25%` → Yellow (small remainder)
- `>25%` → Red (significant outstanding)

This keeps the visual logic in one place and allows future threshold adjustments without touching the Blade template.

---

## 6. Component Layout & Structure

### 6.1 Current Layout Flow

```
Breadcrumb
    ↓
Page Header (title + subtitle + actions)
    ↓
Info Grid [Supplier] [Status] [Tanggal] [DN] [Invoice] [Donut]
    ↓
Item Table Card (7 columns, no subtotals)
    ↓
Timeline & Related Docs Card (collapsed by default)
```

### 6.2 Redesign Layout Flow

```
Breadcrumb (simplified, smaller)
    ↓
Page Header (larger title + status badge inline + prioritized actions)
    ↓
Summary Chips Row [Total Shipped] [Received %] [Open Qty] [Invoice Amt] [Lines] [POs]
    ↓
Info Grid (key-value pairs only — donut moved to summary)
    ↓
Item Table Card (enhanced columns, subtotals, unit of measure)
    ↓
Progress & Timeline Card (expanded by default for shipped shipments; collapsed for draft)
    ↓
Related Documents Card (icon grid with consistent sizing)
```

### 6.3 Specific Component Recommendations

#### 6.3.1 Breadcrumb (show.blade.php:47-53)

**Problem:** Uses raw `/` separators and a custom `breadcrumb-nav` class with ad-hoc styling. No `aria-current` on the active page, poor accessibility.

**Redesign:**
- Use a structured breadcrumb component with chevron icons (`fas fa-chevron-right`).
- Add `aria-current="page"` to the last item.
- Increase font size from 12.5px → 12px with muted secondary color.
- Reduce vertical space: the breadcrumb currently adds 0.5rem margin before every page.

#### 6.3.2 Page Header (show.blade.php:55-69)

**Problem:** `page-section-title` (17px) is too close in weight to the topbar title (36px). The status badge sits in an info-box below rather than inline with the title. Action buttons lack priority.

**Redesign:**
- Merge `.page-section-title` with the status badge into a single inline row:
  ```
  <span class="page-title">SHIPMENT-001</span>
  <x-status-badge :status="..." scope="shipment" />
  ```
- Move action buttons into a dedicated `.page-actions` bar with clear primary/secondary distinction.
- Reduce the subtitle to a single line of helper text with `--color-text-secondary`.

#### 6.3.3 Summary Chips (New Component)

**Add above the info grid:** A compact row of KPI summary chips for at-a-glance scanning, leveraging the existing `summary-chip` CSS pattern (`erp.blade.php:248-270`).

**Proposed chips:**
| Label | Value | Source |
|---|---|---|
| Total Dikirim | 1,250 | `SUM(lines.shipped_qty)` |
| Sudah Diterima | 750 (60%) | `SUM(lines.received_qty)` + donut |
| Sisa | 500 | `SUM(rem)` |
| Total Invoice | Rp 150,000,000 | `SUM(lines.invoice_line_total)` |
| Line Items | 8 | `COUNT(lines)` |
| Purchase Orders | 3 | `COUNT(DISTINCT po_numbers)` |

The donut chart becomes part of the "Sudah Diterima" chip: a small donut next to the percentage value. When `receivingPercent` is 0, show "-" instead of a donut.

#### 6.3.4 Info Grid (show.blade.php:71-87)

**Problem:** The donut chart info-box (`min-height: 96px`) clashes with other boxes. The colored border accent (`info-box::before` at line 432-439) adds visual noise. The info-box uses a vertical flex layout that doesn't align well with the donut's centering.

**Redesign:**
- Remove the donut from the info grid entirely (moved to summary chips).
- Reduce info grid from 6 items to 5 items: Supplier, Status, Tanggal, Delivery Note, Invoice.
- Simplify `.info-box` styling: remove the 4px colored gradient border, remove the inset highlight. Use a clean card with 1px border.
- Increase gap between info boxes from 0.85rem → 12px (`sp-xs`) for tighter grouping.
- On mobile (≤576px), stack to 1 column instead of the current 1-column only at 576px (info-grid currently uses 2 columns at tablet, 1 at phone).

#### 6.3.5 Item Table (show.blade.php:89-114)

**Problems:**
- Missing unit of measure (receiving/show.blade.php includes `{{ $item->unit_name }}`)
- No table totals/subtotal row
- Column header labels are abbreviated ("Harga Invoice", "Total Invoice", "Qty Dikirim", "Sudah Diterima", "Sisa") and could be clearer
- Remainder badge uses inline ternary color logic
- `table-hover` class is applied but hovers are barely visible (`#f1d93b08` at line 378)

**Redesign:**
- Add `unit_name` to the quantity columns (Shipped, Received, Remaining)
- Add a **totals row** at the bottom with: total shipped, total received, total remaining, total invoice amount
- Make the remaining badge a dedicated `<x-status-badge>` component instead of inline ternary
- Improve hover contrast: change from `rgba(241,217,59,.08)` → `rgba(53,66,24,.03)` (a subtle dark overlay rather than a yellow tint that can look like a warning)
- Add `sticky-top` on thead for scroll-friendly long tables
- Rename column headers for clarity (align with LabelRegistry):
  - "Harga Invoice" → "Harga Invoice (Unit)"
  - "Total Invoice" → "Subtotal Invoice"
  - "Qty Dikirik" → "Qty Kirim"
  - "Sudah Diterima" → "Qty Terima"
  - "Sisa" → "Sisa Kirim"

#### 6.3.6 Timeline Section (show.blade.php:116-159)

**Problems:**
- Inline `onclick` JavaScript (anti-pattern)
- Collapsed by default (timeline-section hidden unless `.open` class added)
- The timeline and related documents share the same card with no visual separation
- Timeline uses ad-hoc CSS with `--lemon-line`, `--lemon-green`, `--lemon-yellow` variable references that could be inconsistent

**Redesign:**
- Replace inline onclick with a proper `data-toggle` attribute + a small JS event listener (or Alpine.js if available)
- Split into **two separate cards**: "Shipment Progress Timeline" and "Related Documents"
- For **Draft** status: collapse the timeline entirely (no meaningful events yet) and show a soft info note: "Draft shipment — timeline will appear after confirmation."
- For **Shipped/Partial Received**: expand timeline by default
- For **Received**: show full timeline with all milestones completed
- Replace custom `.timeline` CSS with a cleaner vertical-stepper pattern using the `--lemon-line` as the connecting line and colored dots for milestones

#### 6.3.7 Related Documents Card (show.blade.php:139-156)

**Problems:**
- Mixes `<div class="related-card">` and `<a class="related-card">` — inconsistent interaction patterns
- All cards have the same visual treatment regardless of whether they're links or labels
- No hover state on link cards
- The grid uses `d-flex flex-wrap gap-2` which doesn't produce consistent card sizes

**Redesign:**
- Standardize all related documents as `<a class="related-card">` elements (even PO numbers and receipts can be clickable if routes exist)
- Add a clear hover state: `transform: translateY(-1px)` + `box-shadow: 0 4px 12px rgba(48,66,24,.08)`
- Use consistent icon sizing (28px circles) and typography (title 13px/600, meta 11px/400)
- Grid: `grid-template-columns: repeat(auto-fill, minmax(180px, 1fr))` instead of flexbox-wrap for uniform card sizes
- Group related cards with section labels: "Purchase Orders", "Goods Receipts", "Tools"

---

## 7. Usability & Interaction Improvements

### 7.1 Missing Data Issue (Critical)

The `ShipmentController@show()` method (line 230-239) does **not** compute or pass the following variables that the template expects:

| Variable | Template Usage | Suggested Computation |
|---|---|---|
| `$receivingPercent` | Donut chart value | `round(received_qty / shipped_qty * 100)` |
| `$timeline` | Timeline events | Query `audit_trail` records for this shipment |
| `$poNumbers` | Related PO cards | Extract from `$lines` collection (`->pluck('po_number')->unique()`) |
| `$receiptLinks` | Related GR cards | Query `goods_receipt_items` joined to receipts matching shipment |

**Recommendation:** Extend the `show()` method to compute and pass these values, or add a view composer specifically for the `shipments.show` view.

### 7.2 Mobile Responsiveness

**Current:** The info-grid is `repeat(auto-fit, minmax(180px, 1fr))` — 3 columns at desktop, 2 at tablet, 1 at phone. The donut chart breaks this rhythm.

**Recommendation:**
- Move the donut chart out of the info-grid (as noted in 6.3.3) so all info boxes are uniform height.
- Add a responsive table wrapper with horizontal scroll for the item table on mobile (`<div class="table-responsive">`).
- Ensure action buttons stack vertically on mobile (`flex-direction: column` at ≤576px).

### 7.3 Empty States

**Current:** When `$lines` is empty (no items in shipment), the table shows a bare `<tbody>` with nothing. The timeline section shows nothing if `$timeline` is empty.

**Recommendation:**
- Add an empty state for the item table: "No items in this shipment yet" with an icon.
- Add an empty state for the timeline: "No events recorded for this shipment status."
- When `$receivingPercent` is 0, replace the donut with a simple text: "Belum diterima" in muted text.

### 7.4 Accessibility

**Current issues:**
- Inline `onclick` handler lacks keyboard navigation
- Color-only status indicators (badges) have no text alternatives
- The donut chart has no ARIA labels or semantic alternatives
- Breadcrumb missing `aria-current`
- Table lacks `scope="col"` on `<th>` elements

**Recommendations:**
- Replace inline JS with event listeners
- Add `aria-label` to status badges: `<x-status-badge aria-label="Status: {{ $shipment->status }}">`
- Add a visually-hidden text alternative inside the donut chart: `<span class="sr-only">{{ $receivingPercent }}% received</span>`
- Add `aria-current="page"` to the breadcrumb's last item
- Add `scope="col"` to all `<th>` elements in the table header

---

## 8. Implementation Roadmap

| Priority | Task | Effort | Files to Modify |
|---|---|---|---|
| **P0** | Extend `ShipmentController@show()` to pass `$receivingPercent`, `$timeline`, `$poNumbers`, `$receiptLinks` | S | `app/Http/Controllers/ShipmentController.php` |
| **P0** | Add unit_name to shipment line query + template | S | Controller query + `show.blade.php:97-108` |
| **P0** | Fix inline onclick → proper JS listener for timeline toggle | S | `show.blade.php:119` |
| **P0** | Increase `.info-label` from 7px → 11px | XS | `layouts/erp.blade.php:442` |
| **P1** | Add Summary Chips component above info grid | M | New component + `show.blade.php` |
| **P1** | Add totals row to item table | S | `show.blade.php:94-112` |
| **P1** | Improve table hover contrast + sticky header | S | `layouts/erp.blade.php:377` + table class |
| **P1** | Restructure info grid: remove donut, simplify box style | M | `show.blade.php:71-87` + `erp.blade.php` |
| **P2** | Replace custom timeline CSS with clean stepper | M | `show.blade.php:12-41` (inline styles) |
| **P2** | Split Timeline & Related Docs into separate cards | S | `show.blade.php:116-159` |
| **P2** | Refactor related cards into consistent link pattern | S | `show.blade.php:139-156` |
| **P2** | Standardize typography scale tokens | M | `layouts/erp.blade.php:26-38` + new token definitions |
| **P3** | Add ARIA attributes for accessibility | S | Various template locations |
| **P3** | Add empty states for tables and timeline | S | `show.blade.php` |
| **P3** | Remove gradient overload from surfaces | M | `layouts/erp.blade.php` |

---

## 9. Summary of Key Recommendations

1. **Fix the data gap** — The controller must actually compute `receivingPercent`, `timeline`, `poNumbers`, and `receiptLinks`. None of the current visualizations work.
2. **Add summary chips** — Move the donut chart and key KPIs above the info grid for immediate scanning.
3. **Fix typography** — Increase 7px info labels to 11px minimum; establish a modular scale.
4. **Simplify the color system** — Reduce gradient/overlay overload on surfaces; consolidate to a logical token system.
5. **Add missing data** — Unit of measure in table, totals row, empty states.
6. **Improve interactions** — Replace inline JS, add hover states, add ARIA.
7. **Restructure layout** — Clear visual tiers: header → summary → detail → line items → timeline → related docs.
8. **Mobile-first responsiveness** — Ensure table scroll, stacked actions, and uniform info boxes on small screens.

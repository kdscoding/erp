# Shipment Detail View — UX/UI Redesign Proposal

**Page:** `/shipments/{id}` (Shipment Detail)  
**Context:** Backend error (Undefined variable `$shipmentDate` in `show.blade.php:299`) prevents live review.  
**Approach:** Proposal grounded in logistics/supply chain UX best practices, informed by existing codebase patterns (`show.blade.php`, `receiving/show.blade.php`, `shipments/index.blade.php`, shared UI components).

---

## Table of Contents

1. [Layout Structure](#1-layout-structure)
2. [Data Visualization & Information Architecture](#2-data-visualization--information-architecture)
3. [User Experience Enhancements](#3-user-experience-enhancements)
4. [Navigation & Interaction](#4-navigation--interaction)
5. [Appendix: Wireframe Descriptions](#5-appendix-wireframe-descriptions)

---

## 1. Layout Structure

### 1.1 Design Principle

Shipment detail pages in logistics ERP systems must serve two distinct user goals simultaneously: **fast status recognition** (know at a glance where a shipment stands) and **drill-down detail access** (find specific data when needed). The layout separates these into tiered visual zones.

### 1.2 Proposed Section Hierarchy (Top → Bottom)

| Tier | Section | Purpose | Visual Treatment |
|------|---------|---------|------------------|
| **T1** | Breadcrumb + Page Header | Orientation + identity | Full-width, largest type, elevated shadow |
| **T2** | Status Summary Bar | Instant status scanning | Colored badges, progress indicator, KPI chips |
| **T3** | Key-Value Information Grid | Core document metadata | 4–5 uniform cards, clean borders |
| **T4** | Line Items Table | Detailed item data | Full-width, sticky header, sortable columns |
| **T5** | Shipment Timeline | Audit trail & lifecycle | Collapsible vertical stepper |
| **T6** | Related Documents & Actions | Cross-module navigation | Card grid with grouped sections |

### 1.3 Section Details

#### T1: Breadcrumb + Page Header

```
Home / Shipments / SHP-2026-00142  [Status: Shipped]
=====================================================
Supplier: IndoFood Supplies · Shipment Date: 15-09-2026 · DN: DN-4421
                                          [Back] [Edit] [Print Label] [Contact Carrier]
```

- **Breadcrumb**: Chevron-separated (`>`), `aria-current="page"` on last item. Clickable links to Dashboard → Shipment List → Current.
- **Title row**: Shipment number as `<h1>` (not `<h2>`) with inline status badge — single line, immediate identification.
- **Subtitle line**: Supplier name · Shipment Date · DN number on one metadata line using muted secondary color.
- **Action buttons**: Right-aligned, with clear primary/secondary hierarchy (see Section 4).

#### T2: Status Summary Bar

A horizontal bar spanning full width containing:

**Left side — Progress indicator:**
```
Receiving Progress: ████████░░░░░░░░░░░░ 42%  ·  Received 420 / Shipped 1,000 pcs · Open 580 pcs
```
- Linear progress bar (not donut — linear is more scannable for partial values).
- Text labels on same row at desktop; stacks vertically on mobile.

**Right side — KPI chips:**
```
[Total Kirim: 1,000]  [Total Terima: 420]  [Sisa: 580]  [Invoice: Rp 75jt]  [Lines: 8]  [POs: 3]
```
- 6 compact chips, each 120px minimum width, flex-wrap on narrow screens.
- Color-coded left border: primary (default), success (received-related), warning (open/remainder).

#### T3: Key-Value Information Grid

5 cards in a responsive grid (3 columns desktop, 2 tablet, 1 mobile):

| Card | Content |
|------|---------|
| Supplier | Name + link to supplier profile |
| Status | Status badge (inline, colored) |
| Shipment Date | Formatted date (DD-MM-YYYY) |
| Delivery Note | DN number (link to receiving if exists) |
| Invoice | Invoice number (link to finance if exists) |

- Removed from original: Donut chart (moved to T2), invoice date, currency — these move into expandable detail or the timeline section.

#### T4: Line Items Table

Full-width card with sticky table header. Columns:

| Column | Content | Width | Notes |
|--------|---------|-------|-------|
| PO # | PO number | 100px | Links to PO detail |
| Item Code | Item code | 120px | Bold, mono-style |
| Item Name | Description | 200px | Truncated with tooltip |
| UoM | Unit | 60px | pcs, kg, etc. |
| Invoice Price | Unit price | 120px | Right-aligned |
| Invoice Total | Subtotal | 120px | Right-aligned |
| Qty Kirim | Shipped quantity | 100px | Right-aligned |
| Qty Terima | Received quantity | 100px | Right-aligned, green if = shipped |
| Sisa Kirim | Remaining | 100px | Badge: green/yellow/red by threshold |

- **Totals row**: Bold, light background, spans all monetary columns.
- **Sticky header**: Table header stays visible during scroll (beneficial for 10+ line shipments).
- **Horizontal scroll**: On mobile, table wraps in `table-responsive` container.

#### T5: Shipment Timeline (Collapsible)

Vertical stepper showing status transitions:

```
● Draft Created        01-09-2026 09:15  by Admin
│
● Confirmed & Shipped  05-09-2026 14:30  by Purchasing
│   DN: DN-4421 · Invoice: INV-8821
│
● Partial Received     12-09-2026 10:00  by Warehouse
│   Received 420/1000 pcs
│
○ Expected Completion  (projected)
```

- **Draft status**: Section collapsed by default with note: "Draft shipment — timeline will appear after confirmation."
- **Shipped/Partial**: Expanded by default (active shipment = relevant to current work).
- **Received**: Full timeline with all milestones.
- Uses the existing `<x-ui.empty-state>` component if no events recorded.

#### T6: Related Documents & Actions

Grouped into sub-sections within one card:

**Purchase Orders:**
```
PO-2026-0089  (3 lines)  →  PO-2026-0091  (5 lines)  →  PO-2026-0095  (2 lines)
```

**Goods Receipts:**
```
GR-4421  (Partial)  →  GR-4450 (if exists)
```

**Tools:**
```
Audit Trail  ·  Supplier Performance  ·  Unified Tracking  ·  Print Label
```

---

## 2. Data Visualization & Information Architecture

### 2.1 Information Architecture Principles

Logistics users perform three primary tasks on a shipment detail page:

1. **Check status**: "Has my shipment arrived?"
2. **Verify quantities**: "How much was shipped vs. received?"
3. **Find specific data**: "What's the invoice number for line 3?"

The IA prioritizes these in that order — status and quantities appear above the fold; specific data requires scrolling.

### 2.2 Key Data Points & Priority Matrix

| Priority | Data Point | Presentation | Rationale |
|----------|-----------|-------------|-----------|
| **P0** | Shipment status | Large colored badge (inline with title) | First thing user looks for |
| **P0** | Receiving % | Progress bar + text (T2) | Most frequent query |
| **P0** | Shipped / Received / Open qty | Text trio adjacent to progress bar | Quantities pair with percentage |
| **P1** | Supplier | Key-value card (T3) | Who is this from? |
| **P1** | Key dates (shipment date, ETA) | Key-value card (T3) | When did it ship? When arrive? |
| **P1** | Document references (DN, Invoice) | Key-value card + links (T3) | Reference numbers for cross-checking |
| **P1** | Line item quantities | Table with per-line Qty columns | Detail verification |
| **P2** | Item descriptions & codes | Table columns | Need to scroll — secondary |
| **P2** | Invoice amounts | Table column + totals row | Financial reference |
| **P2** | PO relationships | Related docs card (T6) | Traceability |
| **P3** | Timeline events | Collapsible stepper (T5) | Historical, not immediate need |
| **P3** | Audit trail | Within timeline | Deep dive only |

### 2.3 Visualization Components

#### Progress Bar (Receiving Progress)
- **Type**: Horizontal linear bar (not donut — research shows linear bars communicate partial completion faster for values between 20–80%).
- **Color**: Gradient from `--lemon-green` to `--lemon-green-deep` matching existing theme.
- **Labels**: Percentage text always visible; quantities below bar.
- **Edge cases**: 0% → empty bar with "Belum diterima" label; 100% → full green bar with "Selesai" badge.

#### Status Badges
Use consistent, color-coded badges across all contexts:

| Status | Color | Icon |
|--------|-------|------|
| Draft | Gray | 📝 |
| Shipped | Blue | 🚚 |
| Partial Received | Orange | 📦 |
| Received | Green | ✅ |
| Cancelled | Red | ❌ |

- Badge includes both color AND text label (never color-only).
- Minimum font size: 11px (not the current 7–10px).
- WCAG AA contrast ratio minimum (4.5:1 for normal text).

#### Quantity Remainder Indicators (Per Line)

Color thresholds based on supply chain best practices:

| Condition | Color | Meaning |
|-----------|-------|---------|
| 0 remaining | Green | Fully received |
| 1–25% remaining | Yellow | Small outstanding — may resolve soon |
| >25% remaining | Red | Significant outstanding — needs attention |

### 2.4 Map Integration (Recommended Enhancement)

For shipments in transit (Shipped or Partial Received status), add an **optional expandable map widget** below the progress bar:

```
[📍 Track on Map]  (expandable button)
```

- Displays last known GPS coordinates or route waypoints.
- Integrates with carrier API if available; shows "No live tracking available" placeholder otherwise.
- Not a primary element — collapsed by default to avoid clutter.
- Follows patterns used by major logistics platforms (FedEx, JNE, SiCepat).

### 2.5 Empty States

Every data section needs a graceful empty state using `<x-ui.empty-state>`:

| Section | Empty State |
|---------|-------------|
| Line items table | "No items in this shipment yet" + "Add items" CTA (for Draft) |
| Timeline | "No events recorded" (for Draft) |
| Related docs | "No related documents found" |
| Progress bar | "Shipment data incomplete" (error state) |

---

## 3. User Experience Enhancements

### 3.1 Cognitive Load Reduction Strategies

#### Progressive Disclosure
Not all information needs to be visible simultaneously. Apply:

| Element | Default State | Expand Trigger |
|---------|--------------|----------------|
| Timeline | Collapsed (unless active shipment) | Click "Show Timeline" |
| Map widget | Hidden | Click "Track on Map" |
| Advanced filters (line items) | All columns visible | "Columns ▾" menu to hide/show |
| Invoice/PO detail links | Shown as identifiers only | Click to navigate |

#### Chunking
Group related data into recognizable clusters:
- **Shipment identity** (T1): Number, status, key dates — one glance.
- **Quantities** (T2): Shipped, received, remaining — paired together.
- **Line detail** (T4): Per-item breakdown — for verification tasks.
- **Cross-references** (T6): Links to related documents — for navigation.

#### Visual Hierarchy
| Element | Font Size | Weight | Color |
|---------|-----------|--------|-------|
| Shipment number | 24px | 800 (ExtraBold) | `--lemon-ink` |
| Section titles | 16px | 700 (Bold) | `--lemon-ink` |
| KPI values | 20px | 700 | Dark |
| KPI labels | 11px | 600 | Muted |
| Table headers | 11px | 600, uppercase | Green-tinted |
| Table body | 12.5px | 400 | `--lemon-ink` |
| Metadata text | 12px | 400 | `--color-text-secondary` |

### 3.2 Responsive Behavior

| Viewport | Layout Changes |
|----------|---------------|
| Desktop (≥1024px) | Full layout, 3-column info grid, inline progress details |
| Tablet (768–1023px) | 2-column info grid, progress text below bar, stacked header |
| Mobile (<768px) | Single column, all cards stack, table horizontal scroll, action buttons full-width |

### 3.3 Interaction Feedback

| Interaction | Feedback |
|-------------|----------|
| Hover on table row | Subtle background tint (`rgba(48,66,24,.03)`) |
| Hover on related card | `translateY(-1px)` + shadow lift |
| Click status badge | Tooltip with status description |
| Progress bar | Animated fill on page load (150ms ease-out) |
| Sufficient scroll | Sticky section header on line items table |

### 3.4 Accessibility (WCAG 2.1 AA)

| Requirement | Implementation |
|-------------|----------------|
| Keyboard navigation | All action buttons focusable; timeline toggle is `<button>` not `onclick` |
| Screen reader | `aria-label` on status badges; `aria-current="page"` on breadcrumb |
| Color independence | Status text always accompanies color; badges include icon |
| Focus indicators | 2px outline on `:focus` for all interactive elements |
| Skip links | "Skip to main content" link at page top |
| Table semantics | `<th scope="col">` on all header cells; `<caption>` on table |
| Dynamic updates | Live region (`aria-live="polite"`) for progress bar changes |
| Language | `lang="id"` on Indonesian text, `lang="en"` on English labels |

### 3.5 Error State Design

When the shipment data fails to load (the current scenario — backend error):

```
┌─────────────────────────────────────────────────┐
│ ⚠️  Data Unavailable                            │
│                                                 │
│  Shipment details could not be loaded.          │
│  The system encountered an error.               │
│                                                 │
│  [Retry]  [View Shipment List]  [Contact Admin]│
└─────────────────────────────────────────────────┘
```

- Use `<x-ui.empty-state>` with `size="lg"`.
- Never show a blank page or partial rendering.
- Include both a retry action and a safe navigation exit.

### 3.6 Quick Actions

Context-aware action bar that changes based on shipment status:

| Status | Available Actions |
|--------|------------------|
| Draft | Edit, Export Draft, Mark Shipped, Cancel, Print Draft |
| Shipped | Receive, Print Label, Contact Carrier, Export |
| Partial Received | Continue Receiving, Print Label, Export, View Timeline |
| Received | Print Receipt, Export, View Audit Trail |
| Cancelled | View Audit Trail, Export, Restore (if permitted) |

Actions appear as icon buttons (compact) at mobile width and text+icon buttons at desktop.

---

## 4. Navigation & Interaction

### 4.1 Breadcrumb Navigation

```
Dashboard > Shipments > SHP-2026-00142
```

**Implementation details:**
- Use `<nav aria-label="Breadcrumb">` with ordered list semantics.
- Separator: `>` chevron icon (not `/`).
- Current page: `<span aria-current="page">` with bold weight.
- Font size: 12px, secondary color.
- Below 768px, collapse to: `Shipments > SHP-2026-00142` (drop "Dashboard").

### 4.2 Quick-Action Buttons

Primary actions are context-aware and ranked by frequency of use for the current status:

```
┌──────────────────────────────────────────────────────────────────┐
│ [📦 Receive]  [✏️ Edit]  [🖨️ Print Label]  [📧 Contact Carrier] │
│ [📤 Export]  [↩️ Back]                                          │
└──────────────────────────────────────────────────────────────────┘
```

**Priority order (left to right):**
1. **Primary**: Status-specific action (Receive for Shipped, Edit for Draft, etc.)
2. **Secondary**: Edit / Print / Contact
3. **Tertiary**: Export / Back

**Visual differentiation:**
- Primary: Solid fill with `--lemon-green` background.
- Secondary: Outline style with `--lemon-green-deep` border.
- Tertiary: Ghost/light style.

### 4.3 Contextual Menus

#### Per-Line Item Menu (Table row hover)
When hovering over a line item row, reveal a compact action menu:

```
[👁️ View] [✏️ Edit Qty] [📋 Copy] [⋯ More ▾]
```

The "More" dropdown includes:
- View PO Detail
- View Receiving History for Item
- Add Note
- Flag Exception

#### Shipment-Level Context Menu (Top-right)
Accessed via a `⋯` (kebab) menu alongside the primary action buttons:

```
[⋯]
  ├── Duplicate Shipment
  ├── Add Note
  ├── Set Reminder
  ├── Archive
  ├── Print All Labels
  └── Export Audit Trail
```

### 4.4 Sticky Action Bar (Mobile)

On mobile (<768px), pin a compact action bar at the bottom of the viewport:

```
┌─────────────────────────────────────┐
│ [Receive]  [Edit]  [Back]  [⋯]     │
└─────────────────────────────────────┘
```

- Background: white with top border (`var(--lemon-line)`).
- `z-index: 40` to stay above content.
- Auto-hides when user scrolls down (reveals on scroll-up).

### 4.5 In-Page Navigation

For long pages, add a **section anchor** at the top-right of the content area:

```
Jump to: [Summary ▾] [Items ▾] [Timeline ▾] [Related ▾]
```

- Dropdown menu that scrolls to the section.
- Also renders as a visible tab bar on desktop (≥1200px).

### 4.6 Back Navigation Enhancement

The "Back" button should preserve the user's previous context:

```php
<a href="{{ session('previous_url', route('shipments.index')) }}" class="btn btn-sm btn-light">
    <i class="fas fa-arrow-left"></i> Back
</a>
```

Store the previous URL before navigating to detail:
```php
// In controller or middleware:
session(['previous_url' => url()->previous()]);
```

### 4.7 Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl+N` | New Draft Shipment |
| `Ctrl+F` | Focus search/filter |
| `Escape` | Close any modal/dropdown |
| `Ctrl+P` | Print current shipment |

Implement via a small JS listener that checks `e.ctrlKey` + key character.

---

## 5. Appendix: Wireframe Descriptions

### Wireframe A: Desktop View (≥1024px)

```
┌────────────────────────────────────────────────────────────────────────────┐
│ Breadcrumb: Dashboard > Shipments > SHP-2026-00142                         │
├────────────────────────────────────────────────────────────────────────────┤
│  SHP-2026-00142  [🟢 Shipped]         [Receive] [Edit] [Print] [⋯]       │
│  IndoFood Supplies · 15-09-2026 · DN-4421                                  │
├────────────────────────────────────────────────────────────────────────────┤
│  Receiving: ████████░░░░ 42%  Received 420/1000  Open 580                  │
│  [Total Kirim: 1,000] [Terima: 420] [Sisa: 580] [Invoice: Rp75jt] [8 lines]│
├────────────────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐ ┌──────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐ │
│  │ Supplier     │ │ Status   │ │ Shipment  │ │ Del. Note │ │ Invoice   │ │
│  │ IndoFood     │ │ 🟢Ship   │ │ 15-09-26  │ │ DN-4421   │ │ INV-8821  │ │
│  └──────────────┘ └──────────┘ └───────────┘ └───────────┘ └───────────┘ │
├────────────────────────────────────────────────────────────────────────────┤
│  Item Dalam Dokumen  (8 lines · Total: Rp 75,000,000)                     │
│  ┌────────┬────────┬─────┬─────┬──────┬──────┬──────┬──────┬────────┐    │
│  │ PO #   │ Item   │ UoM │ Price│ Total│ Kirim│ Terim│ Sisa │ Status │    │
│  ├────────┼────────┼─────┼─────┼──────┼──────┼──────┼──────┼────────┤    │
│  │PO-0089 │ ITM001 │ pcs │ 50k  │ 25jt │ 300  │ 200  │ 100  │  🟡    │    │
│  │PO-0089 │ ITM002 │ kg  │ 80k  │ 40jt │ 200  │ 200  │ 0    │  ✅    │    │
│  │PO-0091 │ ITM003 │ pcs │ 120k │ 60jt │ 300  │ 0    │ 300  │  🔴    │    │
│  │  ... (more lines)                                                      │
│  ├────────┴────────┴─────┴─────┴──────┴──────┴──────┴──────┼────────┤    │
│  │                                       TOTALS:  1000 │ 420  │ 580   │    │
│  └──────────────────────────────────────────────────────────────────┘    │
├────────────────────────────────────────────────────────────────────────────┤
│  Shipment Timeline  [▲ Collapse]                                         │
│  ● Draft Created    01-09-26 09:15  by Admin                              │
│  ● Confirmed        05-09-26 14:30  by Purchasing                        │
│  ● Partial Recv.    12-09-26 10:00  by Warehouse  (420/1000)             │
│  ○ Expected Done    (projected)                                           │
├────────────────────────────────────────────────────────────────────────────┤
│  Related Documents & Actions                                             │
│  Purchase Orders: [PO-0089] [PO-0091] [PO-0095]                          │
│  Goods Receipts:  [GR-4421]                                              │
│  Tools: [Audit Trail] [Supplier Perf] [Tracking] [Print Label]           │
└────────────────────────────────────────────────────────────────────────────┘
```

### Wireframe B: Mobile View (<768px)

```
┌──────────────────────────────┐
│ Breadcrumb: Shipments > SHP  │
├──────────────────────────────┤
│ SHP-2026-00142  [🟢 Shipped]│
│ IndoFood · 15-09-2026       │
│ [Receive] [Edit] [⋯]        │
├──────────────────────────────┤
│ Progress: ████░░ 42%        │
│ Rec: 420 / Ship: 1000       │
│ [Open: 580]                 │
├──────────────────────────────┤
│ ┌──────────────────────────┐ │
│ │ Supplier  IndoFood       │ │
│ │ Status    🟢 Shipped     │ │
│ │ Date      15-09-2026     │ │
│ │ DN        DN-4421        │ │
│ │ Invoice   INV-8821       │ │
│ └──────────────────────────┘ │
├──────────────────────────────┤
│ Items (scroll →)            │
│ PO    Item    Kirim Ter Sisa│
│ PO-089 ITM01  300  200  100 │
│ PO-089 ITM02  200  200    0 │
├──────────────────────────────┤
│ Timeline        [Show ▸]    │
├──────────────────────────────┤
│ Related: [POs] [GR] [Audit] │
├──────────────────────────────┤
│ [Receive] [Edit] [Back] [⋯]│ ← sticky bar
└──────────────────────────────┘
```

---

## Implementation Priority Matrix

| Priority | Feature | Effort | Dependencies |
|----------|---------|--------|-------------|
| **P0** | Fix backend error (controller data gap) | Small | Controller `show()` method |
| **P0** | Status Summary Bar (progress + KPI chips) | Medium | New section + CSS |
| **P0** | Action button hierarchy | Small | CSS + Blade conditional logic |
| **P1** | Restructured info grid (5 cards, no donut) | Small | CSS changes |
| **P1** | Sticky table header on line items | Small | CSS `position: sticky` |
| **P1** | Per-line remainder badges with thresholds | Small | Helper function |
| **P2** | Timeline as separate collapsible card | Medium | New JS toggle + CSS |
| **P2** | Related docs with grouped sections | Small | HTML restructure |
| **P2** | Mobile sticky action bar | Medium | CSS + JS scroll detection |
| **P3** | Map widget for in-transit shipments | Large | External API integration |
| **P3** | Keyboard shortcuts | Small | JS listener |
| **P3** | In-page section navigation | Small | JS anchor scrolling |
| **P3** | Contextual per-line menus | Medium | CSS + JS |

---

*Proposal date: 2026-09-20*  
*Based on: Codebase analysis of `erp-monitoring-po`, logistics UX best practices, and existing design system (LEMON theme, AdminLTE 3.2 + Tailwind CSS).*

# Shipment Pages — UI/UX Redesign Specification

## Objective
Optimize the shipments page (`/shipments`) for two primary goals:
1. **Streamline data entry workflow** — reduce clicks, cognitive load, and friction during draft creation/editing.
2. **Increase efficiency of tracking & verifying** — surface shipment state visually and enable rapid cross-referencing.

---

## 1. Optimized Form Structures

### 1.1 Draft Builder → Tabbed Wizard Layout (Create & Edit)
**Problem:** The current builder scatters candidate selection, split board, and review form across separate sections requiring long scrolling.

**Solution:** Convert to a 3-step tabbed wizard with a sticky bottom action bar.

| Step | Tab Label | Content |
|------|-----------|---------|
| 1 | `Select Items` | Supplier filter + candidate table (same as current) |
| 2 | `Review & Edit` | Split board + line-item edit table (same as current) |
| 3 | `Confirm` | Summary confirmation with final save button |

- **Step indicator** (progress dots) always visible at top of the builder section.
- **Sticky action bar** at bottom of viewport: `[Back] [Next/Save Draft]` — always accessible without scrolling.
- Tab 3 auto-calculates totals (total qty, total invoice value, PO coverage %) for final confirmation.

### 1.2 Worklist → Inline Action Row
**Problem:** All actions buried behind dropdown menus; no inline status transitions.

**Solution:**
- Replace dropdown with **inline icon buttons** on each row for the 3 most common actions:
  - 👁️ View Detail → navigates to show page
  - ✏️ Edit Draft (only if Draft status)
  - 🚚 Mark Shipped (only if Shipped/Partial status, shown as primary)
- Secondary actions (Export, Import, Cancel, Continue Receiving) remain in a compact dropdown.
- **Inline status badge** is clickable — clicking directly transitions the shipment to next status (with confirmation toast).
- **Batch checkbox column** added; selecting rows reveals a bulk action toolbar (Mark Shipped, Export Selected, Cancel Selected).

### 1.3 Quick-Entry Shortcut on Worklist Header
- Add **`+ New Draft`** as a prominent primary button (top-right of worklist section) — already present but made more visible with larger padding.
- Add **`Ctrl+N`** keyboard shortcut to open the draft builder.
- Add quick-filter search box that filters the table **live** (no Apply button needed) with debounced 300ms input.

### 1.4 Field Grouping in Header Forms
**Problem:** Header fields in create/edit forms are a flat row of 12-column spans.

**Solution:** Group fields into logical cards:

| Card | Fields |
|------|--------|
| **Document Info** | Shipment Date, Delivery Note, Currency |
| **Invoice Info** | Invoice Number, Invoice Date |
| **Notes** | Supplier Remark, PO Reference Missing checkbox |

Cards are visually separated with subtle borders and appear in a 2-column grid on desktop, single column on mobile.

---

## 2. Enhanced Data Visualization

### 2.1 Kanban View Toggle on Worklist
**Problem:** All shipments displayed as a flat table regardless of status; users must scan every row to find items needing action.

**Solution:** Add a view toggle above the worklist table:
- **List View** (default — current table)
- **Kanban View** — 4 columns: `Draft` | `Shipped` | `Partial Received` | `Archive`

Each column displays shipment cards with:
- Shipment number (bold)
- Supplier name
- Delivery Note
- Progress bar (received/shipped ratio)
- Open qty badge
- Action icons (view, edit, mark-shipped)

Cards are draggable between columns to change status (with AJAX update).

### 2.2 Timeline Visualization on Detail Page (Show)
**Problem:** The detail page shows data in an info-grid + table but has no visual timeline of the shipment lifecycle.

**Solution:** Add a **vertical timeline** below the info-grid:

```
📅 Shipment Created ── 2024-01-15
🚚 Marked Shipped ──── 2024-01-20
📦 Partial Received ── 2024-01-25 (3/5 items)
✅ Fully Received ──── 2024-01-30
```

Each node shows:
- Icon representing the event type
- Date
- Description
- Connected by vertical lines (completed = green, pending = gray dashed)

Data sourced from audit/log tables linked to the shipment.

### 2.3 Radial Progress Indicator on Detail Page
Add a **donut/radial chart** (CSS-only) in the detail page header showing:
- Center text: `85%`
- Ring color: gradient based on completion
- Label below: `Receiving Progress`

This provides an at-a-glance completeness indicator for the shipment.

### 2.4 Inline Sparkline on Worklist Table
Add a tiny sparkline (5-cell bar chart) in the **Progress** column of the worklist table showing received vs shipped vs open as adjacent bars, making it scannable at a glance.

### 2.5 Color-Coded Quantity Indicators
| Condition | Color |
|-----------|-------|
| Outstanding qty = 0 | Green badge ✓ |
| Outstanding qty > 0 but < 25% | Yellow badge ⚠ |
| Outstanding qty >= 25% | Red badge 🔴 |

Applied to progress column, line item tables, and info-grid boxes.

---

## 3. Intuitive Navigation

### 3.1 Breadcrumb Navigation
Add breadcrumbs to all shipment pages:

```
Home > Shipment > Worklist          (worklist)
Home > Shipment > Create Draft      (builder)
Home > Shipment > {Shipment No.}    (detail)
Home > Shipment > Edit {Shipment No.} (edit)
```

### 3.2 Cross-Reference Links
On the detail page (show.blade.php), add a **Related Documents** section at the bottom:

| Document Type | Link | Description |
|---------------|------|-------------|
| Purchase Orders | PO list filtered by PO numbers | `Open PO detail` for each linked PO |
| Receiving Records | Receiving index filtered by shipment_id | Shows all GR documents for this shipment |
| Audit Trail | Audit viewer filtered by shipment | Full change history |
| Supplier Profile | Supplier performance page | Supplier details and metrics |

### 3.3 "Recently Viewed" Sidebar Panel
Add a collapsible sidebar panel (right side) on worklist/detail pages:

```
┌─────────────┐
│ Recent      │
│ Shipments   │
├─────────────┤
│ SO-2024-042 │ ← click to view
│ SO-2024-039 │
│ SO-2024-031 │
│ SO-2024-028 │
└─────────────┘
```

Persisted in `localStorage` — tracks the last 10 shipment IDs viewed.

### 3.4 Keyboard Shortcuts
| Shortcut | Action |
|----------|--------|
| `Ctrl+N` | New Draft Shipment |
| `Ctrl+F` | Focus search/filter input |
| `Ctrl+K` | Command Palette (already exists globally) |
| `Enter` (on focused row) | Open shipment detail |
| `Escape` | Close any modal |

### 3.5 URL Deep-Linking for Filters
Current filter form already uses GET method. Enhance by:
- Preserving all filter parameters in URL on every form interaction
- Adding a **"Copy Filter Link"** button to share current filter state
- Ensuring browser back/forward correctly restores filter state

### 3.6 Smart Navigation from Timeline Events
On the detail page timeline (2.2), each completed event is clickable:
- Clicking "Marked Shipped" navigates to the receiving process page filtered by that shipment.
- Clicking "Received" navigates to the receiving history showing the GR document.

---

## 4. Implementation Files

All changes are contained in 4 Blade templates:

| File | Changes |
|------|---------|
| `resources/views/shipments/index.blade.php` | Kanban toggle, inline actions, batch toolbar, sticky quick-filter, breadcrumbs, sparkline placeholders |
| `resources/views/shipments/create.blade.php` | Tabbed wizard, sticky action bar, field grouping, keyboard shortcuts |
| `resources/views/shipments/show.blade.php` | Timeline visualization, donut chart, related documents, breadcrumbs, recently viewed |
| `resources/views/shipments/edit.blade.php` | Tabbed layout, inline actions, field grouping, sticky action bar |

Plus a shared CSS file: `resources/css/shipments.css` for all redesign-specific styles.

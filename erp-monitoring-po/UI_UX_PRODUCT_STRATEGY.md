# UI/UX & Product Strategy Concept
## Digital Procurement & Supply Chain Monitoring (Item-Level)

Dokumen ini merangkum konsep UI/UX modern yang powerful, fluid, dan operasional untuk sistem procurement/supply chain internal.

---

## 1) Konsep Struktur (High-Level)

### A. Design Principles
1. **Operational Clarity First**: user harus langsung tahu mana item aman, mana item at-risk.
2. **Item-Level Visibility**: semua monitoring berpusat di level item, bukan hanya PO.
3. **Progressive Disclosure**: ringkas di dashboard, detail muncul saat drill-down.
4. **Low Friction Interaction**: update ETD/ETA harus inline, cepat, minim klik.
5. **Fluid Enterprise UI**: clean typography, soft shadows, bento grid, micro-interaction ringan.

### B. Information Hierarchy
- **Level 1 (Executive/Supervisor):** KPI + risk overview + bottleneck.
- **Level 2 (Operational):** daftar PO dan item-level milestones.
- **Level 3 (Execution):** update ETD/ETA, split shipment, receiving item-by-item.

---

## 2) Arsitektur Role & Access Control (3 Role)

### 1. Administrator
- Full access (setup, master data, order, shipment, receiving, user & access control).
- Konfigurasi alert threshold, toleransi keterlambatan, default workflow.

### 2. Supervisor/Pimpinan
- Fokus monitoring dashboard & approval/control decisions.
- Read-heavy + action terbatas (approve, hold, escalate).
- Melihat risk heatmap item-level lintas supplier/warehouse.

### 3. User/Staff (Opsional)
- Fokus eksekusi harian:
  - input/update ETD item,
  - split shipment,
  - posting receiving item-by-item,
  - update discrepancy notes.

---

## 3) Dashboard & KPI Concept (Supervisor Helikopter View)

## A. Bento Grid Layout (Desktop First)
Gunakan grid modular yang responsif:
- **KPI Baris 1 (Primary):**
  1. Open PO
  2. Open Items (outstanding item lines)
  3. At-Risk Items
  4. Overdue Items
  5. Received Today (item count)
  6. Supplier OTIF% (On Time In Full)

- **Bento Baris 2 (Risk & Flow):**
  - `At-Risk Items Panel` (soft amber highlight)
  - `Item Journey Funnel` (Confirmed → Shipped → Received)
  - `Top Delayed Suppliers`

- **Bento Baris 3 (Execution Queue):**
  - `Items Need ETD Update`
  - `Incoming Today/Tomorrow`
  - `Partial Receiving Queue`

## B. KPI Definitions
- **Open Items** = total line item dengan outstanding_qty > 0.
- **At-Risk Items** = item dengan ETD/ETA mendekati/melewati tolerance rule.
- **OTIF%** = item diterima tepat waktu & qty sesuai / total item diterima.
- **Partial Receiving Ratio** = jumlah PO partial / total PO aktif.

## C. Visual Risk Language
- Safe: soft green
- Watchlist: soft amber
- Critical: soft red
- Neutrals: slate/graphite background untuk kenyamanan mata

---

## 4) Layouting & Navigation (Clean but Accessible)

## A. Sidebar Structure
1. **Dashboard**
2. **Procurement**
   - Purchase Orders
   - Item ETD Planner
   - Supplier Confirmation
3. **Logistics**
   - Shipment Tracking
   - Split Shipment Board
4. **Receiving**
   - Receiving Queue (item-level)
   - Discrepancy & Hold
5. **Monitoring & Reports**
   - At-Risk Items
   - Supplier Performance
   - Traceability
6. **Administration**
   - Users & Access
   - Master Data
   - Settings

## B. Screen Pattern
- Sticky filter bar di atas tabel.
- Context chips: Plant, Warehouse, Supplier, Status.
- Split view:
  - kiri: list/table
  - kanan: detail drawer/timeline item.

---

## 5) Detail Interaction: Input ETD Per Item

## A. Efficient ETD Input Modes
1. **Inline Editing in Table**
   - Kolom ETD/ETA editable langsung.
   - Auto-save dengan indikator “Saved / Failed”.

2. **Bulk Update Panel**
   - Multi-select item lines.
   - Apply ETD/ETA massal + optional offset (+2 hari, +5 hari).

3. **Template by Supplier**
   - Default lead-time profile per supplier/item category.
   - ETD terisi otomatis, user tinggal koreksi.

## B. Micro-Interactions
- Hover row → quick actions (Update ETD, Add Note, Mark Confirmed).
- Snackbar feedback non-blocking setelah save.
- Status transition animation ringan (chip morph).

## C. Validation UX
- Hard validation untuk tanggal tidak logis.
- Soft warning untuk ETD melewati tolerance.
- “Explain why” note wajib jika ETD mundur.

---

## 6) Split Shipment & Partial Receiving UX

1. **Split Shipment View**
   - 1 PO line bisa dipecah menjadi beberapa shipment events.
   - Menampilkan planned qty vs shipped qty vs received qty.

2. **Receiving Checklist (Item-by-Item)**
   - Receiver menandai item yang datang.
   - Qty actual per item, accepted/rejected, evidence upload.

3. **Auto Outstanding Logic**
   - Sisa qty tetap status waiting.
   - PO status aggregate mengikuti item completion matrix.

4. **Mini Progress Bar per Item**
   - Confirmed → Shipped → Arrived → Received.
   - Membantu melihat progres tanpa baca teks panjang.

---

## 7) Smart Notification & Alerting

1. **At-Risk Engine**
   - Rule: ETD lewat tolerance / ETA berubah berkali-kali / qty mismatch.
2. **Supervisor Digest**
   - Ringkasan harian: overdue items, high-risk suppliers, pending approvals.
3. **Contextual Alerts**
   - Bukan spam; alert muncul saat buka modul terkait + dashboard summary.

---

## 8) Additional High-Impact Features

1. **Command Palette** (Ctrl/Cmd + K): cari PO/item/supplier super cepat.
2. **Saved Views** per role (mis. “At-Risk Hari Ini”, “Incoming Minggu Ini”).
3. **Action Center**: semua pending task lintas modul dalam satu inbox.
4. **Explainable Audit Trail**: siapa ubah ETD dari A ke B, kenapa, kapan.
5. **Mobile Companion View (read-first)** untuk supervisor on-the-go.

---

## 9) UX Delivery Plan (Praktis)

### Phase 1 (2 minggu)
- Dashboard bento + risk cards
- Item-level ETD inline edit
- Receiving item checklist

### Phase 2 (2–3 minggu)
- Split shipment board
- Bulk ETD update
- At-risk rule engine + notifications

### Phase 3
- Saved views, command palette, advanced analytics

---

## 10) Definition of Good UX (Acceptance)

- Supervisor bisa mengidentifikasi item risiko < 10 detik dari dashboard.
- Staff bisa update ETD 20 item < 2 menit via bulk/inline.
- Receiving item-by-item tanpa kehilangan konteks PO.
- Tidak ada “hidden status”; semua milestone terlihat di level item.

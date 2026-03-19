# DIGITALISASI MONITORING PO & RECEIVING MATERIAL LABEL

Aplikasi internal ERP-like untuk kontrol proses purchasing hingga receiving material label berdasarkan BRD operasional manufacturing.

## Fitur Utama (Sesuai BRD)
- Master Data: Supplier, Item, UOM, Warehouse, Plant
- Purchase Order: Draft, submit, approval, status transition, timeline status
- Shipment Tracking
- Goods Receiving berbasis PO + kalkulasi outstanding otomatis
- Traceability & monitoring
- Dashboard KPI operasional
- Outstanding report (filter-first)
- Audit trail
- Settings (termasuk over-receipt)

## Aturan Master Data (Mandatory)
- `supplier_code` dan `item_code` dipaksa:
  - trim whitespace
  - uppercase
  - unique (aplikasi + index DB)
- Tidak ada hard delete untuk data referensi transaksi; gunakan status aktif/nonaktif.
- Pesan validasi Bahasa Indonesia:
  - `Kode supplier sudah digunakan`
  - `Kode item sudah digunakan`

## Instalasi
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Konfigurasi Environment
```env
APP_TIMEZONE=Asia/Jakarta
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_monitoring_po
DB_USERNAME=root
DB_PASSWORD=
```

## Akun Demo
- Email: `admin@erp.local`
- Password: `password`

## Ringkasan Role
- Admin: full access, settings, audit, master data
- Purchasing: PO creation/edit, shipment update
- Purchasing Manager: approval & monitoring
- Warehouse: goods receiving
- Compliance/Viewer: monitoring read-only

## Alur PO & Receiving
1. Buat PO (`Draft`) dengan nomor otomatis.
2. Submit & approve PO.
3. PO dikirim ke supplier dan shipment dicatat.
4. Warehouse posting GR berbasis item outstanding.
5. Sistem update `received_qty`, `outstanding_qty`, serta status `Partial Received` atau `Closed`.

## Seed Demo
- 10 suppliers
- 50 items
- 20 sample PO (variasi status)
- sampai 10 sample goods receipt

## Testing
```bash
php artisan test
```

## Known Limitations
- Export Excel/PDF belum diaktifkan pada fase ini.
- Upload attachment dokumen belum final.
- Laporan lanjutan (aging, supplier OTP detail) masih basic.

## Future Improvements
- Export Excel/PDF untuk seluruh report.
- Multi-level approval dinamis.
- Attachment management + preview.
- Integrasi BC reference lebih mendalam.


## SOP Penggunaan Aplikasi (Ringkas)
### A. Alur Purchase Order sampai Receiving
1. **Buat PO** dari menu `Dokumen PO` (status awal `Draft`).
2. Buka detail PO, lalu ubah status sesuai urutan:
   - `Draft` → `Submitted`
   - `Submitted` → `Approved`
   - `Approved` → `Sent to Supplier`
   - `Sent to Supplier` → `Supplier Confirmed` / `Shipped`
3. Setelah status minimal `Sent to Supplier` / `Shipped`, masuk menu `Dokumen Receiving`.
4. Pilih PO (opsional filter), lalu posting **per item** pada tabel outstanding.
5. Sistem otomatis update received/outstanding dan status PO (`Partial Received` / `Closed`).

### B. Kenapa muncul "Transisi status PO tidak valid"?
Penyebab umum:
- Status tujuan tidak sesuai urutan dari status saat ini.
- PO sudah status final (`Cancelled` / `Closed`).
- User role tidak sesuai hak akses.

Solusi:
- Cek daftar transisi yang diizinkan di panel kanan detail PO.
- Ikuti urutan transisi yang ditampilkan sistem.
- Pastikan login dengan role yang memiliki hak update PO.

### C. SOP Receiving Item-by-Item
- Receiving dilakukan **satu item per transaksi** (bukan 1 PO full sekaligus).
- Gunakan tombol `Post Item` pada baris item yang datang.
- Isi tanggal terima, qty terima, dan nomor dokumen pendukung.
- Ulangi untuk item lain saat item benar-benar datang.


## Dokumen Konsep UI/UX Strategis
Lihat dokumen konsep lengkap: `UI_UX_PRODUCT_STRATEGY.md`.


## Cleanup Database (Tabel Tidak Dipakai)
Berdasarkan pemakaian kode saat ini, tabel berikut tidak lagi dipakai oleh flow aktif:
- `po_approvals`
- `supplier_confirmations`
- `shipment_items`

Migrasi pembersihan sudah disediakan di:
- `database/migrations/2026_03_19_130000_drop_unused_procurement_tables.php`

Jalankan:
```bash
php artisan migrate
```

Jika ingin rollback:
```bash
php artisan migrate:rollback --step=1
```

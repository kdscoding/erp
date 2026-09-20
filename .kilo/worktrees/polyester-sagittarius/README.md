# ERP Monitoring PO & Receiving

## A. Panduan Penggunaan Source Code (Scripts)

### Default database (sesuai request)
- DB: MySQL localhost
- Username: `root`
- Password: `laragon`

### 1) Setup + Repair gabungan (fresh / existing)
Script ini menggabungkan proses `setup` dan `continue`:
```bash
bash scripts/setup_erp.sh
```

Jika folder project sudah ada, script otomatis masuk mode repair/continue dan tetap merapikan konfigurasi + migration + seeder.

### 2) Lanjut Step 2 Master Data (review DB existing)
```bash
bash scripts/step2_master_data.sh erp-monitoring-po
```


### 3) Lanjut Step 3 Purchase Order
```bash
bash scripts/step3_purchase_order.sh erp-monitoring-po
```

### 4) Step 4 UI Template ERP Profesional
```bash
bash scripts/step4_ui_template.sh erp-monitoring-po
```

### 5) Step 5 Shipment + Goods Receiving
```bash
bash scripts/step5_shipment_receiving.sh erp-monitoring-po
```

### 6) Step 6 Traceability + Reports
```bash
bash scripts/step6_traceability_reports.sh erp-monitoring-po
```

### 7) Step 7 Master Pendukung (Item, Unit, Warehouse, Plant)
```bash
bash scripts/step7_master_supporting.sh erp-monitoring-po
```

### 8) Step 8 Final (Menu lengkap + Settings + Audit Trail)
```bash
bash scripts/step8_finalize_menu_audit_settings.sh erp-monitoring-po
```

### 9) Menjalankan aplikasi
```bash
cd erp-monitoring-po
php artisan serve
```

### Opsi override env jika perlu
```bash
APP_NAME=erp-monitoring-po \
DB_CONNECTION=mysql \
DB_HOST=127.0.0.1 \
DB_PORT=3306 \
DB_DATABASE=erp_monitoring_po \
DB_USERNAME=root \
DB_PASSWORD=laragon \
bash scripts/setup_erp.sh
```

---

## B. Tentang Aplikasi (Detail)

Nama aplikasi: **DIGITALISASI MONITORING PO & RECEIVING MATERIAL LABEL**.

Tujuan:
- memastikan semua penerimaan material label terhubung ke PO,
- memantau status PO harian,
- mempercepat traceability dari PO sampai receiving.

Status implementasi script saat ini:
1. Semua tabel inti ERP dibuat dulu secara urut dan ter-normalisasi (role, master, PO, shipment, receiving, audit, settings).
2. Seed default disiapkan (role, admin, unit, warehouse, plant, supplier, item).
3. Step 2 mengaktifkan modul Supplier dan validasi ulang kolom master data existing.
4. Step 3 menambahkan modul Purchase Order: PO number manual, item code diketik manual dengan dropdown saran, satuan otomatis muncul, qty diisi user, ETD opsional (dikonfirmasi supplier), dan seluruh id tetap auto-generate dari DB.
5. Step 4 menerapkan template UI ERP profesional berbasis adaptasi AdminLTE (sidebar/topbar/menu/kpi/table enterprise).
6. Step 5 menambahkan shipment + goods receiving dasar dengan update status PO otomatis.
7. Step 6 menambahkan traceability search dan laporan outstanding PO.
8. Step 7 menambahkan menu+modul master item, unit, warehouse, plant.
9. Step 8 finalisasi menu lengkap + settings + audit trail (menu ditulis ulang penuh agar pasti berubah).

Role target:
- Admin
- Purchasing
- Purchasing Manager
- Warehouse
- BC/Compliance
- Viewer

# Assumptions

1. Sistem diprioritaskan untuk operasi internal (intranet) dengan desktop-first UX.
2. Approval PO saat ini single-step (`Submitted` -> `Approved`), namun histori approval tetap disimpan.
3. Over-receipt default **tidak diizinkan**, dan dapat diaktifkan via setting `allow_over_receipt`.
4. Shipment dapat dibuat untuk PO berstatus `Approved`, `Sent to Supplier`, atau `Supplier Confirmed`.
5. Receiving diizinkan untuk PO yang sudah bergerak ke proses supplier/shipment dan bukan `Draft`, `Cancelled`, atau `Closed`.
6. Bahasa UI menggunakan Bahasa Indonesia, sedangkan naming database & kode menggunakan bahasa Inggris.
7. Semua nomor dokumen auto-generate dengan format harian:
   - PO: `PO-YYYYMMDD-####`
   - Shipment: `SHP-YYYYMMDD-####`
   - GR: `GR-YYYYMMDD-####`
8. Timezone aplikasi menggunakan `Asia/Jakarta` di level konfigurasi environment.

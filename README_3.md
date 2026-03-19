diff --git a/erp-monitoring-po/README.md b/erp-monitoring-po/README.md
index 5ad13779e0df8fca8c419cc16548c14801893757..5b2f2f01753b8589a8e6dbfd6521b0ec7c736af0 100644
--- a/erp-monitoring-po/README.md
+++ b/erp-monitoring-po/README.md
@@ -1,58 +1,81 @@
-<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>
+# DIGITALISASI MONITORING PO & RECEIVING MATERIAL LABEL
 
-<p align="center">
-<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
-<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
-<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
-<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
-</p>
+Aplikasi internal ERP-style berbasis Laravel untuk monitoring end-to-end Purchase Order (PO), shipment, dan goods receipt material label.
 
-## About Laravel
-
-Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:
-
-- [Simple, fast routing engine](https://laravel.com/docs/routing).
-- [Powerful dependency injection container](https://laravel.com/docs/container).
-- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-- [Robust background job processing](https://laravel.com/docs/queues).
-- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).
-
-Laravel is accessible, powerful, and provides tools required for large, robust applications.
-
-## Learning Laravel
-
-Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.
-
-In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.
-
-You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.
-
-## Agentic Development
-
-Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:
+## 1) Tech Stack
+- Laravel 12, PHP 8.3
+- MySQL 8
+- Blade + Bootstrap/AdminLTE style
+- Auth scaffold Laravel
+- Role-based access via middleware `role`
 
+## 2) Instalasi
 ```bash
-composer require laravel/boost --dev
-
-php artisan boost:install
+cp .env.example .env
+composer install
+php artisan key:generate
+php artisan migrate --seed
+php artisan serve
 ```
 
-Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.
-
-## Contributing
-
-Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).
-
-## Code of Conduct
-
-In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).
-
-## Security Vulnerabilities
+## 3) Konfigurasi Environment
+Set minimum variabel berikut di `.env`:
+```env
+APP_TIMEZONE=Asia/Jakarta
+DB_CONNECTION=mysql
+DB_HOST=127.0.0.1
+DB_PORT=3306
+DB_DATABASE=erp_monitoring_po
+DB_USERNAME=root
+DB_PASSWORD=
+```
 
-If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.
+## 4) Akun Demo
+- Email: `admin@erp.local`
+- Password: `password`
+
+## 5) Roles
+- Admin: akses penuh + settings + audit
+- Purchasing: kelola PO + shipment
+- Purchasing Manager: approval PO
+- Warehouse: goods receiving
+- BC/Compliance: traceability dan referensi data
+- Viewer: read-only dashboard/report
+
+## 6) Workflow PO
+1. Buat PO (status `Draft`) dengan auto/manual nomor.
+2. Submit -> Approve -> Sent to Supplier -> Supplier Confirmed -> Shipped.
+3. Saat receiving terjadi: status otomatis `Partial Received` atau `Closed`.
+4. Semua perubahan status disimpan ke `po_status_histories`.
+
+## 7) Workflow Receiving
+1. Pilih item PO outstanding.
+2. Input qty terima.
+3. Sistem validasi over-receipt (berdasarkan settings `allow_over_receipt`).
+4. Sistem update `received_qty` + `outstanding_qty` dan status PO.
+
+## 8) Modul Utama
+- Master data: supplier, item, unit, warehouse, plant
+- Purchase Order monitoring
+- Shipment tracking
+- Goods receiving
+- Traceability search
+- Outstanding PO report dengan filter
+- Dashboard KPI operasional
+- Audit trail
+
+## 9) Testing
+```bash
+php artisan test
+```
 
-## License
+## 10) Known Limitations
+- Export Excel/PDF belum diaktifkan pada fase ini.
+- Attachment upload UI belum final.
+- KPI lanjutan masih bisa diperkaya.
 
-The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
+## 11) Future Improvements
+- Integrasi notifikasi email approval.
+- Export report (Excel/PDF) per modul.
+- Fine-grained permission matrix per action.
+- API integration untuk supplier portal.

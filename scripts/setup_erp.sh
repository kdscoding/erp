#!/usr/bin/env bash
set -euo pipefail

APP_NAME="${APP_NAME:-erp-monitoring-po}"
DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-erp_monitoring_po}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-laragon}"

need_cmd() {
  command -v "$1" >/dev/null 2>&1 || { echo "[ERROR] command tidak ditemukan: $1"; exit 1; }
}

need_cmd php
need_cmd composer
need_cmd npm

set_env() {
  local key="$1"
  local value="$2"
  if grep -Eq "^[#[:space:]]*${key}=" .env; then
    sed -i "s|^[#[:space:]]*${key}=.*|${key}=${value}|" .env
  else
    echo "${key}=${value}" >> .env
  fi
}

if [ -d "$APP_NAME" ] && [ -f "$APP_NAME/artisan" ]; then
  echo "[INFO] Existing project detected: $APP_NAME (continue/repair mode)"
  cd "$APP_NAME"
else
  if [ -d "$APP_NAME" ]; then
    echo "[ERROR] folder $APP_NAME sudah ada tapi bukan project Laravel valid (artisan tidak ditemukan)."
    exit 1
  fi

  echo "[1/10] Create Laravel project..."
  composer create-project laravel/laravel "$APP_NAME"
  cd "$APP_NAME"

  echo "[2/10] Install Breeze..."
  composer require laravel/breeze --dev
  php artisan breeze:install blade
fi

echo "[3/10] Configure .env (MySQL localhost root/laragon)..."
cp .env.example .env || true
set_env "APP_NAME" "\"ERP Monitoring PO\""
set_env "APP_TIMEZONE" "Asia/Jakarta"
set_env "APP_LOCALE" "id"
set_env "DB_CONNECTION" "$DB_CONNECTION"
set_env "DB_HOST" "$DB_HOST"
set_env "DB_PORT" "$DB_PORT"
set_env "DB_DATABASE" "$DB_DATABASE"
set_env "DB_USERNAME" "$DB_USERNAME"
set_env "DB_PASSWORD" "$DB_PASSWORD"
php artisan key:generate --force
php artisan config:clear
php artisan cache:clear >/dev/null 2>&1 || true

echo "[4/10] Ensure dependencies..."
composer install
npm install
npm run build

echo "[5/10] Write ERP core migration (ordered + normalized)..."
mkdir -p database/migrations
cat > database/migrations/2026_01_01_000000_create_erp_core_tables.php <<'MIG'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->unique(['user_id', 'role_id']);
            });
        }

        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('supplier_code')->unique();
                $table->string('supplier_name');
                $table->text('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('contact_person')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->string('unit_code')->unique();
                $table->string('unit_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->string('warehouse_code')->unique();
                $table->string('warehouse_name');
                $table->string('location')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('plants')) {
            Schema::create('plants', function (Blueprint $table) {
                $table->id();
                $table->string('plant_code')->unique();
                $table->string('plant_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->string('item_code')->unique();
                $table->string('item_name');
                $table->string('category')->nullable();
                $table->text('specification')->nullable();
                $table->foreignId('unit_id')->nullable()->constrained('units');
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number')->unique();
                $table->date('po_date');
                $table->foreignId('supplier_id')->constrained('suppliers');
                $table->foreignId('plant_id')->nullable()->constrained('plants');
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
                $table->string('currency', 10)->default('IDR');
                $table->text('notes')->nullable();
                $table->string('status')->default('Draft');
                $table->timestamp('sent_to_supplier_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamp('approved_at')->nullable();
                $table->date('eta_date')->nullable();
                $table->string('bc_reference_no')->nullable();
                $table->date('bc_reference_date')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items');
                $table->decimal('ordered_qty', 14, 2);
                $table->decimal('received_qty', 14, 2)->default(0);
                $table->decimal('outstanding_qty', 14, 2)->default(0);
                $table->decimal('unit_price', 14, 2)->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('po_approvals')) {
            Schema::create('po_approvals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('approver_id')->constrained('users');
                $table->string('status');
                $table->text('note')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('supplier_confirmations')) {
            Schema::create('supplier_confirmations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->date('confirmation_date')->nullable();
                $table->date('eta_date')->nullable();
                $table->text('remark')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->string('shipment_number')->unique();
                $table->date('shipment_date')->nullable();
                $table->date('eta_date')->nullable();
                $table->string('delivery_note_number')->nullable();
                $table->text('supplier_remark')->nullable();
                $table->string('status')->default('Shipped');
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shipment_items')) {
            Schema::create('shipment_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
                $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items');
                $table->decimal('shipped_qty', 14, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('goods_receipts')) {
            Schema::create('goods_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('gr_number')->unique();
                $table->date('receipt_date');
                $table->foreignId('purchase_order_id')->constrained('purchase_orders');
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
                $table->foreignId('received_by')->nullable()->constrained('users');
                $table->string('document_number')->nullable();
                $table->text('remark')->nullable();
                $table->string('status')->default('Posted');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('goods_receipt_items')) {
            Schema::create('goods_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
                $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items');
                $table->foreignId('item_id')->constrained('items');
                $table->decimal('received_qty', 14, 2)->default(0);
                $table->decimal('accepted_qty', 14, 2)->default(0);
                $table->decimal('rejected_qty', 14, 2)->default(0);
                $table->text('remark')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('po_status_histories')) {
            Schema::create('po_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->foreignId('changed_by')->nullable()->constrained('users');
                $table->timestamp('changed_at')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('attachments')) {
            Schema::create('attachments', function (Blueprint $table) {
                $table->id();
                $table->string('module');
                $table->unsignedBigInteger('record_id');
                $table->string('file_path');
                $table->string('file_name');
                $table->foreignId('uploaded_by')->nullable()->constrained('users');
                $table->timestamps();
                $table->index(['module', 'record_id']);
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('module');
                $table->unsignedBigInteger('record_id')->nullable();
                $table->string('action');
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users');
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('po_status_histories');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('supplier_confirmations');
        Schema::dropIfExists('po_approvals');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('items');
        Schema::dropIfExists('plants');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('units');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
};
MIG

echo "[6/10] Write core model/controller/routes (profile route safe)..."
mkdir -p app/Models app/Http/Controllers resources/views

cat > app/Models/Role.php <<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'slug'];
}
PHP

cat > app/Models/User.php <<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }
}
PHP

cat > app/Http/Controllers/DashboardController.php <<'PHP'
<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard');
    }
}
PHP

cat > resources/views/dashboard.blade.php <<'BLADE'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard ERP PO & Receiving</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                Setup selesai. Lanjutkan Step 2 Master Data.
            </div>
        </div>
    </div>
</x-app-layout>
BLADE

cat > routes/web.php <<'PHP'
<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
PHP

echo "[7/10] Write seeders with defaults..."
mkdir -p database/seeders
cat > database/seeders/RolePermissionSeeder.php <<'PHP'
<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Purchasing', 'slug' => 'purchasing'],
            ['name' => 'Purchasing Manager', 'slug' => 'purchasing_manager'],
            ['name' => 'Warehouse', 'slug' => 'warehouse'],
            ['name' => 'BC Compliance', 'slug' => 'compliance'],
            ['name' => 'Viewer', 'slug' => 'viewer'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }

        $admin = User::firstOrCreate(['email' => 'admin@erp.local'], [
            'name' => 'Admin ERP',
            'password' => Hash::make('password'),
        ]);

        $adminRoleId = Role::where('slug', 'admin')->value('id');
        DB::table('user_roles')->updateOrInsert([
            'user_id' => $admin->id,
            'role_id' => $adminRoleId,
        ], []);
    }
}
PHP

cat > database/seeders/MasterDataSeeder.php <<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('units')->updateOrInsert(['unit_code' => 'PCS'], ['unit_name' => 'Pieces', 'updated_at' => now(), 'created_at' => now()]);
        DB::table('warehouses')->updateOrInsert(['warehouse_code' => 'WH-01'], ['warehouse_name' => 'Main Warehouse', 'location' => 'Plant A', 'updated_at' => now(), 'created_at' => now()]);
        DB::table('plants')->updateOrInsert(['plant_code' => 'PLT-A'], ['plant_name' => 'Plant A', 'updated_at' => now(), 'created_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'allow_over_receipt'], ['value' => '0', 'updated_at' => now(), 'created_at' => now()]);

        for ($i = 1; $i <= 10; $i++) {
            DB::table('suppliers')->updateOrInsert([
                'supplier_code' => sprintf('SUP%03d', $i),
            ], [
                'supplier_name' => 'Supplier '.$i,
                'email' => 'supplier'.$i.'@mail.com',
                'status' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }

        for ($i = 1; $i <= 50; $i++) {
            DB::table('items')->updateOrInsert([
                'item_code' => sprintf('ITM%04d', $i),
            ], [
                'item_name' => 'Label Material '.$i,
                'unit_id' => DB::table('units')->where('unit_code', 'PCS')->value('id'),
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }
}
PHP

cat > database/seeders/DatabaseSeeder.php <<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            MasterDataSeeder::class,
        ]);
    }
}
PHP

echo "[8/10] Register role middleware alias..."
php -r '$p="bootstrap/app.php"; $c=file_get_contents($p); if (strpos($c, "\"role\"") === false) { $target = "->withMiddleware(function (Middleware \\$middleware): void {"; $replacement = "->withMiddleware(function (Middleware \\$middleware): void {\n        \\$middleware->alias([\"role\" => \\App\\Http\\Middleware\\RoleMiddleware::class]);"; $c = str_replace($target, $replacement, $c); file_put_contents($p, $c); }'

mkdir -p app/Http/Middleware
cat > app/Http/Middleware/RoleMiddleware.php <<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles)
    {
        $allowed = explode('|', $roles);
        $user = $request->user();
        if (!$user || !$user->roles()->whereIn('slug', $allowed)->exists()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}
PHP

echo "[9/10] Rebuild DB fresh to avoid conflict..."
php artisan config:clear
php artisan migrate:fresh --seed

echo "[10/10] Done"
echo "App: $APP_NAME"
echo "DB: $DB_CONNECTION://$DB_USERNAME:$DB_PASSWORD@$DB_HOST:$DB_PORT/$DB_DATABASE"
echo "Login: admin@erp.local / password"
echo "Run: cd $APP_NAME && php artisan serve"

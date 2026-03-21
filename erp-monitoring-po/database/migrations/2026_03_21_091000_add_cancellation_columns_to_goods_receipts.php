<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('goods_receipts', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('status');
            }

            if (! Schema::hasColumn('goods_receipts', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('cancel_reason')->constrained('users');
            }

            if (! Schema::hasColumn('goods_receipts', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipts', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }

            if (Schema::hasColumn('goods_receipts', 'cancelled_by')) {
                $table->dropConstrainedForeignId('cancelled_by');
            }

            if (Schema::hasColumn('goods_receipts', 'cancel_reason')) {
                $table->dropColumn('cancel_reason');
            }
        });
    }
};

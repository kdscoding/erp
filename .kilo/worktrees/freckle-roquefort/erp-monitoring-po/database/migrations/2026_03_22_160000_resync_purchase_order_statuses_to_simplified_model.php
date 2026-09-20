<?php

use App\Support\ErpFlow;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('purchase_orders')
            ->orderBy('id')
            ->pluck('id')
            ->each(fn ($poId) => ErpFlow::refreshPoStatusByOutstanding((int) $poId));
    }

    public function down(): void
    {
        // No rollback because previous header statuses were derived and non-deterministic.
    }
};

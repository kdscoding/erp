<?php

use App\Support\DomainStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('purchase_orders') && ! Schema::hasColumn('purchase_orders', 'status_code')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->string('status_code', 100)->nullable()->after('status')->index();
            });
        }

        if (Schema::hasTable('purchase_order_items') && ! Schema::hasColumn('purchase_order_items', 'item_status_code')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->string('item_status_code', 100)->nullable()->after('item_status')->index();
            });
        }

        if (Schema::hasTable('shipments') && ! Schema::hasColumn('shipments', 'status_code')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->string('status_code', 100)->nullable()->after('status')->index();
            });
        }

        if (Schema::hasTable('goods_receipts') && ! Schema::hasColumn('goods_receipts', 'status_code')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->string('status_code', 100)->nullable()->after('status')->index();
            });
        }

        if (Schema::hasTable('po_status_histories')) {
            Schema::table('po_status_histories', function (Blueprint $table) {
                if (! Schema::hasColumn('po_status_histories', 'from_status_code')) {
                    $table->string('from_status_code', 100)->nullable()->after('from_status');
                }

                if (! Schema::hasColumn('po_status_histories', 'to_status_code')) {
                    $table->string('to_status_code', 100)->nullable()->after('to_status');
                }
            });
        }

        if (Schema::hasTable('document_terms') && ! Schema::hasColumn('document_terms', 'internal_code')) {
            Schema::table('document_terms', function (Blueprint $table) {
                $table->string('internal_code', 100)->nullable()->after('code');
            });
        }

        if (Schema::hasTable('purchase_orders')) {
            foreach (DomainStatus::pairs(DomainStatus::GROUP_PO_STATUS) as $internalCode => $legacyValue) {
                DB::table('purchase_orders')
                    ->where('status', $legacyValue)
                    ->update(['status_code' => $internalCode]);
            }
        }

        if (Schema::hasTable('purchase_order_items')) {
            foreach (DomainStatus::pairs(DomainStatus::GROUP_PO_ITEM_STATUS) as $internalCode => $legacyValue) {
                DB::table('purchase_order_items')
                    ->where('item_status', $legacyValue)
                    ->update(['item_status_code' => $internalCode]);
            }
        }

        if (Schema::hasTable('shipments')) {
            foreach (DomainStatus::pairs(DomainStatus::GROUP_SHIPMENT_STATUS) as $internalCode => $legacyValue) {
                DB::table('shipments')
                    ->where('status', $legacyValue)
                    ->update(['status_code' => $internalCode]);
            }
        }

        if (Schema::hasTable('goods_receipts')) {
            foreach (DomainStatus::pairs(DomainStatus::GROUP_GOODS_RECEIPT_STATUS) as $internalCode => $legacyValue) {
                DB::table('goods_receipts')
                    ->where('status', $legacyValue)
                    ->update(['status_code' => $internalCode]);
            }
        }

        if (Schema::hasTable('po_status_histories')) {
            foreach (DomainStatus::pairs(DomainStatus::GROUP_PO_STATUS) as $internalCode => $legacyValue) {
                DB::table('po_status_histories')
                    ->where('from_status', $legacyValue)
                    ->whereNull('from_status_code')
                    ->update(['from_status_code' => $internalCode]);

                DB::table('po_status_histories')
                    ->where('to_status', $legacyValue)
                    ->whereNull('to_status_code')
                    ->update(['to_status_code' => $internalCode]);
            }
        }

        if (Schema::hasTable('document_terms')) {
            foreach ([
                DomainStatus::GROUP_PO_STATUS,
                DomainStatus::GROUP_PO_ITEM_STATUS,
                DomainStatus::GROUP_SHIPMENT_STATUS,
                DomainStatus::GROUP_GOODS_RECEIPT_STATUS,
            ] as $groupKey) {
                foreach (DomainStatus::pairs($groupKey) as $internalCode => $legacyValue) {
                    DB::table('document_terms')
                        ->where('group_key', $groupKey)
                        ->where('code', $legacyValue)
                        ->update(['internal_code' => $internalCode]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_orders') && Schema::hasColumn('purchase_orders', 'status_code')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('status_code');
            });
        }

        if (Schema::hasTable('purchase_order_items') && Schema::hasColumn('purchase_order_items', 'item_status_code')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->dropColumn('item_status_code');
            });
        }

        if (Schema::hasTable('shipments') && Schema::hasColumn('shipments', 'status_code')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropColumn('status_code');
            });
        }

        if (Schema::hasTable('goods_receipts') && Schema::hasColumn('goods_receipts', 'status_code')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->dropColumn('status_code');
            });
        }

        if (Schema::hasTable('po_status_histories')) {
            Schema::table('po_status_histories', function (Blueprint $table) {
                if (Schema::hasColumn('po_status_histories', 'from_status_code')) {
                    $table->dropColumn('from_status_code');
                }

                if (Schema::hasColumn('po_status_histories', 'to_status_code')) {
                    $table->dropColumn('to_status_code');
                }
            });
        }

        if (Schema::hasTable('document_terms') && Schema::hasColumn('document_terms', 'internal_code')) {
            Schema::table('document_terms', function (Blueprint $table) {
                $table->dropColumn('internal_code');
            });
        }
    }
};

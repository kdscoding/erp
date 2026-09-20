<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'supplier_code')) {
                $table->string('supplier_code')->unique();
            }

            if (!Schema::hasColumn('suppliers', 'supplier_name')) {
                $table->string('supplier_name');
            }

            if (!Schema::hasColumn('suppliers', 'status')) {
                $table->boolean('status')->default(true);
            }

            if (!Schema::hasColumn('suppliers', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

            $columnsToRemove = [];
            if (Schema::hasColumn('suppliers', 'address')) {
                $columnsToRemove[] = 'address';
            }
            if (Schema::hasColumn('suppliers', 'phone')) {
                $columnsToRemove[] = 'phone';
            }
            if (Schema::hasColumn('suppliers', 'email')) {
                $columnsToRemove[] = 'email';
            }
            if (Schema::hasColumn('suppliers', 'contact_person')) {
                $columnsToRemove[] = 'contact_person';
            }

            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_person')->nullable();
        });
    }
};
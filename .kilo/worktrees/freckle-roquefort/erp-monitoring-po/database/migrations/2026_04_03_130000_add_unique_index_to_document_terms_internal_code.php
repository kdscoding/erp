<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (
            Schema::hasTable('document_terms')
            && Schema::hasColumn('document_terms', 'internal_code')
        ) {
            Schema::table('document_terms', function (Blueprint $table) {
                $table->unique(['group_key', 'internal_code'], 'document_terms_group_internal_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('document_terms')) {
            Schema::table('document_terms', function (Blueprint $table) {
                $table->dropUnique('document_terms_group_internal_unique');
            });
        }
    }
};

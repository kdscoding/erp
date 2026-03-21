<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('document_terms')) {
            Schema::create('document_terms', function (Blueprint $table) {
                $table->id();
                $table->string('group_key', 100);
                $table->string('code', 100);
                $table->string('label', 150);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['group_key', 'code']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_terms');
    }
};

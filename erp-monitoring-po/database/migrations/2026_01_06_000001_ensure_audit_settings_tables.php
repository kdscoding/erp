<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('module')->nullable();
                $table->unsignedBigInteger('record_id')->nullable();
                $table->string('action')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
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
        // no-op
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('document_terms', function (Blueprint $table) {
      if (! Schema::hasColumn('document_terms', 'badge_class')) {
        $table->string('badge_class', 120)->nullable()->after('label');
      }

      if (! Schema::hasColumn('document_terms', 'badge_text')) {
        $table->string('badge_text', 120)->nullable()->after('badge_class');
      }
    });
  }

  public function down(): void
  {
    Schema::table('document_terms', function (Blueprint $table) {
      if (Schema::hasColumn('document_terms', 'badge_text')) {
        $table->dropColumn('badge_text');
      }

      if (Schema::hasColumn('document_terms', 'badge_class')) {
        $table->dropColumn('badge_class');
      }
    });
  }
};

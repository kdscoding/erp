<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'nik')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nik')->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('password');
            });
        }

        $users = DB::table('users')->orderBy('id')->get(['id', 'nik']);
        foreach ($users as $user) {
            if (! $user->nik) {
                DB::table('users')->where('id', $user->id)->update([
                    'nik' => sprintf('EMP%06d', $user->id),
                ]);
            }
        }

        if (! Schema::hasTable('password_reset_requests')) {
            Schema::create('password_reset_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('request_note');
                $table->string('status', 30)->default('pending');
                $table->timestamp('requested_at')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users');
                $table->timestamp('processed_at')->nullable();
                $table->text('admin_note')->nullable();
                $table->timestamps();
            });
        }

        if (! $this->hasIndex('users', 'users_nik_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('nik');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_requests');

        if (Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }

        if (Schema::hasColumn('users', 'nik')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['nik']);
                $table->dropColumn('nik');
            });
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        return match ($driver) {
            'sqlite' => collect($connection->select("PRAGMA index_list('{$table}')"))->contains(fn ($row) => ($row->name ?? null) === $index),
            'mysql' => collect($connection->select("SHOW INDEX FROM {$table}"))->contains(fn ($row) => ($row->Key_name ?? null) === $index),
            default => false,
        };
    }
};

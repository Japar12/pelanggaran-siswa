<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students_and_users_tables', function (Blueprint $table) {
            Schema::table('students', function (Blueprint $table) {
            // relasi ke akun siswa
            if (!Schema::hasColumn('students', 'user_id')) {
                $table->foreignId('user_id')
                      ->nullable()
                      ->constrained('users')
                      ->onDelete('set null');
            }

            // relasi ke akun orang tua
            if (!Schema::hasColumn('students', 'parent_user_id')) {
                $table->foreignId('parent_user_id')
                      ->nullable()
                      ->constrained('users')
                      ->onDelete('set null');
            }
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students_and_users_tables', function (Blueprint $table) {
             if (Schema::hasColumn('students', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            if (Schema::hasColumn('students', 'parent_user_id')) {
                $table->dropConstrainedForeignId('parent_user_id');
            }
        });
    }
};

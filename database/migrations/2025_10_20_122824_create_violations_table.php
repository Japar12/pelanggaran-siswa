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
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
               $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('description'); // jenis pelanggaran
            $table->integer('points')->default(0); // poin pelanggaran
            $table->date('date')->default(now());
            $table->timestamps();
        });

         // Tambahkan total_points di students agar otomatis menjumlah pelanggaran
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'total_points')) {
                $table->integer('total_points')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations');

         Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('total_points');
        });
    }
};

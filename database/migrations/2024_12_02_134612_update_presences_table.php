<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdatePresencesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            // Menghapus kolom check_in dan check_out
            $table->dropColumn(['check_in', 'check_out']);

            // Menambahkan kolom baru
            $table->date('date')->default(DB::raw('CURRENT_DATE')); // Menambahkan kolom date
            $table->time('time')->default(DB::raw('CURRENT_TIME')); // Menambahkan kolom time
            $table->enum('status', ['hadir', 'izin', 'sakit'])->default('hadir'); // Status presensi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            // Menambah kembali kolom check_in dan check_out jika rollback
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();

            // Menghapus kolom baru
            $table->dropColumn(['date', 'time', 'status']);
        });
    }
}
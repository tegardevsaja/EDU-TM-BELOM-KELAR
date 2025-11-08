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
    Schema::table('certificate_elements', function (Blueprint $table) {
        // Pastikan hanya menambah kolom yang belum ada
        if (!Schema::hasColumn('certificate_elements', 'is_bold')) {
            $table->boolean('is_bold')->default(false)->after('alignment');
        }
        if (!Schema::hasColumn('certificate_elements', 'is_italic')) {
            $table->boolean('is_italic')->default(false)->after('is_bold');
        }
        if (!Schema::hasColumn('certificate_elements', 'is_underline')) {
            $table->boolean('is_underline')->default(false)->after('is_italic');
        }
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_elements', function (Blueprint $table) {
            // Kembalikan ke tipe data semula
            $table->integer('x_position')->change();
            $table->integer('y_position')->change();
            
            // Hapus kolom yang ditambahkan
            $table->dropColumn(['is_bold', 'content']);
        });
    }
};  
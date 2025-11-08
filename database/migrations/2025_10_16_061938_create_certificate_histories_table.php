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
        Schema::create('certificate_histories', function (Blueprint $table) {
            $table->id();
             $table->foreignId('template_id')
                  ->constrained('certificate_templates')
                  ->onDelete('cascade');
            $table->foreignId('kelas_id')
                  ->constrained('kelas')
                  ->onDelete('cascade'); // ambil dari table kelas
            $table->integer('jumlah_siswa');
            $table->string('jenis_file'); // pdf / png / jpg / zip
            $table->foreignId('generated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete(); // user yang buat
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_histories');
    }
};

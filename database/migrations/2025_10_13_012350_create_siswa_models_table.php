<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nis', 20);
            $table->string('nama', 100);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 50);
            $table->date('tanggal_lahir');
            $table->string('agama', 50);
            $table->string('nama_orang_tua', 100);
            $table->text('alamat_orang_tua');
            $table->string('no_hp_orang_tua', 20);
            $table->string('asal_sekolah', 100);
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('jurusan_id');
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->year('tahun_masuk');
            $table->enum('status', ['Aktif', 'Alumni', 'Nonaktif']);
            $table->timestamps();

            // Foreign keys
            $table->foreign('kelas_id')
                  ->references('id')
                  ->on('kelas')
                  ->onDelete('cascade');

            $table->foreign('jurusan_id')
                  ->references('id')
                  ->on('jurusans')
                  ->onDelete('cascade');

            $table->foreign('tahun_ajaran_id')
                  ->references('id')
                  ->on('tahun_ajarans')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};

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
        Schema::create('certificate_student_grades', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->foreignId('certificate_student_id')
                  ->constrained('certificate_students')
                  ->onDelete('cascade')
                  ->comment('ID sertifikat siswa');
            
            // Grade Data
            $table->string('mata_pelajaran', 100)
                  ->comment('Nama mata pelajaran/komponen penilaian');
            
            $table->decimal('nilai', 5, 2)
                  ->nullable()
                  ->comment('Nilai angka (0.00 - 100.00)');
            
            $table->string('grade', 5)
                  ->nullable()
                  ->comment('Grade huruf: A, B, C, D, E');
            
            $table->string('predikat', 50)
                  ->nullable()
                  ->comment('Predikat: Sangat Baik, Baik, Cukup, Kurang');
            
            $table->text('keterangan')
                  ->nullable()
                  ->comment('Keterangan tambahan');
            
            $table->integer('order')
                  ->default(0)
                  ->comment('Urutan tampil di sertifikat');
            
            $table->timestamps();

            // Index
            $table->index(['certificate_student_id', 'order']);
        });
        
        echo "✅ certificate_student_grades table created!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_student_grades');
    }
};
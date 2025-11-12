<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificate_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();
            $table->boolean('granted')->default(false);
            $table->string('reason')->nullable();
            $table->foreignId('granted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['siswa_id','tahun_ajaran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_overrides');
    }
};

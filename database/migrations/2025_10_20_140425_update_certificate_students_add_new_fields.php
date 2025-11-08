<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('certificate_students', function (Blueprint $table) {
            // STEP 1: File paths baru (depan & belakang)
            $table->string('front_file_path', 500)
                  ->nullable()
                  ->after('siswa_id')
                  ->comment('Path file sertifikat sisi depan');
            
            $table->string('back_file_path', 500)
                  ->nullable()
                  ->after('front_file_path')
                  ->comment('Path file sertifikat sisi belakang (nilai)');
            
            // STEP 2: Eligibility System
            $table->enum('status', ['eligible', 'blocked', 'manual_override'])
                  ->default('eligible')
                  ->after('tanggal_sertifikat')
                  ->comment('Status kelayakan siswa mendapat sertifikat');
            
            $table->boolean('is_eligible')
                  ->default(true)
                  ->after('status')
                  ->comment('Flag eligible/tidak eligible');
            
            $table->text('blocking_reason')
                  ->nullable()
                  ->after('is_eligible')
                  ->comment('Alasan tidak eligible (misal: alfa > 3x)');
            
            // STEP 3: Manual Override System
            $table->foreignId('manual_override_by')
                  ->nullable()
                  ->after('blocking_reason')
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('ID admin yang melakukan override manual');
            
            $table->text('manual_override_note')
                  ->nullable()
                  ->after('manual_override_by')
                  ->comment('Catatan mengapa di-override');
            
            $table->timestamp('manual_override_at')
                  ->nullable()
                  ->after('manual_override_note')
                  ->comment('Waktu override dilakukan');
            
            // STEP 4: Soft Deletes
            $table->softDeletes()->after('updated_at');
            
            // STEP 5: Index untuk performa
            $table->index(['history_id', 'is_eligible']);
            $table->index('status');
        });

        // STEP 6: Migrate data dari file_path ke front_file_path
        $migrated = DB::statement('
            UPDATE certificate_students 
            SET front_file_path = file_path,
                updated_at = NOW()
            WHERE file_path IS NOT NULL AND file_path != ""
        ');

        // STEP 7: Set semua data existing jadi eligible
        DB::table('certificate_students')
            ->update([
                'is_eligible' => true,
                'status' => 'eligible',
                'updated_at' => now()
            ]);
        
        echo "✅ certificate_students updated successfully!\n";
        echo "   - Migrated file_path → front_file_path\n";
        echo "   - Set all existing records as eligible\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: kembalikan data ke file_path
        DB::statement('
            UPDATE certificate_students 
            SET file_path = front_file_path 
            WHERE front_file_path IS NOT NULL
        ');

        Schema::table('certificate_students', function (Blueprint $table) {
            $table->dropIndex(['history_id', 'is_eligible']);
            $table->dropIndex(['status']);
            $table->dropForeign(['manual_override_by']);
            $table->dropColumn([
                'front_file_path',
                'back_file_path',
                'status',
                'is_eligible',
                'blocking_reason',
                'manual_override_by',
                'manual_override_note',
                'manual_override_at'
            ]);
            $table->dropSoftDeletes();
        });
    }
};
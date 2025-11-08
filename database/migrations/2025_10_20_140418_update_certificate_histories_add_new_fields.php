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
        // STEP 1: Drop & re-add generated_by dengan posisi yang benar
        Schema::table('certificate_histories', function (Blueprint $table) {
            $table->dropForeign(['generated_by']);
        });

        // STEP 2: Tambah kolom baru
        Schema::table('certificate_histories', function (Blueprint $table) {
            // Foreign key untuk grade template (nullable karena optional)
            $table->foreignId('grade_template_id')
                  ->nullable()
                  ->after('template_id')
                  ->comment('ID template untuk halaman nilai/belakang');
            
            // Status processing
            $table->enum('status', ['draft', 'processing', 'completed', 'failed'])
                  ->default('draft')
                  ->after('jenis_file')
                  ->comment('Status pembuatan sertifikat');
            
            // Format output detail
            $table->string('format_output', 10)
                  ->nullable()
                  ->after('status')
                  ->comment('Format file output: pdf, png, jpg, jpeg');
            
            // Compression flag
            $table->boolean('is_compressed')
                  ->default(false)
                  ->after('format_output')
                  ->comment('Apakah hasil di-zip');
            
            // Path file zip
            $table->string('zip_path', 500)
                  ->nullable()
                  ->after('is_compressed')
                  ->comment('Path file zip jika is_compressed = true');
            
            // Notes
            $table->text('notes')
                  ->nullable()
                  ->after('zip_path')
                  ->comment('Catatan tambahan');
            
            // Soft deletes
            $table->softDeletes()->after('updated_at');
            
            // Index untuk performa query
            $table->index(['status', 'created_at']);
            $table->index('is_compressed');
        });

        // STEP 3: Migrate data dari jenis_file ke format_output & is_compressed
        $histories = DB::table('certificate_histories')->get();
        
        foreach ($histories as $record) {
            $jenisFile = strtolower(trim($record->jenis_file));
            $isZip = in_array($jenisFile, ['zip', 'rar', '7z']);
            
            DB::table('certificate_histories')
                ->where('id', $record->id)
                ->update([
                    'format_output' => $isZip ? 'png' : $jenisFile,
                    'is_compressed' => $isZip,
                    'status' => 'completed', // data lama dianggap selesai
                    'updated_at' => now()
                ]);
        }
        
        echo "✅ certificate_histories updated successfully! Migrated " . count($histories) . " records.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: kembalikan data ke jenis_file
        $histories = DB::table('certificate_histories')->get();
        
        foreach ($histories as $record) {
            $jenisFile = $record->is_compressed ? 'zip' : ($record->format_output ?? 'pdf');
            
            DB::table('certificate_histories')
                ->where('id', $record->id)
                ->update(['jenis_file' => $jenisFile]);
        }

        Schema::table('certificate_histories', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['is_compressed']);
            $table->dropColumn([
                'grade_template_id',
                'status',
                'format_output',
                'is_compressed',
                'zip_path',
                'notes'
            ]);
            $table->dropSoftDeletes();
        });
    }
};
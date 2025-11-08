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
        // STEP 1: Migrate data dari background_image ke file_background_path
        DB::statement('
            UPDATE certificate_templates 
            SET file_background_path = background_image 
            WHERE file_background_path IS NULL AND background_image IS NOT NULL
        ');

        // STEP 2: Tambah kolom baru
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->boolean('is_double_sided')
                  ->default(false)
                  ->after('height')
                  ->comment('Apakah sertifikat 2 sisi (depan-belakang)');
            
            $table->enum('orientation', ['portrait', 'landscape'])
                  ->default('portrait')
                  ->after('is_double_sided')
                  ->comment('Orientasi template: portrait atau landscape');
            
            $table->text('description')
                  ->nullable()
                  ->after('orientation')
                  ->comment('Deskripsi template');
            
            $table->boolean('is_active')
                  ->default(true)
                  ->after('description')
                  ->comment('Status aktif template');
            
            $table->softDeletes()->after('updated_at');
        });

        // STEP 3: Set semua template existing jadi active
        DB::table('certificate_templates')->update([
            'is_active' => true,
            'updated_at' => now()
        ]);
        
        // Log info
        echo "✅ certificate_templates updated successfully!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->dropColumn([
                'is_double_sided', 
                'orientation', 
                'description',
                'is_active'
            ]);
            $table->dropSoftDeletes();
        });
    }
};
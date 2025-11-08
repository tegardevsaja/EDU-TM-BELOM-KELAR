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
            // STEP 1: Field Type & Configuration
            $table->enum('field_type', ['static', 'dynamic', 'date'])
                  ->default('dynamic')
                  ->after('field_name')
                  ->comment('Tipe: static (manual), dynamic (dari DB), date (tanggal)');
            
            $table->string('field_label', 100)
                  ->nullable()
                  ->after('field_type')
                  ->comment('Label user-friendly (contoh: "Nama Siswa")');
            
            $table->string('field_source', 100)
                  ->nullable()
                  ->after('field_label')
                  ->comment('Nama kolom database (contoh: "siswas.nama")');
            
            // STEP 2: Date Configuration
            $table->string('date_format', 50)
                  ->nullable()
                  ->after('field_source')
                  ->comment('Format tanggal: d F Y, d-m-Y, l d F Y, dll');
            
            $table->boolean('is_auto_date')
                  ->default(false)
                  ->after('date_format')
                  ->comment('Auto isi dengan tanggal saat cetak');
            
            $table->text('default_value')
                  ->nullable()
                  ->after('is_auto_date')
                  ->comment('Nilai default untuk static field');
            
            // STEP 3: Display Order & Validation
            $table->integer('order')
                  ->default(0)
                  ->after('alignment')
                  ->comment('Urutan render (0, 1, 2, ...)');
            
            $table->boolean('is_required')
                  ->default(true)
                  ->after('order')
                  ->comment('Field wajib diisi atau tidak');
            
            // STEP 4: Text Styling
            $table->boolean('is_bold')
                  ->default(false)
                  ->after('is_required')
                  ->comment('Text bold');
            
            $table->boolean('is_italic')
                  ->default(false)
                  ->after('is_bold')
                  ->comment('Text italic');
            
            $table->boolean('is_underline')
                  ->default(false)
                  ->after('is_italic')
                  ->comment('Text underline');
            
            // STEP 5: Layout Configuration
            $table->integer('width')
                  ->nullable()
                  ->after('is_underline')
                  ->comment('Lebar area text (pixel)');
            
            $table->integer('max_length')
                  ->nullable()
                  ->after('width')
                  ->comment('Max karakter');
            
            $table->string('text_transform', 20)
                  ->nullable()
                  ->after('max_length')
                  ->comment('uppercase, lowercase, capitalize, none');
            
            // STEP 6: Soft Delete
            $table->softDeletes()->after('updated_at');
            
            // STEP 7: Index
            $table->index(['template_id', 'order'], 'idx_template_order');
            $table->index('field_type', 'idx_field_type');
        });
        
        echo "✅ certificate_elements enhanced successfully!\n";
        echo "   - Added field configuration columns\n";
        echo "   - Added styling options\n";
        echo "   - Added indexes for performance\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_elements', function (Blueprint $table) {
            $table->dropIndex('idx_template_order');
            $table->dropIndex('idx_field_type');
            
            $table->dropColumn([
                'field_type',
                'field_label',
                'field_source',
                'date_format',
                'is_auto_date',
                'default_value',
                'order',
                'is_required',
                'is_bold',
                'is_italic',
                'is_underline',
                'width',
                'max_length',
                'text_transform'
            ]);
            
            $table->dropSoftDeletes();
        });
    }
};
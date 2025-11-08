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
        Schema::create('grade_template_elements', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->foreignId('grade_template_id')
                  ->constrained('grade_templates')
                  ->onDelete('cascade')
                  ->comment('ID grade template');
            
            // Field Info
            $table->string('field_name', 100)
                  ->comment('Nama field: nama_mapel, nilai, grade, keterangan');
            
            $table->string('field_label', 100)
                  ->nullable()
                  ->comment('Label tampilan');
            
            $table->enum('field_type', ['static', 'dynamic', 'header'])
                  ->default('dynamic')
                  ->comment('Tipe field');
            
            $table->string('field_source', 100)
                  ->nullable()
                  ->comment('Source kolom database');
            
            // Position
            $table->integer('x_position')
                  ->comment('Posisi X (pixel dari kiri)');
            
            $table->integer('y_position')
                  ->comment('Posisi Y (pixel dari atas)');
            
            // Styling
            $table->integer('font_size')
                  ->default(12)
                  ->comment('Ukuran font');
            
            $table->string('font_family', 50)
                  ->default('Arial')
                  ->comment('Jenis font');
            
            $table->string('color', 20)
                  ->default('#000000')
                  ->comment('Warna text (hex)');
            
            $table->enum('alignment', ['left', 'center', 'right'])
                  ->default('left')
                  ->comment('Alignment text');
            
            $table->integer('width')
                  ->nullable()
                  ->comment('Lebar area text');
            
            $table->integer('order')
                  ->default(0)
                  ->comment('Urutan render');
            
            // Text Style
            $table->boolean('is_bold')->default(false);
            $table->boolean('is_italic')->default(false);
            $table->boolean('is_underline')->default(false);
            
            $table->timestamps();

            // Index
            $table->index(['grade_template_id', 'order']);
        });
        
        echo "✅ grade_template_elements table created successfully!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_template_elements');
    }
};
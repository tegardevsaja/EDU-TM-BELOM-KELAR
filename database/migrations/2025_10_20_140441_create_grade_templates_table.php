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
        Schema::create('grade_templates', function (Blueprint $table) {
            $table->id();
            
            $table->string('nama_template', 100)
                  ->comment('Nama template halaman nilai/belakang');
            
            $table->string('background_image', 500)
                  ->comment('Path file background image');
            
            $table->integer('width')
                  ->nullable()
                  ->comment('Lebar template (pixel)');
            
            $table->integer('height')
                  ->nullable()
                  ->comment('Tinggi template (pixel)');
            
            $table->enum('orientation', ['portrait', 'landscape'])
                  ->default('portrait')
                  ->comment('Orientasi template');
            
            $table->text('description')
                  ->nullable()
                  ->comment('Deskripsi template');
            
            $table->boolean('is_active')
                  ->default(true)
                  ->comment('Status aktif');
            
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('is_active');
            $table->index('nama_template');
        });
        
        echo "✅ grade_templates table created successfully!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_templates');
    }
};
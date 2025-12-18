<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('left_label', 100)->nullable();
            $table->string('left_name', 150)->nullable();
            $table->string('left_org', 150)->nullable();
            $table->string('right_label', 100)->nullable();
            $table->string('right_name', 150)->nullable();
            $table->string('right_org', 150)->nullable();
            $table->string('city', 150)->nullable();
            $table->string('left_signature_path')->nullable();
            $table->string('right_signature_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_signatures');
    }
};

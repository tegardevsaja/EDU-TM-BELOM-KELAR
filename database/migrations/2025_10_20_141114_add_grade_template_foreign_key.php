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
        Schema::table('certificate_histories', function (Blueprint $table) {
            // Add foreign key constraint untuk grade_template_id
            $table->foreign('grade_template_id')
                  ->references('id')
                  ->on('grade_templates')
                  ->nullOnDelete();
        });
        
        echo "✅ Foreign key grade_template_id added to certificate_histories!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_histories', function (Blueprint $table) {
            $table->dropForeign(['grade_template_id']);
        });
    }
};
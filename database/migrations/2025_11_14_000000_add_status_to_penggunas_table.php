<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penggunas', function (Blueprint $table) {
            if (!Schema::hasColumn('penggunas', 'status')) {
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('nik');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penggunas', function (Blueprint $table) {
            if (Schema::hasColumn('penggunas', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

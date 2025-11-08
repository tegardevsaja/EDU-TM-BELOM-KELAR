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
        Schema::create('certificate_eligibility_rules', function (Blueprint $table) {
            $table->id();
            
            $table->string('rule_name', 100)
                  ->comment('Nama aturan eligibility');
            
            // Blocked Absence Rules (Alfa, Bolos, Tanpa Keterangan)
            $table->integer('max_blocked_absence')
                  ->default(3)
                  ->comment('Max ketidakhadiran yang diblokir (alfa/bolos)');
            
            $table->json('blocked_absence_types')
                  ->nullable()
                  ->comment('Array tipe yang diblokir: ["alfa","bolos","tanpa_keterangan"]');
            
            // Allowed Absence Rules (Sakit, Izin)
            $table->integer('max_allowed_absence')
                  ->default(5)
                  ->comment('Max ketidakhadiran yang diizinkan (sakit/izin)');
            
            $table->json('allowed_absence_types')
                  ->nullable()
                  ->comment('Array tipe yang diizinkan: ["sakit","izin"]');
            
            // Attendance Percentage
            $table->decimal('min_attendance_percentage', 5, 2)
                  ->nullable()
                  ->comment('Min persentase kehadiran (contoh: 75.00)');
            
            $table->text('description')
                  ->nullable()
                  ->comment('Deskripsi aturan');
            
            $table->boolean('is_active')
                  ->default(true)
                  ->comment('Status aktif aturan');
            
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('is_active');
        });

        // Insert Default Rule
        DB::table('certificate_eligibility_rules')->insert([
            'rule_name' => 'Aturan Eligibility Default',
            'max_blocked_absence' => 3,
            'blocked_absence_types' => json_encode(['alfa', 'bolos', 'tanpa_keterangan']),
            'max_allowed_absence' => 5,
            'allowed_absence_types' => json_encode(['sakit', 'izin']),
            'min_attendance_percentage' => 75.00,
            'description' => 'Aturan default: Maksimal 3x alfa/bolos atau 5x sakit/izin. Minimal kehadiran 75%.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "✅ certificate_eligibility_rules table created!\n";
        echo "   - Default rule inserted\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_eligibility_rules');
    }
};
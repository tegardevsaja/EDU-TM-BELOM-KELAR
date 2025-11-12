<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->nullable();
            $table->string('action'); // route name or custom action
            $table->string('method', 10)->nullable();
            $table->string('model_type')->nullable();
            $table->string('model_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};

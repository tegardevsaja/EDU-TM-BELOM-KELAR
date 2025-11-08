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
            Schema::create('certificate_elements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->constrained('certificate_templates')->onDelete('cascade');
                $table->string('field_name');
                $table->integer('x_position');
                $table->integer('y_position');
                $table->integer('font_size')->nullable();
                $table->string('font_family')->nullable();
                $table->string('color')->nullable();
                $table->string('alignment')->default('left');
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('certificate_elements');
        }
    };

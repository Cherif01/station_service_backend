
<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
    public function up(): void
    {
        Schema::create('jaugeage_cuves', function (Blueprint $table) {

            $table->id();

            $table->foreignId('id_cuve')
                ->constrained('cuves')
                ->cascadeOnDelete();

            $table->decimal('hauteur', 8, 2);

            $table->decimal('volume_mesure', 12, 2);

            $table->text('commentaire')->nullable();

            $table->boolean('status')->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('modify_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jaugeage_cuves');
    }
};
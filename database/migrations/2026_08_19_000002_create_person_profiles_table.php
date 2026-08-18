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
        Schema::create('person_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->unique()->constrained('people')->onDelete('cascade');
            $table->json('targets')->nullable(); // [{"title": "++PZ", "done": true}, {"title": "++01", "done": false}]
            $table->string('catatan_akademik')->nullable();
            $table->string('catatan_keuangan')->nullable();
            $table->tinyInteger('skala_sales')->default(1); // 1-5
            $table->tinyInteger('skala_katim')->default(1); // 1-5
            $table->tinyInteger('skala_keaktifan')->default(1); // 1-5
            $table->tinyInteger('skala_prioritas')->default(1); // 1-5
            $table->text('cara_aktif')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_profiles');
    }
};

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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->text('description');
            $table->date('activity_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('category');
            $table->index('activity_date');
        });

        Schema::create('activity_pic', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->primary(['activity_id', 'person_id']);
        });

        Schema::create('activity_participant', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->primary(['activity_id', 'person_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_participant');
        Schema::dropIfExists('activity_pic');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('people');
    }
};

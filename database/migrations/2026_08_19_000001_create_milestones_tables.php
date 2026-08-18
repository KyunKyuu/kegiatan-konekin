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
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('milestones')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('target_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->string('color')->default('theme-purple');
            $table->integer('order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('parent_id');
            $table->index('target_date');
            $table->index('status');
        });

        Schema::create('milestone_person', function (Blueprint $table) {
            $table->foreignId('milestone_id')->constrained('milestones')->onDelete('cascade');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('role')->nullable();
            $table->timestamps();
            $table->primary(['milestone_id', 'person_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestone_person');
        Schema::dropIfExists('milestones');
    }
};

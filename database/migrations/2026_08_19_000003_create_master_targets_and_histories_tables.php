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
        // 1. Master Targets Table
        Schema::create('master_targets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Person Targets (Assigned targets with checklist status)
        Schema::create('person_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->foreignId('master_target_id')->nullable()->constrained('master_targets')->onDelete('set null');
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('person_id');
            $table->index('is_completed');
        });

        // 3. Person Scale Change History
        Schema::create('person_scale_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('scale_type'); // 'sales', 'katim', 'keaktifan', 'prioritas'
            $table->tinyInteger('old_value');
            $table->tinyInteger('new_value');
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('person_id');
            $table->index('scale_type');
        });

        // 4. Person Notes (Multi-catatan)
        Schema::create('person_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('category')->default('Umum'); // 'Akademik', 'Keuangan', 'Perilaku', 'Kinerja', etc.
            $table->string('status_label')->nullable(); // 'Aktif', 'Lunas', 'Baik', etc.
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('person_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_notes');
        Schema::dropIfExists('person_scale_histories');
        Schema::dropIfExists('person_targets');
        Schema::dropIfExists('master_targets');
    }
};

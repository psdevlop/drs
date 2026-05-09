<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluee_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['self', 'peer', 'manager']);
            $table->string('intern_role')->nullable();
            $table->json('ratings')->nullable();
            $table->json('responses')->nullable();
            $table->string('frequency')->nullable();
            $table->unsignedTinyInteger('self_score')->nullable();
            $table->string('rehire_recommendation')->nullable();
            $table->string('salary_increase')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['evaluator_id', 'evaluee_id', 'type']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};

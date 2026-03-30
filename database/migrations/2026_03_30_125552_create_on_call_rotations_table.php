<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('on_call_rotations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('cycle_type', ['daily', 'weekly'])->default('daily');
            $table->integer('cycle_length')->default(1); // e.g. 1 = every day/week, 2 = every other
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null = indefinite
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('on_call_rotation_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rotation_id')->constrained('on_call_rotations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('order')->default(0); // position in rotation
            $table->timestamps();
            $table->unique(['rotation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('on_call_rotation_users');
        Schema::dropIfExists('on_call_rotations');
    }
};

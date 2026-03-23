<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('on_calls', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('on_call_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('on_call_id')->constrained('on_calls')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['on_call_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('on_call_users');
        Schema::dropIfExists('on_calls');
    }
};

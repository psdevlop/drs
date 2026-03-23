<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['domain', 'hosting', 'cdn', 'website'])->default('domain');
            $table->string('provider')->nullable();
            $table->string('registrant')->nullable();
            $table->string('registrant_id')->nullable();
            $table->date('registration_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->enum('status', ['active', 'expired', 'pending', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('admin_id')->nullable()->after('url');
            $table->string('admin_password')->nullable()->after('admin_id');
            $table->string('test_id')->nullable()->after('admin_password');
            $table->string('test_password')->nullable()->after('test_id');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['admin_id', 'admin_password', 'test_id', 'test_password']);
        });
    }
};

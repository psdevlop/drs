<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('on_calls', function (Blueprint $table) {
            $table->foreignId('pic_user_id')->nullable()->after('notes')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('on_calls', function (Blueprint $table) {
            $table->dropForeign(['pic_user_id']);
            $table->dropColumn('pic_user_id');
        });
    }
};

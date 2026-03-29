<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['task_id', 'user_id']);
        });

        // Migrate existing assigned_to data to pivot table
        $tasks = DB::table('tasks')->whereNotNull('assigned_to')->get();
        foreach ($tasks as $task) {
            DB::table('task_user')->insert([
                'task_id' => $task->id,
                'user_id' => $task->assigned_to,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });

        $assignments = DB::table('task_user')->get();
        foreach ($assignments as $assignment) {
            DB::table('tasks')->where('id', $assignment->task_id)->update(['assigned_to' => $assignment->user_id]);
        }

        Schema::dropIfExists('task_user');
    }
};

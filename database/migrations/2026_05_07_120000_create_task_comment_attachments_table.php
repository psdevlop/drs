<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_comment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_comment_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->timestamps();
        });

        $existing = DB::table('task_comments')
            ->whereNotNull('attachment_path')
            ->get(['id', 'attachment_path', 'attachment_name', 'created_at', 'updated_at']);

        foreach ($existing as $row) {
            DB::table('task_comment_attachments')->insert([
                'task_comment_id' => $row->id,
                'file_path' => $row->attachment_path,
                'original_name' => $row->attachment_name ?: basename($row->attachment_path),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::table('task_comments', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name']);
        });
    }

    public function down(): void
    {
        Schema::table('task_comments', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('body');
            $table->string('attachment_name')->nullable()->after('attachment_path');
        });

        $rows = DB::table('task_comment_attachments')
            ->orderBy('id')
            ->get(['task_comment_id', 'file_path', 'original_name']);

        $seen = [];
        foreach ($rows as $row) {
            if (isset($seen[$row->task_comment_id])) {
                continue;
            }
            DB::table('task_comments')
                ->where('id', $row->task_comment_id)
                ->update([
                    'attachment_path' => $row->file_path,
                    'attachment_name' => $row->original_name,
                ]);
            $seen[$row->task_comment_id] = true;
        }

        Schema::dropIfExists('task_comment_attachments');
    }
};

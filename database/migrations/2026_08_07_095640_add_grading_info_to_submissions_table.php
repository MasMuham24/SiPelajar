<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {

            $table->foreignId('graded_by')
                ->nullable()
                ->after('feedback')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('graded_at')
                ->nullable()
                ->after('graded_by');

        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {

            $table->dropForeign(['graded_by']);

            $table->dropColumn([
                'graded_by',
                'graded_at',
            ]);

        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('student_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->after('teacher_id')->constrained('classrooms')->cascadeOnDelete();
            $table->time('check_out')->nullable()->after('check_in');
            $table->integer('late_minutes')->nullable()->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['classroom_id']);
            $table->dropColumn(['teacher_id', 'classroom_id', 'check_out', 'late_minutes']);
        });
    }
};

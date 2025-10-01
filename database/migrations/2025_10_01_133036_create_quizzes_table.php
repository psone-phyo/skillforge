<?php

use App\Enums\Table;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Table::QUIZ, function (Blueprint $table) {
            $table->id();
            $table->integer('course_id');
            $table->integer('instructor_id');
            $table->string('title');
            $table->string('mm_title')->nullable();
            $table->integer('passing_score')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::QUIZ);
    }
};

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
        Schema::create(Table::QUIZ_QUESTION_OPTION, function (Blueprint $table) {
            $table->id();
            $table->integer('quiz_question_id');
            $table->text('answer');
            $table->integer('sort')->default(1);
            $table->boolean('is_correct')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::QUIZ_QUESTION_OPTION);
    }
};

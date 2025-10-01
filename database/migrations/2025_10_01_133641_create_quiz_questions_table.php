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
        Schema::create(Table::QUIZ_QUESTION, function (Blueprint $table) {
            $table->id();
            $table->integer('quiz_id');
            $table->text('question');
            $table->integer('sort')->nullable();
            $table->integer('point')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::QUIZ_QUESTION);
    }
};

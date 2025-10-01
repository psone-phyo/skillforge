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
        Schema::create(Table::LESSON, function (Blueprint $table) {
            $table->id();
            $table->integer('course_id');
            $table->string('title')->nullable();
            $table->string('mm_title')->nullable();
            $table->longText('description')->nullable();
            $table->longText('mm_description')->nullable();
            $table->text('video_url');
            $table->boolean('is_locked')->default(0);
            $table->integer('sort');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::LESSON);
    }
};

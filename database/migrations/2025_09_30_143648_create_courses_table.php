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
        Schema::create(Table::COURSE, function (Blueprint $table) {
            $table->id();
            $table->integer('instructor_id');
            $table->string('course_code');
            $table->string('title');
            $table->string('mm_title');
            $table->string('slug');
            $table->string('sub_title')->nullable();
            $table->string('mm_sub_title')->nullable();
            $table->string('description')->nullable();
            $table->string('mm_description')->nullable();
            $table->enum('level', ['basic', 'intermediate', 'advanced']);
            $table->enum('language', ['Myanmar','English']);
            $table->text('thumbnail_url')->nullable();
            $table->boolean('is_paid')->default(0);
            $table->double('price')->nullable();
            $table->enum('status', ['on_progress', 'finished', 'published', 'inactive']);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::COURSE);
    }
};

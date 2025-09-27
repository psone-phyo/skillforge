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
            $table->string('title');
            $table->string('mm_title');
            $table->longText('description');
            $table->longText('mm_description');
            $table->longText('outline');
            $table->longText('mm_outline');
            $table->boolean('is_free');
            $table->double('price')->default(0);
            $table->string('status');
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

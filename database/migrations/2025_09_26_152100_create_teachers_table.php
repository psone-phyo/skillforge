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
        Schema::create(Table::TEACHER, function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('user_id');
            $table->longText('bio');
            $table->longText('proposal');
            $table->text('cv');
            $table->text('profile');
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])
                    ->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::TEACHER);
    }
};

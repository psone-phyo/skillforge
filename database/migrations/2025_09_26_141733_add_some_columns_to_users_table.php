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
        Schema::table(Table::USER, function (Blueprint $table) {
            $table->string('provider')->after('password')->nullable();
            $table->text('provider_id')->after('provider')->nullable();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(Table::USER, function (Blueprint $table) {
            $table->dropColumn('provider');
            $table->dropColumn('provider_id');
        });
    }
};

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
        Schema::create(Table::PAYMENT, function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('course_id');
            $table->text('ref');
            $table->text('transaction_url')->nullable();
            $table->text('transaction_number')->nullable();
            $table->text('course_fee');
            $table->text('comission');
            $table->text('total_amount');
            $table->text('payment_method');
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::PAYMENT);
    }
};

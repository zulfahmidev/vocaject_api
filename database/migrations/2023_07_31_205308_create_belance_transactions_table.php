<?php

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
        Schema::create('belance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->enum('type', ['top-up', 'pencairan', 'pembayaran']);
            $table->integer('amount');
            $table->text('description');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('belance_transactions');
    }
};

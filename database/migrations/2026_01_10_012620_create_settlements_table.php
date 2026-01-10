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
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('winning_bid_id')->nullable()->constrained('bids')->cascadeOnDelete();
            $table->unsignedBigInteger('buyer_premium_cents')->default(0);
            $table->unsignedBigInteger('total_cents')->default(0);
            $table->string('status')->default('pending')->index();
            $table->timestamps();

            $table->unique('lot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};

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
        Schema::create('bids', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained();
            $table->integer('cycle_number');
            $table->decimal('discount_amount', 15, 2); // Le montant sacrifié
            $table->enum('status', ['pending', 'won', 'lost'])->default('pending');
            $table->timestamps();
            
            $table->unique(['group_id', 'user_id', 'cycle_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};

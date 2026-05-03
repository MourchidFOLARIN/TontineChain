<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->foreignUuid('creator_id')->constrained('users')->onDelete('restrict');
            $table->string('contract_address', 42)->nullable()->index();
            $table->string('contract_tx_hash', 66)->nullable();
            $table->decimal('contribution_amount', 18, 2);
            $table->string('contribution_token', 20)->default('USDC');
            $table->integer('max_members');
            $table->integer('current_members')->default(0);
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly']);
            $table->integer('current_cycle')->default(0);
            $table->integer('total_cycles')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending')->index();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('next_due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};

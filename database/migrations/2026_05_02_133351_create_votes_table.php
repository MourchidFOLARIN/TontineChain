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
        Schema::create('votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('creator_id')->constrained('users'); // Qui a fait la proposition
            $table->string('type'); // ex: 'swap_positions'
            $table->json('proposal_data'); // ex: {"user_a": "UUID", "user_b": "UUID"}
            $table->integer('yes_votes')->default(0);
            $table->integer('no_votes')->default(0);
            $table->integer('required_votes');
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('vote_records', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('vote_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained();
            $table->boolean('choice'); // true = yes, false = no
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vote_records');
        Schema::dropIfExists('votes');
    }
};

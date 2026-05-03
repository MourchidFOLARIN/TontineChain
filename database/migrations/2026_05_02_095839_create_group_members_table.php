<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('position');
            $table->enum('status', ['invited', 'active', 'suspended', 'excluded'])->default('invited');
            $table->boolean('has_received')->default(false);
            $table->integer('cycle_received')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            
            $table->unique(['group_id', 'user_id']);
            $table->unique(['group_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};

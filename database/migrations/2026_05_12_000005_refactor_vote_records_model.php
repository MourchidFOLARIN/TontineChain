<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vote_records')) {
            return;
        }

        Schema::table('vote_records', function (Blueprint $table) {
            $table->index(['vote_id', 'user_id'], 'vote_records_vote_id_user_id_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vote_records')) {
            return;
        }

        Schema::table('vote_records', function (Blueprint $table) {
            $table->dropIndex('vote_records_vote_id_user_id_index');
        });
    }
};

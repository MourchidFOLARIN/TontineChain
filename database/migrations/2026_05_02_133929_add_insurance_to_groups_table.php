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
        Schema::table('groups', function (Blueprint $table) {
            $table->decimal('insurance_fund', 15, 2)->default(0)->after('status');
            $table->decimal('insurance_percent', 5, 2)->default(1.0)->after('insurance_fund'); // 1% par défaut
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['insurance_fund', 'insurance_percent']);
        });
    }
};

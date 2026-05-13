<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Vérifier le driver utilisé et adapter le type de colonne
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'pgsql') {
            // PostgreSQL : jsonb est approprié
            Schema::table('tontine_notifications', function (Blueprint $table) {
                $table->jsonb('metadata')->nullable()->change();
            });
        } else {
            // MySQL/SQLite : utiliser json
            Schema::table('tontine_notifications', function (Blueprint $table) {
                $table->json('metadata')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Pas de changement à l'envers pour éviter les problèmes de compatibilité
    }
};

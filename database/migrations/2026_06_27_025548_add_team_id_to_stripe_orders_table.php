<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stripe_orders', function (Blueprint $table) {
            $table->foreignId('team_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['team_id', 'status']);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement(<<<'SQL'
                UPDATE stripe_orders
                INNER JOIN users ON users.id = stripe_orders.user_id
                LEFT JOIN teams current_teams ON current_teams.id = users.current_team_id
                LEFT JOIN teams personal_teams
                    ON personal_teams.user_id = users.id
                    AND personal_teams.personal_team = 1
                SET stripe_orders.team_id = COALESCE(current_teams.id, personal_teams.id)
                WHERE stripe_orders.team_id IS NULL
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stripe_orders', function (Blueprint $table) {
            $table->dropIndex(['team_id', 'status']);
            $table->dropConstrainedForeignId('team_id');
        });
    }
};

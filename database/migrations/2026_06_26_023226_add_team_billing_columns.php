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
        Schema::table('teams', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->after('personal_team')->index();
            $table->string('pm_type')->nullable()->after('stripe_id');
            $table->string('pm_last_four', 4)->nullable()->after('pm_type');
            $table->timestamp('trial_ends_at')->nullable()->after('pm_last_four');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->change();

            $table->index(['team_id', 'stripe_status']);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement(<<<'SQL'
                UPDATE subscriptions
                INNER JOIN users ON users.id = subscriptions.user_id
                LEFT JOIN teams current_teams ON current_teams.id = users.current_team_id
                LEFT JOIN teams personal_teams
                    ON personal_teams.user_id = users.id
                    AND personal_teams.personal_team = 1
                SET subscriptions.team_id = COALESCE(current_teams.id, personal_teams.id)
                WHERE subscriptions.team_id IS NULL
            SQL);

            DB::statement(<<<'SQL'
                UPDATE teams
                INNER JOIN subscriptions ON subscriptions.team_id = teams.id
                INNER JOIN users ON users.id = subscriptions.user_id
                SET
                    teams.stripe_id = COALESCE(teams.stripe_id, users.stripe_id),
                    teams.pm_type = COALESCE(teams.pm_type, users.pm_type),
                    teams.pm_last_four = COALESCE(teams.pm_last_four, users.pm_last_four),
                    teams.trial_ends_at = COALESCE(teams.trial_ends_at, users.trial_ends_at)
                WHERE users.stripe_id IS NOT NULL
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement(<<<'SQL'
                UPDATE subscriptions
                INNER JOIN teams ON teams.id = subscriptions.team_id
                SET subscriptions.user_id = teams.user_id
                WHERE subscriptions.user_id IS NULL
            SQL);
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['team_id', 'stripe_status']);
            $table->dropConstrainedForeignId('team_id');
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex(['stripe_id']);
            $table->dropColumn([
                'stripe_id',
                'pm_type',
                'pm_last_four',
                'trial_ends_at',
            ]);
        });
    }
};

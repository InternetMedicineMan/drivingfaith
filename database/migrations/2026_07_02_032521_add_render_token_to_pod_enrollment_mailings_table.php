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
        Schema::table('pod_enrollment_mailings', function (Blueprint $table) {
            $table->string('render_token', 64)->nullable()->after('rendered_html');
            $table->string('rendered_checksum', 64)->nullable()->after('render_token');

            $table->unique('render_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pod_enrollment_mailings', function (Blueprint $table) {
            $table->dropUnique(['render_token']);
            $table->dropColumn([
                'render_token',
                'rendered_checksum',
            ]);
        });
    }
};

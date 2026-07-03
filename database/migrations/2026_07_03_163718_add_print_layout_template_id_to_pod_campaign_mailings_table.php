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
        Schema::table('pod_campaign_mailings', function (Blueprint $table) {
            $table->foreignId('print_layout_template_id')
                ->nullable()
                ->after('cover_letter_template_id')
                ->constrained('pod_print_layout_templates')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pod_campaign_mailings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('print_layout_template_id');
        });
    }
};

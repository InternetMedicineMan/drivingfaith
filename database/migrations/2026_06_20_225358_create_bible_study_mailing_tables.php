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
        Schema::create('mailing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('source_key')->nullable()->unique();
            $table->string('status')->default('draft');
            $table->text('description')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'slug']);
            $table->index(['team_id', 'status']);
        });

        Schema::create('mailing_content_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('status')->default('draft');
            $table->string('provider')->default('lob');
            $table->string('provider_template_id')->nullable();
            $table->string('html_path')->nullable();
            $table->mediumText('html_content')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['team_id', 'type', 'slug', 'version']);
            $table->index(['team_id', 'type', 'status']);
        });

        Schema::create('campaign_mailings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('mailing_campaigns')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('sequence');
            $table->unsignedSmallInteger('delay_days_after_previous')->default(0);
            $table->boolean('pause_until_reply')->default(false);
            $table->foreignId('cover_letter_template_id')->nullable()->constrained('mailing_content_templates')->nullOnDelete();
            $table->foreignId('bible_study_template_id')->nullable()->constrained('mailing_content_templates')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->text('description')->nullable();
            $table->string('provider')->default('lob');
            $table->string('provider_template_id')->nullable();
            $table->string('mail_class')->default('marketing');
            $table->boolean('color')->default(false);
            $table->boolean('double_sided')->default(true);
            $table->string('address_placement')->default('top_first_page');
            $table->boolean('return_envelope')->default(true);
            $table->unsignedTinyInteger('perforated_page')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'sequence']);
            $table->index(['campaign_id', 'status']);
        });

        Schema::create('campaign_mailing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_mailing_id')->constrained('campaign_mailings')->cascadeOnDelete();
            $table->unsignedTinyInteger('page_number');
            $table->string('name')->nullable();
            $table->string('html_path')->nullable();
            $table->mediumText('html_content')->nullable();
            $table->string('paper_size')->default('letter');
            $table->string('orientation')->default('portrait');
            $table->unsignedTinyInteger('expected_page_count')->default(1);
            $table->string('checksum')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_mailing_id', 'page_number']);
        });

        Schema::create('mailing_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_key')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('organization')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address1');
            $table->string('address2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip', 20);
            $table->string('country', 2)->default('US');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['team_id', 'external_key']);
            $table->index(['team_id', 'last_name', 'first_name']);
            $table->index(['zip', 'state']);
        });

        Schema::create('campaign_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->constrained('mailing_campaigns')->cascadeOnDelete();
            $table->foreignId('mailing_contact_id')->constrained('mailing_contacts')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->date('paused_until')->nullable();
            $table->foreignId('next_mailing_id')->nullable()->constrained('campaign_mailings')->nullOnDelete();
            $table->date('next_send_on')->nullable();
            $table->unsignedSmallInteger('current_sequence')->default(1);
            $table->foreignId('reply_required_by_mailing_id')->nullable()->constrained('campaign_mailings')->nullOnDelete();
            $table->timestamp('reply_required_at')->nullable();
            $table->timestamp('reply_received_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'mailing_contact_id']);
            $table->index(['team_id', 'status']);
            $table->index(['status', 'next_send_on']);
        });

        Schema::create('enrollment_mailings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_enrollment_id')->constrained('campaign_enrollments')->cascadeOnDelete();
            $table->foreignId('campaign_mailing_id')->constrained('campaign_mailings')->cascadeOnDelete();
            $table->foreignId('mailing_contact_id')->constrained('mailing_contacts')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->string('status')->default('planned');
            $table->date('scheduled_for')->nullable();
            $table->foreignId('cover_letter_template_id')->nullable()->constrained('mailing_content_templates')->nullOnDelete();
            $table->foreignId('bible_study_template_id')->nullable()->constrained('mailing_content_templates')->nullOnDelete();
            $table->foreignId('override_cover_letter_template_id')->nullable()->constrained('mailing_content_templates')->nullOnDelete();
            $table->mediumText('override_cover_letter_html')->nullable();
            $table->text('cover_letter_override_reason')->nullable();
            $table->mediumText('rendered_html')->nullable();
            $table->timestamp('rendered_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_enrollment_id', 'campaign_mailing_id']);
            $table->index(['team_id', 'status', 'scheduled_for']);
            $table->index(['campaign_enrollment_id', 'sequence']);
        });

        Schema::create('mailing_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_enrollment_id')->constrained('campaign_enrollments')->cascadeOnDelete();
            $table->foreignId('enrollment_mailing_id')->nullable()->constrained('enrollment_mailings')->nullOnDelete();
            $table->foreignId('campaign_mailing_id')->constrained('campaign_mailings')->cascadeOnDelete();
            $table->foreignId('mailing_contact_id')->constrained('mailing_contacts')->cascadeOnDelete();
            $table->string('status')->default('queued');
            $table->date('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('provider')->default('lob');
            $table->string('provider_id')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->unsignedInteger('cost_cents')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_enrollment_id', 'campaign_mailing_id']);
            $table->index(['team_id', 'status']);
            $table->index(['status', 'scheduled_for']);
            $table->index(['provider', 'provider_id']);
        });

        Schema::create('campaign_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_enrollment_id')->constrained('campaign_enrollments')->cascadeOnDelete();
            $table->foreignId('enrollment_mailing_id')->nullable()->constrained('enrollment_mailings')->nullOnDelete();
            $table->foreignId('campaign_mailing_id')->nullable()->constrained('campaign_mailings')->nullOnDelete();
            $table->foreignId('mailing_contact_id')->constrained('mailing_contacts')->cascadeOnDelete();
            $table->timestamp('received_at')->useCurrent();
            $table->string('channel')->default('mail');
            $table->text('summary')->nullable();
            $table->mediumText('raw_content')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'received_at']);
            $table->index(['campaign_enrollment_id', 'received_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_replies');
        Schema::dropIfExists('mailing_deliveries');
        Schema::dropIfExists('enrollment_mailings');
        Schema::dropIfExists('campaign_enrollments');
        Schema::dropIfExists('mailing_contacts');
        Schema::dropIfExists('campaign_mailing_pages');
        Schema::dropIfExists('campaign_mailings');
        Schema::dropIfExists('mailing_content_templates');
        Schema::dropIfExists('mailing_campaigns');
    }
};

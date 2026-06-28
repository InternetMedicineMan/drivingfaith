<?php

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the bible study mailing schema', function () {
    expect(Schema::hasTable('pod_campaigns'))->toBeTrue()
        ->and(Schema::hasColumns('pod_campaigns', [
            'team_id',
            'name',
            'slug',
            'source_key',
            'status',
        ]))->toBeTrue()
        ->and(Schema::hasTable('pod_content_templates'))->toBeTrue()
        ->and(Schema::hasColumns('pod_content_templates', [
            'team_id',
            'type',
            'name',
            'provider_template_id',
            'html_content',
        ]))->toBeTrue()
        ->and(Schema::hasTable('pod_campaign_mailings'))->toBeTrue()
        ->and(Schema::hasColumns('pod_campaign_mailings', [
            'campaign_id',
            'sequence',
            'delay_days_after_previous',
            'pause_until_reply',
            'cover_letter_template_id',
            'bible_study_template_id',
            'provider_template_id',
        ]))->toBeTrue()
        ->and(Schema::hasTable('pod_campaign_mailing_pages'))->toBeTrue()
        ->and(Schema::hasColumns('pod_campaign_mailing_pages', [
            'campaign_mailing_id',
            'page_number',
            'html_path',
            'html_content',
        ]))->toBeTrue()
        ->and(Schema::hasTable('ministry_contacts'))->toBeTrue()
        ->and(Schema::hasColumns('ministry_contacts', [
            'team_id',
            'status',
            'first_source_type',
            'first_source_name',
            'first_contacted_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('ministry_contact_events'))->toBeTrue()
        ->and(Schema::hasColumns('ministry_contact_events', [
            'team_id',
            'contact_id',
            'type',
            'source',
            'summary',
        ]))->toBeTrue()
        ->and(Schema::hasTable('ministry_contact_tasks'))->toBeTrue()
        ->and(Schema::hasColumns('ministry_contact_tasks', [
            'team_id',
            'contact_id',
            'created_from_event_id',
            'assigned_to_user_id',
            'status',
            'type',
            'title',
            'due_at',
            'completed_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('pod_campaign_enrollments'))->toBeTrue()
        ->and(Schema::hasColumns('pod_campaign_enrollments', [
            'contact_id',
            'reply_required_by_mailing_id',
            'reply_required_at',
            'reply_received_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('pod_enrollment_mailings'))->toBeTrue()
        ->and(Schema::hasColumns('pod_enrollment_mailings', [
            'campaign_enrollment_id',
            'campaign_mailing_id',
            'cover_letter_template_id',
            'bible_study_template_id',
            'override_cover_letter_template_id',
            'override_cover_letter_html',
            'rendered_html',
        ]))->toBeTrue()
        ->and(Schema::hasTable('pod_deliveries'))->toBeTrue()
        ->and(Schema::hasTable('pod_replies'))->toBeTrue();
});

it('stores campaign enrollment and delivery state', function () {
    $team = Team::factory()->create();

    $campaignId = DB::table('pod_campaigns')->insertGetId([
        'team_id' => $team->id,
        'name' => 'Romans Bible Study',
        'slug' => 'romans-bible-study',
        'source_key' => 'romans-2026',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $coverTemplateId = DB::table('pod_content_templates')->insertGetId([
        'team_id' => $team->id,
        'type' => 'cover_letter',
        'name' => 'Default Cover Letter',
        'slug' => 'default-cover-letter',
        'status' => 'active',
        'html_content' => '<html><body>Hello {{ first_name }}</body></html>',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $mailingId = DB::table('pod_campaign_mailings')->insertGetId([
        'campaign_id' => $campaignId,
        'name' => 'Lesson 1',
        'sequence' => 1,
        'pause_until_reply' => true,
        'cover_letter_template_id' => $coverTemplateId,
        'status' => 'active',
        'provider_template_id' => 'tmpl_lesson_1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('pod_campaign_mailing_pages')->insert([
        'campaign_mailing_id' => $mailingId,
        'page_number' => 1,
        'name' => 'Lesson Page 1',
        'html_content' => '<div class="page"><p>Romans study content for {{ contact.first_name }}</p></div>',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $contactId = DB::table('ministry_contacts')->insertGetId([
        'team_id' => $team->id,
        'status' => 'active',
        'first_source_type' => 'bible_study',
        'first_source_name' => 'Romans Bible Study request',
        'first_contacted_at' => now(),
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'address1' => '123 Example St',
        'city' => 'Nashville',
        'state' => 'TN',
        'zip' => '37201',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $enrollmentId = DB::table('pod_campaign_enrollments')->insertGetId([
        'team_id' => $team->id,
        'campaign_id' => $campaignId,
        'contact_id' => $contactId,
        'status' => 'active',
        'next_mailing_id' => $mailingId,
        'next_send_on' => now()->toDateString(),
        'current_sequence' => 1,
        'reply_required_by_mailing_id' => $mailingId,
        'reply_required_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $enrollmentMailingId = DB::table('pod_enrollment_mailings')->insertGetId([
        'team_id' => $team->id,
        'campaign_enrollment_id' => $enrollmentId,
        'campaign_mailing_id' => $mailingId,
        'contact_id' => $contactId,
        'sequence' => 1,
        'status' => 'planned',
        'scheduled_for' => now()->toDateString(),
        'cover_letter_template_id' => $coverTemplateId,
        'override_cover_letter_html' => '<html><body>Custom one-time note</body></html>',
        'cover_letter_override_reason' => 'Personal pastoral follow-up.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('pod_deliveries')->insert([
        'team_id' => $team->id,
        'campaign_enrollment_id' => $enrollmentId,
        'enrollment_mailing_id' => $enrollmentMailingId,
        'campaign_mailing_id' => $mailingId,
        'contact_id' => $contactId,
        'status' => 'queued',
        'scheduled_for' => now()->toDateString(),
        'idempotency_key' => 'campaign-1:enrollment-1:mailing-1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('pod_replies')->insert([
        'team_id' => $team->id,
        'campaign_enrollment_id' => $enrollmentId,
        'enrollment_mailing_id' => $enrollmentMailingId,
        'campaign_mailing_id' => $mailingId,
        'contact_id' => $contactId,
        'channel' => 'mail',
        'summary' => 'Returned lesson response card.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('ministry_contact_events')->insert([
        'team_id' => $team->id,
        'contact_id' => $contactId,
        'type' => 'bible_study_request',
        'source' => 'bible_studies',
        'source_label' => 'Romans Bible Study',
        'summary' => 'Requested the Romans Bible Study.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $eventId = DB::table('ministry_contact_events')->insertGetId([
        'team_id' => $team->id,
        'contact_id' => $contactId,
        'type' => 'phone_call_requested',
        'source' => 'pod',
        'source_label' => 'Response Card',
        'summary' => 'Requested a phone call.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('ministry_contact_tasks')->insert([
        'team_id' => $team->id,
        'contact_id' => $contactId,
        'created_from_event_id' => $eventId,
        'type' => 'phone_call',
        'status' => 'open',
        'priority' => 'normal',
        'title' => 'Call Ada about Bible Study response',
        'due_at' => now()->addDay(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(DB::table('pod_campaign_enrollments')->where('status', 'active')->count())->toBe(1)
        ->and(DB::table('pod_enrollment_mailings')->whereNotNull('override_cover_letter_html')->count())->toBe(1)
        ->and(DB::table('pod_deliveries')->where('status', 'queued')->count())->toBe(1)
        ->and(DB::table('pod_replies')->count())->toBe(1)
        ->and(DB::table('ministry_contact_events')->where('contact_id', $contactId)->count())->toBe(2)
        ->and(DB::table('ministry_contact_tasks')->where('contact_id', $contactId)->where('status', 'open')->count())->toBe(1);
});

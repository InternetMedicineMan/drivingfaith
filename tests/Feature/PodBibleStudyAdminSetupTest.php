<?php

use App\Filament\Resources\MinistryContacts\MinistryContactResource;
use App\Filament\Resources\PodCampaignEnrollments\PodCampaignEnrollmentResource;
use App\Filament\Resources\PodCampaignMailings\PodCampaignMailingResource;
use App\Filament\Resources\PodCampaigns\PodCampaignResource;
use App\Filament\Resources\PodContentTemplates\PodContentTemplateResource;
use App\Filament\Resources\PodPrintLayoutTemplates\PodPrintLayoutTemplateResource;
use App\Filament\Resources\Teams\TeamResource;
use App\Models\MinistryContact;
use App\Models\MinistryContactEvent;
use App\Models\MinistryContactTask;
use App\Models\PodCampaign;
use App\Models\PodCampaignEnrollment;
use App\Models\PodCampaignMailing;
use App\Models\PodCampaignMailingPage;
use App\Models\PodContentTemplate;
use App\Models\PodEnrollmentMailing;
use App\Models\PodPrintLayoutTemplate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('models a team scoped bible study pod campaign setup', function () {
    $team = Team::factory()->create([
        'name' => 'Grace Chapel',
    ]);

    $campaign = PodCampaign::query()->create([
        'team_id' => $team->id,
        'name' => 'Romans Bible Study',
        'slug' => 'romans-bible-study',
        'source_key' => 'romans-2026',
        'status' => 'active',
    ]);

    $coverLetter = PodContentTemplate::query()->create([
        'team_id' => $team->id,
        'type' => 'cover_letter',
        'name' => 'Default Welcome Letter',
        'slug' => 'default-welcome-letter',
        'status' => 'active',
        'html_content' => '<html><body>Hello {{ first_name }}</body></html>',
    ]);

    expect(PodCampaignMailingResource::nextSequenceForCampaign($campaign->id))->toBe(1);

    $mailing = PodCampaignMailing::query()->create([
        'campaign_id' => $campaign->id,
        'name' => 'Lesson 1',
        'sequence' => 1,
        'pause_until_reply' => true,
        'cover_letter_template_id' => $coverLetter->id,
        'status' => 'active',
    ]);

    PodCampaignMailingPage::query()->create([
        'campaign_mailing_id' => $mailing->id,
        'page_number' => 1,
        'name' => 'Lesson Content',
        'html_content' => '<div class="page"><p>Romans lesson content for {{ contact.first_name }}</p></div>',
    ]);

    $contact = MinistryContact::query()->create([
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
    ]);

    MinistryContactEvent::query()->create([
        'contact_id' => $contact->id,
        'type' => 'bible_study_request',
        'source' => 'bible_studies',
        'source_label' => 'Romans Bible Study',
        'summary' => 'Requested the Romans Bible Study.',
    ]);

    $callEvent = MinistryContactEvent::query()->create([
        'contact_id' => $contact->id,
        'type' => 'phone_call_requested',
        'source' => 'pod',
        'source_label' => 'Response Card',
        'summary' => 'Requested a phone call.',
    ]);

    MinistryContactTask::query()->create([
        'contact_id' => $contact->id,
        'created_from_event_id' => $callEvent->id,
        'type' => 'phone_call',
        'status' => 'open',
        'priority' => 'normal',
        'title' => 'Call Ada about Bible Study response',
        'due_at' => now()->addDay(),
    ]);

    $enrollment = PodCampaignEnrollment::query()->create([
        'team_id' => $team->id,
        'campaign_id' => $campaign->id,
        'contact_id' => $contact->id,
        'status' => 'active',
        'next_mailing_id' => $mailing->id,
        'next_send_on' => now()->toDateString(),
        'current_sequence' => 1,
    ]);

    $plannedMailing = PodEnrollmentMailing::query()->create([
        'team_id' => $team->id,
        'campaign_enrollment_id' => $enrollment->id,
        'campaign_mailing_id' => $mailing->id,
        'contact_id' => $contact->id,
        'sequence' => 1,
        'status' => 'planned',
        'scheduled_for' => now()->toDateString(),
        'cover_letter_template_id' => $coverLetter->id,
        'render_token' => 'test-render-token',
    ]);

    $this->get("/pod/render/enrollment-mailings/{$plannedMailing->id}?token=wrong-token")
        ->assertNotFound();

    $this->get("/pod/render/enrollment-mailings/{$plannedMailing->id}?token=test-render-token")
        ->assertOk()
        ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
        ->assertSee('<!doctype html>', false)
        ->assertSee('Hello Ada', false)
        ->assertSee('Romans lesson content for Ada', false);

    expect($team->podCampaigns)->toHaveCount(1)
        ->and($team->podContentTemplates)->toHaveCount(1)
        ->and($team->ministryContacts)->toHaveCount(1)
        ->and($contact->events)->toHaveCount(2)
        ->and($contact->tasks)->toHaveCount(1)
        ->and($contact->tasks->first()->team_id)->toBe($team->id)
        ->and($contact->events->first()->team_id)->toBe($team->id)
        ->and($campaign->mailings)->toHaveCount(1)
        ->and(PodCampaignMailingResource::nextSequenceForCampaign($campaign->id))->toBe(2)
        ->and(PodPrintLayoutTemplate::query()->where('scope', 'system')->where('slot', 'letter_file')->count())->toBe(1)
        ->and($mailing->perforated_page)->toBeNull()
        ->and($mailing->pages)->toHaveCount(1)
        ->and($mailing->coverLetterTemplate->is($coverLetter))->toBeTrue()
        ->and($enrollment->contact->full_name)->toBe('Ada Lovelace')
        ->and($plannedMailing->refresh()->rendered_checksum)->not->toBeNull()
        ->and($enrollment->nextMailing->is($mailing))->toBeTrue();
});

it('places pod bible study admin resources under ministry module navigation', function () {
    expect(TeamResource::getNavigationGroup())->toBe('Ministry Modules')
        ->and(TeamResource::getNavigationLabel())->toBe('Ministry Groups')
        ->and(PodCampaignResource::getNavigationGroup())->toBe('Ministry Modules')
        ->and(PodCampaignResource::getNavigationLabel())->toBe('Bible Study Campaigns')
        ->and(PodContentTemplateResource::getNavigationParentItem())->toBe('Bible Study Campaigns')
        ->and(PodPrintLayoutTemplateResource::getNavigationParentItem())->toBe('Bible Study Campaigns')
        ->and(PodCampaignMailingResource::getNavigationParentItem())->toBe('Bible Study Campaigns')
        ->and(MinistryContactResource::getNavigationGroup())->toBe('People & Outreach')
        ->and(PodCampaignEnrollmentResource::getNavigationParentItem())->toBe('Bible Study Campaigns');
});

it('renders the ministry pod admin pages for admins', function (string $path) {
    $admin = User::factory()->create();

    Role::query()->create([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get($path)
        ->assertOk();
})->with([
    '/admin/teams',
    '/admin/teams/create',
    '/admin/pod-campaigns',
    '/admin/pod-campaigns/create',
    '/admin/pod-content-templates',
    '/admin/pod-content-templates/create',
    '/admin/pod-print-layout-templates',
    '/admin/pod-print-layout-templates/create',
    '/admin/pod-campaign-mailings',
    '/admin/pod-campaign-mailings/create',
    '/admin/ministry-contacts',
    '/admin/ministry-contacts/create',
    '/admin/pod-campaign-enrollments',
    '/admin/pod-campaign-enrollments/create',
]);

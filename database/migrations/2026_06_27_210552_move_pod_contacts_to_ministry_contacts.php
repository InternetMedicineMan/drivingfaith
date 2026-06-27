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
        if (! Schema::hasTable('ministry_contacts')) {
            $this->createMinistryContactsTable();
        }

        if (Schema::hasTable('pod_contacts')) {
            $this->copyPodContactsToMinistryContacts();

            $this->renameContactForeignKey('pod_campaign_enrollments');
            $this->renameContactForeignKey('pod_enrollment_mailings');
            $this->renameContactForeignKey('pod_deliveries');
            $this->renameContactForeignKey('pod_replies');

            Schema::dropIfExists('pod_contacts');
        }

        if (! Schema::hasTable('ministry_contact_events')) {
            Schema::create('ministry_contact_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('contact_id')->constrained('ministry_contacts')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->nullableMorphs('eventable');
                $table->string('type');
                $table->string('source')->nullable();
                $table->string('source_label')->nullable();
                $table->timestamp('occurred_at')->useCurrent();
                $table->text('summary')->nullable();
                $table->mediumText('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['team_id', 'type']);
                $table->index(['team_id', 'source']);
                $table->index(['contact_id', 'occurred_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ministry_contact_events');

        if (! Schema::hasTable('pod_contacts')) {
            $this->createPodContactsTable();
        }

        if (Schema::hasTable('ministry_contacts')) {
            $this->copyMinistryContactsToPodContacts();

            $this->restoreContactForeignKey('pod_campaign_enrollments');
            $this->restoreContactForeignKey('pod_enrollment_mailings');
            $this->restoreContactForeignKey('pod_deliveries');
            $this->restoreContactForeignKey('pod_replies');
        }
    }

    private function createMinistryContactsTable(): void
    {
        Schema::create('ministry_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_key')->nullable();
            $table->string('status')->default('active');
            $table->string('first_source_type')->nullable();
            $table->string('first_source_name')->nullable();
            $table->timestamp('first_contacted_at')->nullable();
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
            $table->index(['team_id', 'status']);
            $table->index(['team_id', 'first_source_type']);
            $table->index(['team_id', 'last_name', 'first_name']);
            $table->index(['zip', 'state']);
        });
    }

    private function createPodContactsTable(): void
    {
        Schema::create('pod_contacts', function (Blueprint $table) {
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
    }

    private function copyPodContactsToMinistryContacts(): void
    {
        DB::table('pod_contacts')
            ->orderBy('id')
            ->each(function (object $contact): void {
                DB::table('ministry_contacts')->updateOrInsert(
                    ['id' => $contact->id],
                    [
                        'team_id' => $contact->team_id,
                        'external_key' => $contact->external_key,
                        'status' => 'active',
                        'first_source_type' => 'pod',
                        'first_source_name' => 'POD contact import',
                        'first_contacted_at' => $contact->created_at,
                        'first_name' => $contact->first_name,
                        'last_name' => $contact->last_name,
                        'organization' => $contact->organization,
                        'email' => $contact->email,
                        'phone' => $contact->phone,
                        'address1' => $contact->address1,
                        'address2' => $contact->address2,
                        'city' => $contact->city,
                        'state' => $contact->state,
                        'zip' => $contact->zip,
                        'country' => $contact->country,
                        'metadata' => $contact->metadata,
                        'created_at' => $contact->created_at,
                        'updated_at' => $contact->updated_at,
                        'deleted_at' => $contact->deleted_at,
                    ],
                );
            });
    }

    private function copyMinistryContactsToPodContacts(): void
    {
        DB::table('ministry_contacts')
            ->orderBy('id')
            ->each(function (object $contact): void {
                DB::table('pod_contacts')->updateOrInsert(
                    ['id' => $contact->id],
                    [
                        'team_id' => $contact->team_id,
                        'external_key' => $contact->external_key,
                        'first_name' => $contact->first_name,
                        'last_name' => $contact->last_name,
                        'organization' => $contact->organization,
                        'email' => $contact->email,
                        'phone' => $contact->phone,
                        'address1' => $contact->address1,
                        'address2' => $contact->address2,
                        'city' => $contact->city,
                        'state' => $contact->state,
                        'zip' => $contact->zip,
                        'country' => $contact->country,
                        'metadata' => $contact->metadata,
                        'created_at' => $contact->created_at,
                        'updated_at' => $contact->updated_at,
                        'deleted_at' => $contact->deleted_at,
                    ],
                );
            });
    }

    private function renameContactForeignKey(string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'mailing_contact_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropForeign(['mailing_contact_id']);
        });

        Schema::table($tableName, function (Blueprint $table): void {
            $table->renameColumn('mailing_contact_id', 'contact_id');
        });

        Schema::table($tableName, function (Blueprint $table): void {
            $table->foreign('contact_id')->references('id')->on('ministry_contacts')->cascadeOnDelete();
        });
    }

    private function restoreContactForeignKey(string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'contact_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropForeign(['contact_id']);
        });

        Schema::table($tableName, function (Blueprint $table): void {
            $table->renameColumn('contact_id', 'mailing_contact_id');
        });

        Schema::table($tableName, function (Blueprint $table): void {
            $table->foreign('mailing_contact_id')->references('id')->on('pod_contacts')->cascadeOnDelete();
        });
    }
};

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowPathIcon,
    BuildingOffice2Icon,
    CheckCircleIcon,
    CreditCardIcon,
    PlusIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';
import AppLayout from '@/Layouts/AppLayout.vue';
import Plans from '@/Components/Plans.vue';

const props = defineProps({
    hasBillableTeam: {
        type: Boolean,
        default: false,
    },
    teamBilling: {
        type: Object,
        default: () => ({
            subscribed: false,
            status: null,
        }),
    },
});

const page = usePage();

const user = computed(() => page.props.auth.user);
const currentTeam = computed(() => user.value.current_team);
const teams = computed(() => user.value.all_teams ?? []);
const ministryTeams = computed(() => teams.value.filter((team) => ! team.personal_team));
const hasMinistryTeam = computed(() => ministryTeams.value.length > 0);
const teamLabel = computed(() => currentTeam.value?.name ?? 'No ministry team selected');
const isSubscribed = computed(() => Boolean(props.teamBilling.subscribed));
const canChoosePlan = computed(() => props.hasBillableTeam && ! isSubscribed.value);
const billingStatusLabel = computed(() => {
    if (! props.hasBillableTeam) {
        return 'Choose a team';
    }

    return isSubscribed.value ? 'Active' : 'Needs setup';
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-base-content/60">
                        {{ $t('Signed in as :name', { name: user.name }) }}
                    </p>
                    <h2 class="mt-1 text-2xl font-bold text-base-content">
                        {{ $t('Choose your ministry workspace') }}
                    </h2>
                </div>

                <div class="flex items-center gap-2 rounded-lg border border-base-300 bg-base-100 px-3 py-2 text-sm font-medium text-base-content">
                    <BuildingOffice2Icon class="h-5 w-5 text-primary" />
                    <span>{{ teamLabel }}</span>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
                    <section class="space-y-6">
                        <div class="rounded-lg border border-base-300 bg-base-100 p-6 shadow-sm">
                            <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                                <div class="max-w-2xl">
                                    <p class="text-sm font-semibold uppercase tracking-wide text-primary">
                                        {{ $t('Next step') }}
                                    </p>
                                    <h3 class="mt-2 text-2xl font-bold text-base-content">
                                        <template v-if="hasMinistryTeam">
                                            {{ $t('Work inside :team', { team: teamLabel }) }}
                                        </template>
                                        <template v-else>
                                            {{ $t('Create or join a church team') }}
                                        </template>
                                    </h3>
                                    <p class="mt-3 text-base text-base-content/70">
                                        <template v-if="hasMinistryTeam">
                                            {{ $t('Each church or group keeps its own people, outreach, and collaborators, so pastors and staff can move between the teams they serve.') }}
                                        </template>
                                        <template v-else>
                                            {{ $t('Your account is ready, but the ministry workspace is where the system becomes useful. Create the church or group you serve, then invite the people who should help run it.') }}
                                        </template>
                                    </p>
                                </div>

                                <div class="flex shrink-0 flex-col gap-3 sm:min-w-52">
                                    <Link :href="route('teams.create')" class="btn btn-primary">
                                        <PlusIcon class="h-5 w-5" />
                                        {{ $t('Create Team') }}
                                    </Link>
                                    <Link v-if="currentTeam" :href="route('teams.show', currentTeam)" class="btn btn-outline">
                                        <UserGroupIcon class="h-5 w-5" />
                                        {{ $t('Manage Team') }}
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="rounded-lg border border-base-300 bg-base-100 p-5">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <BuildingOffice2Icon class="h-5 w-5" />
                                </div>
                                <p class="mt-4 text-sm text-base-content/60">{{ $t('Ministry teams') }}</p>
                                <p class="mt-1 text-3xl font-bold text-base-content">{{ ministryTeams.length }}</p>
                            </div>

                            <div class="rounded-lg border border-base-300 bg-base-100 p-5">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <ArrowPathIcon class="h-5 w-5" />
                                </div>
                                <p class="mt-4 text-sm text-base-content/60">{{ $t('Available workspaces') }}</p>
                                <p class="mt-1 text-3xl font-bold text-base-content">{{ teams.length }}</p>
                            </div>

                            <div class="rounded-lg border border-base-300 bg-base-100 p-5">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-success/10 text-success">
                                    <CreditCardIcon class="h-5 w-5" />
                                </div>
                                <p class="mt-4 text-sm text-base-content/60">{{ $t('Billing status') }}</p>
                                <p class="mt-1 text-xl font-bold text-base-content">
                                    {{ $t(billingStatusLabel) }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-base-300 bg-base-100 shadow-sm">
                            <div class="border-b border-base-300 px-6 py-5">
                                <h3 class="text-lg font-bold text-base-content">{{ $t('Your teams') }}</h3>
                                <p class="mt-1 text-sm text-base-content/70">
                                    {{ $t('Churches, groups, and ministry teams you can access from this account.') }}
                                </p>
                            </div>

                            <div class="divide-y divide-base-300">
                                <div v-for="team in teams" :key="team.id" class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-base-200 text-base-content">
                                            <BuildingOffice2Icon class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-semibold text-base-content">{{ team.name }}</p>
                                                <span v-if="team.id === user.current_team_id" class="badge badge-primary badge-sm">
                                                    {{ $t('Current') }}
                                                </span>
                                                <span v-if="team.personal_team" class="badge badge-ghost badge-sm">
                                                    {{ $t('Personal') }}
                                                </span>
                                            </div>
                                            <p class="mt-1 text-sm text-base-content/60">
                                                {{ team.personal_team ? $t('Account home base') : $t('Team-owned workspace') }}
                                            </p>
                                        </div>
                                    </div>

                                    <Link :href="route('teams.show', team)" class="btn btn-ghost btn-sm">
                                        {{ $t('Settings') }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </section>

                    <aside class="space-y-6">
                        <div v-if="props.hasBillableTeam" class="rounded-lg border border-base-300 bg-base-100 p-6 shadow-sm">
                            <div class="flex items-center gap-3">
                                <CheckCircleIcon class="h-6 w-6 text-success" />
                                <h3 class="font-bold text-base-content">{{ $t('Subscription setup') }}</h3>
                            </div>
                            <p class="mt-3 text-sm text-base-content/70">
                                {{ $t('Start with the workspace you want to fund. Team-owned billing can be connected to this flow as the subscription model moves from accounts to teams.') }}
                            </p>
                            <Link v-if="isSubscribed" :href="route('stripe.billing')" class="btn btn-outline btn-sm mt-5 w-full">
                                <CreditCardIcon class="h-5 w-5" />
                                {{ $t('Manage Billing') }}
                            </Link>
                            <a v-else-if="canChoosePlan" href="#team-plans" class="btn btn-primary btn-sm mt-5 w-full">
                                <CreditCardIcon class="h-5 w-5" />
                                {{ $t('Choose Plan') }}
                            </a>
                        </div>

                        <div class="rounded-lg border border-base-300 bg-base-100 p-6 shadow-sm">
                            <h3 class="font-bold text-base-content">{{ $t('Invite collaborators') }}</h3>
                            <p class="mt-3 text-sm text-base-content/70">
                                {{ $t('Pastors, staff, and volunteers can all join the same team. Owners can invite them from team settings.') }}
                            </p>
                            <Link v-if="currentTeam" :href="route('teams.show', currentTeam)" class="btn btn-outline btn-sm mt-5 w-full">
                                <UserGroupIcon class="h-5 w-5" />
                                {{ $t('Open Team Settings') }}
                            </Link>
                        </div>
                    </aside>
                </div>

                <div v-if="canChoosePlan" id="team-plans" class="mt-12">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-base-content">{{ $t('Pick a plan for this workspace') }}</h2>
                        <p class="mt-2 text-base-content/70">
                            {{ $t('Choose the plan that fits the church or group you are setting up now.') }}
                        </p>
                    </div>
                    <Plans />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

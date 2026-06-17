<script setup>
import Banner from '@/Components/Profile/Banner.vue';
import {
    ArrowRightIcon,
    BellIcon,
    CalendarDaysIcon,
    CheckIcon,
    EnvelopeIcon,
    KeyIcon,
    PaperAirplaneIcon,
    ShieldCheckIcon,
    SparklesIcon,
    UsersIcon,
} from '@heroicons/vue/24/outline';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const page = usePage();
const currentYear = new Date().getFullYear();

const authUser = computed(() => page.props.auth?.user);

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('coming-soon.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('email'),
    });
};

const features = [
    {
        icon: UsersIcon,
        title: 'People & member directory',
        body: 'One unified record for members, regular attenders, and visitors. No more scattered spreadsheets.',
    },
    {
        icon: KeyIcon,
        title: 'Passwordless login',
        body: 'Members sign in with a one-time code or their Google account. Nothing to remember, nothing to reset.',
    },
    {
        icon: CalendarDaysIcon,
        title: 'Event sign-ups & check-in',
        body: 'Build a signup form in minutes. Run kiosk check-in for events like VBS without the chaos.',
    },
    {
        icon: CheckIcon,
        title: 'Nominations & voting',
        body: 'Run nominating committees and business-meeting votes with ranked, anonymized results.',
    },
    {
        icon: BellIcon,
        title: 'Notification hub',
        body: 'Reach everyone by push, text, and email from one place, plus an in-app message board.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Board portal',
        body: 'A private space for leadership to host documents and run meeting votes with confidence.',
    },
];

const tanglePoints = [
    'Member info trapped in a half-updated spreadsheet only one person can find.',
    'Announcements lost three group texts deep, somewhere under a meme.',
    'A different signup link for every potluck, retreat, and VBS week.',
    'Business-meeting votes counted on slips of paper passed down the pew.',
];

const steps = [
    {
        number: '01',
        title: 'Set up your church space',
        body: 'Create your private space in minutes. Each church is its own world.',
    },
    {
        number: '02',
        title: 'Invite your people',
        body: 'Add leaders, members, and volunteers. They sign in passwordlessly, with no support tickets.',
    },
    {
        number: '03',
        title: 'Run everything',
        body: 'Communication, events, voting, and outreach all live in one calm dashboard.',
    },
];

const values = [
    {
        title: 'Your data stays yours',
        body: 'Private by default. No selling, no scraping, no surprises.',
    },
    {
        title: 'Sabbath-aware',
        body: "The tool respects rest. Schedules, sends, and reminders honor your church's rhythm.",
    },
    {
        title: 'Time back for volunteers',
        body: 'Less admin friction means more of the ministry that actually matters.',
    },
];
</script>

<template>
    <Banner />
    <Head title="DrivingFaith - Run your whole church from one place">
        <meta
            name="description"
            content="DrivingFaith is the back-office platform built for churches. Manage people, communication, events, governance, and outreach in one calm, integrated system."
        >
        <meta property="og:title" content="DrivingFaith - Run your whole church from one place">
        <meta
            property="og:description"
            content="One calm, integrated platform for people, communication, events, voting, and outreach, built for churches."
        >
        <meta property="og:type" content="website">
    </Head>

    <div class="faith-page min-h-screen bg-[#fbf8ec] text-[#24352c]">
        <header class="sticky top-0 z-40 border-b border-[#ded7c3]/80 bg-[#fbf8ec]/90 backdrop-blur">
            <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-5">
                <a href="#top" class="flex min-w-0 items-center gap-2">
                    <img src="/images/drivingfaith-icon-square.png" alt="DrivingFaith" class="h-10 w-10 shrink-0 rounded-lg object-cover">
                    <span class="truncate text-lg font-semibold tracking-tight">DrivingFaith</span>
                </a>

                <div class="hidden items-center gap-8 md:flex">
                    <a href="#features" class="text-sm text-[#637466] transition hover:text-[#24352c]">Features</a>
                    <a href="#how" class="text-sm text-[#637466] transition hover:text-[#24352c]">How it works</a>
                    <a href="#outreach" class="text-sm text-[#637466] transition hover:text-[#24352c]">Outreach</a>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <Link
                        v-if="authUser"
                        :href="route('dashboard')"
                        class="hidden rounded-md px-3 py-2 text-sm font-medium text-[#305f49] transition hover:bg-[#efe8d3] sm:inline-flex"
                    >
                        Dashboard
                    </Link>
                    <Link
                        v-else-if="canLogin"
                        :href="route('login')"
                        class="hidden rounded-md px-3 py-2 text-sm font-medium text-[#305f49] transition hover:bg-[#efe8d3] sm:inline-flex"
                    >
                        Log in
                    </Link>
                    <a
                        href="#waitlist"
                        class="inline-flex h-10 items-center gap-1.5 rounded-md bg-[#305f49] px-4 text-sm font-medium text-[#fbf8ec] transition hover:bg-[#254938]"
                    >
                        Join waitlist
                        <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                    </a>
                </div>
            </nav>
        </header>

        <main id="top">
            <section class="faith-grain relative overflow-hidden">
                <div class="mx-auto max-w-6xl px-5 pb-20 pt-20 sm:pb-28 sm:pt-28">
                    <div class="mx-auto max-w-3xl text-center">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[#ded7c3] bg-white/70 px-3 py-1 text-xs font-medium text-[#637466]">
                            <SparklesIcon class="h-4 w-4 text-[#c89528]" aria-hidden="true" />
                            Now welcoming early churches
                        </span>

                        <h1 class="mt-6 text-5xl font-medium leading-[1.05] tracking-normal text-[#24352c] sm:text-6xl md:text-7xl">
                            Run your whole church
                            <br>
                            <span class="italic text-[#305f49]">from one calm place.</span>
                        </h1>

                        <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-[#637466] sm:text-xl">
                            DrivingFaith brings people, communication, events, governance, and outreach
                            into one back office your volunteers can actually use.
                        </p>

                        <form id="waitlist" class="mx-auto mt-10 max-w-2xl scroll-mt-24 text-left" novalidate @submit.prevent="submit">
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <div class="flex-1">
                                    <label for="hero-email" class="sr-only">Email address</label>
                                    <input
                                        id="hero-email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        autocomplete="email"
                                        placeholder="you@yourchurch.org"
                                        class="h-12 w-full rounded-md border-[#ded7c3] bg-white/85 px-4 text-base text-[#24352c] placeholder:text-[#7a877b] transition focus:border-[#c89528] focus:ring-[#c89528]/30"
                                    >
                                </div>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-[#305f49] px-6 font-medium text-[#fbf8ec] transition hover:bg-[#254938] disabled:opacity-70"
                                >
                                    <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-[#fbf8ec]/40 border-t-[#fbf8ec]" />
                                    <span>{{ form.processing ? 'Joining...' : 'Join the waitlist' }}</span>
                                    <ArrowRightIcon v-if="! form.processing" class="h-4 w-4" aria-hidden="true" />
                                </button>
                            </div>
                            <p v-if="form.errors.email" class="mt-3 text-sm text-[#b42318]" role="alert">
                                {{ form.errors.email }}
                            </p>
                            <p v-else-if="form.recentlySuccessful" class="mt-3 text-sm font-medium text-[#305f49]" role="status">
                                You're on the list. We'll be in touch when there's something worth saying.
                            </p>
                            <p class="mt-3 text-sm text-[#637466]">
                                No spam. We'll only email you when there's something worth saying.
                            </p>
                        </form>
                    </div>
                </div>
            </section>

            <section class="border-t border-[#ded7c3] bg-[#f5efd9]">
                <div class="mx-auto max-w-4xl px-5 py-20 sm:py-28">
                    <p class="text-sm font-medium uppercase tracking-[0.18em] text-[#305f49]/80">
                        The everyday tangle
                    </p>
                    <h2 class="mt-3 text-3xl text-[#24352c] sm:text-4xl md:text-5xl">
                        Most churches are running on a dozen tools that don't talk to each other.
                    </h2>
                    <div class="mt-10 grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="point in tanglePoints"
                            :key="point"
                            class="group flex gap-4 rounded-lg border border-[#d78f80]/45 bg-white/70 p-5 shadow-[0_16px_36px_-30px_rgba(95,48,48,0.6)] transition hover:-translate-y-0.5 hover:border-[#b85f52]/70 hover:bg-white/90"
                        >
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-[#b85f52]/30 bg-[#f9ded8] text-[#a54135]">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2.4" aria-hidden="true">
                                    <path d="M5 5l10 10M15 5L5 15" />
                                </svg>
                            </span>
                            <p class="text-base leading-7 text-[#536458] sm:text-lg">
                                {{ point }}
                            </p>
                        </div>
                    </div>
                    <p class="mt-10 text-2xl italic text-[#24352c] sm:text-3xl">
                        There has to be a better way, and now there is.
                    </p>
                </div>
            </section>

            <section id="features" class="scroll-mt-20 border-t border-[#ded7c3] bg-[#fbf8ec]">
                <div class="mx-auto max-w-6xl px-5 py-24 sm:py-32">
                    <div class="max-w-2xl">
                        <p class="text-sm font-medium uppercase tracking-[0.18em] text-[#305f49]/80">
                            Everything in one place
                        </p>
                        <h2 class="mt-3 text-4xl text-[#24352c] sm:text-5xl">
                            Built around the work your church already does.
                        </h2>
                        <p class="mt-4 text-lg leading-8 text-[#637466]">
                            Six tightly connected pieces, designed so a brand-new volunteer can pick it up on a Sunday morning.
                        </p>
                    </div>

                    <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <article
                            v-for="feature in features"
                            :key="feature.title"
                            class="rounded-lg border border-[#ded7c3] bg-white/75 p-6 transition hover:border-[#305f49]/40 hover:shadow-[0_14px_34px_-22px_rgba(36,53,44,0.35)]"
                        >
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-[#e2eddf] text-[#305f49]">
                                <component :is="feature.icon" class="h-5 w-5" aria-hidden="true" />
                            </span>
                            <h3 class="mt-5 text-xl font-semibold text-[#24352c]">{{ feature.title }}</h3>
                            <p class="mt-2 text-[15px] leading-relaxed text-[#637466]">{{ feature.body }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="outreach" class="scroll-mt-20 bg-[#305f49] text-[#fbf8ec]">
                <div class="mx-auto grid max-w-6xl gap-12 px-5 py-24 sm:py-32 lg:grid-cols-[1.1fr_1fr] lg:items-center">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-[0.18em] text-[#e0b343]">
                            Outreach by mail
                        </p>
                        <h2 class="mt-3 text-4xl leading-tight sm:text-5xl md:text-6xl">
                            Reach people where they are,
                            <span class="italic text-[#e0b343]"> even offline.</span>
                        </h2>
                        <p class="mt-6 max-w-xl text-lg leading-8 text-[#fbf8ec]/80">
                            Some of the people your church is called to reach aren't on email. They aren't
                            checking an app. DrivingFaith bridges digital and physical outreach so no one
                            falls through the cracks.
                        </p>

                        <div class="mt-10 space-y-6">
                            <div class="flex gap-4 rounded-lg border border-[#fbf8ec]/15 bg-[#fbf8ec]/5 p-5 backdrop-blur-sm">
                                <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#e0b343] text-[#3c2f09]">
                                    <EnvelopeIcon class="h-5 w-5" aria-hidden="true" />
                                </span>
                                <div>
                                    <h3 class="text-xl font-semibold">Bible studies by mail</h3>
                                    <p class="mt-1.5 text-[15px] leading-relaxed text-[#fbf8ec]/80">
                                        A member or guest requests a study, and a beautifully printed copy is automatically generated and mailed to them.
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-4 rounded-lg border border-[#fbf8ec]/15 bg-[#fbf8ec]/5 p-5 backdrop-blur-sm">
                                <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#e0b343] text-[#3c2f09]">
                                    <PaperAirplaneIcon class="h-5 w-5" aria-hidden="true" />
                                </span>
                                <div>
                                    <h3 class="text-xl font-semibold">Event postcards</h3>
                                    <p class="mt-1.5 text-[15px] leading-relaxed text-[#fbf8ec]/80">
                                        Send printed postcard invitations for events and outreach campaigns right alongside digital invites.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute -inset-4 rounded-lg bg-[#e0b343]/15 blur-2xl" aria-hidden="true" />
                        <div class="relative rotate-[-1.5deg] rounded-lg border border-[#e0b343]/30 bg-[#fffdf7] p-6 text-[#24352c] shadow-[0_18px_50px_-28px_rgba(0,0,0,0.45)]">
                            <div class="flex items-center justify-between gap-3 text-xs text-[#637466]">
                                <span>Outgoing · First-class mail</span>
                                <span class="rounded-full bg-[#fbf1c5] px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider text-[#6a4c00]">
                                    Ready to send
                                </span>
                            </div>
                            <div class="mt-5 border-t border-dashed border-[#ded7c3] pt-5">
                                <p class="text-2xl leading-snug text-[#24352c]">
                                    "You asked about <span class="italic text-[#305f49]">hope</span>. We're sending the first study your way."
                                </p>
                                <p class="mt-4 text-sm text-[#637466]">To: Maria L.</p>
                                <p class="text-sm text-[#637466]">From: Hillcrest Community Church</p>
                            </div>
                            <div class="mt-6 flex items-center gap-2 rounded-md bg-[#f5efd9] px-3 py-2 text-xs text-[#637466]">
                                <span class="h-2 w-2 rounded-full bg-[#e0b343]" />
                                Auto-generated · Printed · Mailed
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="how" class="scroll-mt-20 border-t border-[#ded7c3] bg-[#f5efd9]">
                <div class="mx-auto max-w-6xl px-5 py-24 sm:py-32">
                    <div class="max-w-2xl">
                        <p class="text-sm font-medium uppercase tracking-[0.18em] text-[#305f49]/80">
                            How it works
                        </p>
                        <h2 class="mt-3 text-4xl text-[#24352c] sm:text-5xl">
                            Three steps. No IT degree required.
                        </h2>
                    </div>
                    <ol class="mt-14 grid gap-6 md:grid-cols-3">
                        <li v-for="step in steps" :key="step.number" class="rounded-lg border border-[#ded7c3] bg-white/75 p-7">
                            <span class="text-3xl italic text-[#c89528]">{{ step.number }}</span>
                            <h3 class="mt-3 text-xl font-semibold text-[#24352c]">{{ step.title }}</h3>
                            <p class="mt-2 text-[15px] leading-relaxed text-[#637466]">{{ step.body }}</p>
                        </li>
                    </ol>
                </div>
            </section>

            <section class="border-t border-[#ded7c3] bg-[#fbf8ec]">
                <div class="mx-auto max-w-4xl px-5 py-24 text-center sm:py-32">
                    <p class="text-sm font-medium uppercase tracking-[0.18em] text-[#305f49]/80">
                        Built for ministry
                    </p>
                    <h2 class="mt-3 text-4xl text-[#24352c] sm:text-5xl">
                        Made by someone in ministry, for the churches doing the work.
                    </h2>
                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-[#637466]">
                        DrivingFaith is built first for Seventh-day Adventist congregations and welcomes
                        any Christian church. We care about the things you care about.
                    </p>
                    <div class="mt-12 grid gap-6 text-left sm:grid-cols-3">
                        <div v-for="value in values" :key="value.title" class="rounded-lg border border-[#ded7c3] bg-white/75 p-6">
                            <h3 class="text-lg font-semibold text-[#24352c]">{{ value.title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-[#637466]">{{ value.body }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-[#f5efd9]">
                <div class="mx-auto max-w-3xl px-5 py-24 text-center sm:py-32">
                    <h2 class="text-4xl text-[#24352c] sm:text-5xl">
                        Be among the first churches on board.
                    </h2>
                    <p class="mt-5 text-lg leading-8 text-[#637466]">
                        We're inviting a small group of churches to shape DrivingFaith before launch.
                        Leave your email and we'll save you a seat.
                    </p>
                    <form class="mt-10 text-left" novalidate @submit.prevent="submit">
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <div class="flex-1">
                                <label for="footer-email" class="sr-only">Email address</label>
                                <input
                                    id="footer-email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                    placeholder="you@yourchurch.org"
                                    class="h-12 w-full rounded-md border-[#ded7c3] bg-white px-4 text-base text-[#24352c] placeholder:text-[#7a877b] transition focus:border-[#c89528] focus:ring-[#c89528]/30"
                                >
                            </div>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-[#305f49] px-6 font-medium text-[#fbf8ec] transition hover:bg-[#254938] disabled:opacity-70"
                            >
                                <span>{{ form.processing ? 'Joining...' : 'Join the waitlist' }}</span>
                                <ArrowRightIcon v-if="! form.processing" class="h-4 w-4" aria-hidden="true" />
                            </button>
                        </div>
                        <p v-if="form.errors.email" class="mt-3 text-sm text-[#b42318]" role="alert">
                            {{ form.errors.email }}
                        </p>
                        <p v-else-if="form.recentlySuccessful" class="mt-3 text-sm font-medium text-[#305f49]" role="status">
                            You're on the list. Thanks for trusting us early.
                        </p>
                    </form>
                </div>
            </section>
        </main>

        <footer class="border-t border-[#ded7c3] bg-[#fbf8ec]">
            <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 px-5 py-10 sm:flex-row sm:items-center">
                <div class="flex items-center gap-2">
                    <img src="/images/drivingfaith-icon-square.png" alt="DrivingFaith" class="h-10 w-10 rounded-lg object-cover">
                    <div>
                        <p class="text-base font-semibold text-[#24352c]">DrivingFaith</p>
                        <p class="text-xs text-[#637466]">Built for churches.</p>
                    </div>
                </div>
                <div class="flex flex-col items-start gap-1 text-sm text-[#637466] sm:items-end">
                    <a href="mailto:hello@drivingfaith.app" class="transition hover:text-[#24352c]">
                        hello@drivingfaith.app
                    </a>
                    <p>&copy; {{ currentYear }} DrivingFaith. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.faith-page {
    font-family: Inter, ui-sans-serif, system-ui, sans-serif;
}

.faith-page h1,
.faith-page h2,
.faith-page h3 {
    font-family: Georgia, ui-serif, serif;
}

.faith-grain {
    background-image:
        radial-gradient(ellipse 80% 50% at 50% -10%, rgba(224, 179, 67, 0.18), transparent 60%),
        radial-gradient(ellipse 60% 40% at 90% 10%, rgba(48, 95, 73, 0.14), transparent 60%);
}
</style>

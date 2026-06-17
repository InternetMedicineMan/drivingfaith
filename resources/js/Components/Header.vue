<script setup>
import { Link } from '@inertiajs/vue3';

const publicLinks = [
    { label: 'Features', href: '/#features' },
    { label: 'How it works', href: '/#how' },
    { label: 'Outreach', href: '/#outreach' },
    { label: 'Roadmap', route: 'roadmap.index' },
    { label: 'Blog', route: 'blog.index' },
];
</script>

<template>
    <header class="sticky top-0 z-50 w-full border-b border-[#ded7c3]/80 bg-[#fbf8ec]/90 text-[#24352c] backdrop-blur">
        <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-5">
            <div class="flex min-w-0 items-center gap-3">
                <details class="dropdown lg:hidden">
                    <summary class="btn btn-ghost btn-circle btn-sm text-[#305f49]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h12M4 18h16" />
                        </svg>
                    </summary>
                    <ul class="menu dropdown-content z-[1] mt-3 w-56 gap-1 rounded-lg border border-[#ded7c3] bg-[#fbf8ec] p-3 shadow-lg">
                        <li v-for="link in publicLinks" :key="link.label">
                            <Link v-if="link.route" :href="route(link.route)" class="rounded-md text-[#305f49]">
                                {{ link.label }}
                            </Link>
                            <a v-else :href="link.href" class="rounded-md text-[#305f49]">
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </details>

                <Link href="/" class="flex min-w-0 items-center gap-2 transition-opacity hover:opacity-85">
                    <img class="h-10 w-10 rounded-lg object-cover" src="/images/drivingfaith-icon-square.png" alt="DrivingFaith">
                    <span class="truncate text-lg font-semibold tracking-tight">DrivingFaith</span>
                </Link>
            </div>

            <div class="hidden items-center gap-7 lg:flex">
                <template v-for="link in publicLinks" :key="link.label">
                    <Link v-if="link.route" :href="route(link.route)" class="text-sm font-medium text-[#637466] transition hover:text-[#24352c]">
                        {{ link.label }}
                    </Link>
                    <a v-else :href="link.href" class="text-sm font-medium text-[#637466] transition hover:text-[#24352c]">
                        {{ link.label }}
                    </a>
                </template>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <template v-if="$page.props.auth.user">
                    <Link :href="route('dashboard')" class="hidden rounded-md px-3 py-2 text-sm font-medium text-[#305f49] transition hover:bg-[#efe8d3] sm:inline-flex">
                        Dashboard
                    </Link>
                </template>
                <template v-else>
                    <Link :href="route('login')" class="hidden rounded-md px-3 py-2 text-sm font-medium text-[#305f49] transition hover:bg-[#efe8d3] sm:inline-flex">
                        Log in
                    </Link>
                </template>

                <a href="/#waitlist" class="inline-flex h-10 items-center rounded-md bg-[#305f49] px-4 text-sm font-medium text-[#fbf8ec] transition hover:bg-[#254938]">
                    Join waitlist
                </a>
            </div>
        </nav>
    </header>
</template>

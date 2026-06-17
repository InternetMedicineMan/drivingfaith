<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div data-theme="light" class="min-h-screen bg-[#fbf8ec] text-[#24352c]">
        <header class="border-b border-[#ded7c3]/80 bg-[#fbf8ec]/90 backdrop-blur">
            <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-5">
                <Link href="/" class="flex min-w-0 items-center gap-2">
                    <img src="/images/drivingfaith-icon-square.png" alt="DrivingFaith" class="h-10 w-10 rounded-lg object-cover">
                    <span class="truncate text-lg font-semibold tracking-tight">DrivingFaith</span>
                </Link>

                <div class="flex shrink-0 items-center gap-2 text-sm">
                    <slot name="actions">
                        <Link href="/" class="hidden rounded-md px-3 py-2 font-medium text-[#305f49] transition hover:bg-[#efe8d3] sm:inline-flex">
                            Home
                        </Link>
                        <a href="/#waitlist" class="inline-flex h-10 items-center rounded-md bg-[#305f49] px-4 font-medium text-[#fbf8ec] transition hover:bg-[#254938]">
                            Join waitlist
                        </a>
                    </slot>
                </div>
            </nav>
        </header>

        <main class="faith-auth-bg flex min-h-[calc(100vh-4rem)] flex-col items-center px-5 py-10 sm:justify-center sm:py-14">
            <div class="text-center">
                <slot name="logo" />
            </div>

            <div class="mt-8 w-full overflow-hidden rounded-lg border border-[#ded7c3] bg-white/85 px-6 py-6 shadow-[0_22px_65px_-38px_rgba(36,53,44,0.5)] backdrop-blur sm:max-w-md sm:px-8 sm:py-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.faith-auth-bg {
    background-image:
        radial-gradient(ellipse 70% 38% at 50% 0%, rgba(224, 179, 67, 0.18), transparent 65%),
        radial-gradient(ellipse 55% 34% at 90% 15%, rgba(48, 95, 73, 0.13), transparent 65%);
}
</style>

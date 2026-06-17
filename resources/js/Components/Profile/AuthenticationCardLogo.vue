<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationMark from "@/Components/Profile/ApplicationMark.vue";
</script>

<template>
    <Link :href="'/'" class="group inline-flex flex-col items-center gap-3">
        <ApplicationMark class="block h-24 w-24 rounded-2xl shadow-[0_18px_45px_-24px_rgba(48,95,73,0.8)] transition group-hover:scale-105" />
        <span class="text-lg font-semibold tracking-tight text-[#24352c]">DrivingFaith</span>
    </Link>
</template>

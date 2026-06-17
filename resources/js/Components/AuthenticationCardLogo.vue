<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'" class="group inline-flex flex-col items-center justify-center transition-transform hover:scale-105">
        <div class="mb-3 flex h-20 w-20 items-center justify-center rounded-2xl shadow-lg">
            <img class="h-20 w-20 rounded-2xl object-cover" src="/images/drivingfaith-icon-square.png" alt="DrivingFaith">
        </div>
        <span class="bg-gradient-to-r from-[#305f49] to-[#c89528] bg-clip-text text-2xl font-bold text-transparent">
            DrivingFaith
        </span>
    </Link>
</template>

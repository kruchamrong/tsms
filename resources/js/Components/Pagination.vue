<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center justify-end gap-1 mt-4">
        <template v-for="(link, p) in links" :key="p">
            <div v-if="link.url === null" class="px-3 py-2 text-sm font-medium text-gray-400 border border-gray-200 rounded-md bg-gray-50 cursor-not-allowed" v-html="link.label" />
            <Link v-else 
                class="px-3 py-2 text-sm font-medium border rounded-md transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                :class="link.active ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm' : 'border-gray-200 text-gray-700 bg-white hover:bg-gray-50 hover:text-indigo-600'" 
                :href="link.url" 
                v-html="link.label" 
            />
        </template>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array
});
</script>

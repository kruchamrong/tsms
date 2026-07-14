<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="print-layout">
        <!-- We use print media queries to ensure this looks good on paper -->
        <main class="w-full bg-white text-black print:m-0 print:p-0">
            <slot />
        </main>
    </div>
</template>

<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 1cm;
    }
    body {
        margin: 0;
        padding: 0;
        background: white;
    }
    .print-layout {
        background: white;
    }
    /* Hide URL and Page number that browsers usually add if possible, though user has to uncheck it in print dialog usually */
}
</style>

$indexTemplate = @"
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>
<template>
    <Head title="Management" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800 leading-tight">Management</h2></template>
        <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8"><div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4">ទំព័រនេះកំពុងត្រូវបានរៀបចំ (Under Construction)</h3>
        </div></div></div>
    </AuthenticatedLayout>
</template>
"@

Set-Content "resources\js\Pages\Subject\Index.vue" $indexTemplate
Set-Content "resources\js\Pages\Subject\Create.vue" $indexTemplate
Set-Content "resources\js\Pages\Subject\Edit.vue" $indexTemplate
Set-Content "resources\js\Pages\SchoolClass\Index.vue" $indexTemplate
Set-Content "resources\js\Pages\SchoolClass\Create.vue" $indexTemplate
Set-Content "resources\js\Pages\SchoolClass\Edit.vue" $indexTemplate
Set-Content "resources\js\Pages\Room\Index.vue" $indexTemplate
Set-Content "resources\js\Pages\Room\Create.vue" $indexTemplate
Set-Content "resources\js\Pages\Room\Edit.vue" $indexTemplate

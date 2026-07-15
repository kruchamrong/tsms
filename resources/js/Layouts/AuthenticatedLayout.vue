<script setup>
import { ref, watch, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const unreadNotifications = computed(() => {
    const notifs = usePage().props.auth.notifications;
    if (!notifs) return [];
    if (Array.isArray(notifs)) return notifs;
    return Object.values(notifs);
});

const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

watch(() => usePage().props.flash, (flash) => {
    if (flash?.success) {
        toastMessage.value = flash.success;
        toastType.value = 'success';
        showToast.value = true;
        setTimeout(() => { showToast.value = false; }, 3000);
    } else if (flash?.error) {
        toastMessage.value = flash.error;
        toastType.value = 'error';
        showToast.value = true;
        setTimeout(() => { showToast.value = false; }, 8000);
    }
}, { deep: true, immediate: true });
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav
                class="border-b border-gray-100 bg-white"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-2 lg:space-x-4 sm:-my-px sm:ms-6 sm:flex items-center"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    ផ្ទាំងដើម
                                </NavLink>
                                <template v-if="$page.props.auth.user.role === 'school_admin'">
                                    <NavLink
                                        :href="route('teachers.index')"
                                        :active="route().current('teachers.*')"
                                    >
                                        គ្រូបង្រៀន
                                    </NavLink>
                                    
                                    <!-- Settings Dropdown (កំណត់) -->
                                    <div class="hidden sm:flex sm:items-center relative ml-2">
                                        <Dropdown align="left" width="48">
                                            <template #trigger>
                                                <span class="inline-flex rounded-md">
                                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150" :class="{ 'text-gray-900 font-semibold border-b-2 border-indigo-400': route().current('subjects.*') || route().current('curricula.*') || route().current('classes.*') || route().current('rooms.*') }">
                                                        កំណត់
                                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>
                                            <template #content>
                                                <DropdownLink :href="route('subjects.index')" :active="route().current('subjects.*')">មុខវិជ្ជា</DropdownLink>
                                                <DropdownLink :href="route('curricula.index')" :active="route().current('curricula.*')">កម្មវិធីសិក្សា</DropdownLink>
                                                <DropdownLink :href="route('classes.index')" :active="route().current('classes.*')">ថ្នាក់រៀន</DropdownLink>
                                                <DropdownLink :href="route('rooms.index')" :active="route().current('rooms.*')">បន្ទប់រៀន</DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>

                                    <!-- Timetable Dropdown (កាលវិភាគ) -->
                                    <div class="hidden sm:flex sm:items-center relative ml-2">
                                        <Dropdown align="left" width="48">
                                            <template #trigger>
                                                <span class="inline-flex rounded-md">
                                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150" :class="{ 'text-gray-900 font-semibold border-b-2 border-indigo-400': route().current('teaching-assignments.*') || route().current('teacher-availabilities.*') || route().current('timetables.*') }">
                                                        កាលវិភាគ
                                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>
                                            <template #content>
                                                <DropdownLink :href="route('teaching-assignments.index')" :active="route().current('teaching-assignments.*')">បែងចែកភារកិច្ចបង្រៀន</DropdownLink>
                                                <DropdownLink :href="route('teacher-availabilities.index')" :active="route().current('teacher-availabilities.*')">កំណត់ម៉ោងទំនេរគ្រូ</DropdownLink>
                                                <DropdownLink :href="route('timetables.index')" :active="route().current('timetables.*')">រៀបចំកាលវិភាគគ្រូបង្រៀន</DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>

                                    <!-- Absence Management Dropdown (គ្រប់គ្រងអវត្តមាន) -->
                                    <div class="hidden sm:flex sm:items-center relative ml-2">
                                        <Dropdown align="left" width="48">
                                            <template #trigger>
                                                <span class="inline-flex rounded-md">
                                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150" :class="{ 'text-gray-900 font-semibold border-b-2 border-indigo-400': route().current('teacher-leaves.*') || route().current('substitute-assignments.*') }">
                                                        គ្រប់គ្រងអវត្តមាន
                                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>
                                            <template #content>
                                                <DropdownLink :href="route('teacher-leaves.index')" :active="route().current('teacher-leaves.*')">សុំច្បាប់</DropdownLink>
                                                <DropdownLink :href="route('substitute-assignments.index')" :active="route().current('substitute-assignments.*')">គ្រូជំនួស</DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>

                                    <NavLink :href="route('reports.index')" :active="route().current('reports.*')">
                                        របាយការណ៍
                                    </NavLink>
                                </template>
                                
                                <!-- Global Templates Dropdown -->
                                <div class="hidden sm:flex sm:items-center relative ml-2" v-if="$page.props.auth.user.role === 'super_admin'">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150" :class="{ 'text-gray-900 font-semibold border-b-2 border-indigo-400': route().current('admin.template-*') }">
                                                    គ្រប់គ្រងទិន្នន័យគំរូ
                                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <DropdownLink :href="route('admin.template-subject-groups.index')" :active="route().current('admin.template-subject-groups.*')">កម្រងមុខវិជ្ជាគំរូ</DropdownLink>
                                            <DropdownLink :href="route('admin.template-subjects.index')" :active="route().current('admin.template-subjects.*')">មុខវិជ្ជាគំរូ</DropdownLink>
                                            <DropdownLink :href="route('admin.template-curricula.index')" :active="route().current('admin.template-curricula.*')">កម្មវិធីសិក្សាគំរូ</DropdownLink>
                                            <DropdownLink :href="route('admin.template-periods.index')" :active="route().current('admin.template-periods.*')">ម៉ោងសិក្សាគំរូ</DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                                
                                <!-- Admin Dropdown -->
                                <div class="hidden sm:flex sm:items-center relative ml-2" v-if="$page.props.auth.user.role === 'super_admin'">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150" :class="{ 'text-gray-900 font-semibold border-b-2 border-indigo-400': route().current('admin.*') }">
                                                    គ្រប់គ្រងប្រព័ន្ធ
                                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <DropdownLink :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                                                គ្រប់គ្រងគណនីសាលា
                                            </DropdownLink>
                                            <DropdownLink :href="route('admin.audits.index')" :active="route().current('admin.audits.*')">
                                                ប្រវត្តិសកម្មភាព
                                            </DropdownLink>
                                            <DropdownLink :href="route('admin.health.view')" :active="route().current('admin.health.*')">
                                                សុខភាពប្រព័ន្ធ
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Notifications Dropdown -->
                            <div class="hidden sm:flex sm:items-center sm:ms-6 relative">
                                <Dropdown align="right" width="80">
                                    <template #trigger>
                                        <button class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none transition ease-in-out duration-150">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                            <span v-if="unreadNotifications.length > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full">
                                                {{ unreadNotifications.length }}
                                            </span>
                                        </button>
                                    </template>
                                    <template #content>
                                        <div class="px-4 py-2 font-medium text-sm text-gray-700 bg-gray-50 border-b">សារជូនដំណឹង</div>
                                        <div class="max-h-80 overflow-y-auto">
                                            <template v-if="unreadNotifications.length > 0">
                                                <div v-for="notif in unreadNotifications" :key="notif.id" class="block w-full px-4 py-3 text-start border-b transition duration-150 ease-in-out hover:bg-blue-50 cursor-pointer">
                                                    <div class="font-bold text-sm text-gray-800">{{ notif.data.title }}</div>
                                                    <div class="text-xs text-gray-600 mt-1 leading-relaxed">{{ notif.data.message }}</div>
                                                </div>
                                            </template>
                                            <div v-else class="px-4 py-4 text-sm text-gray-500 text-center">
                                                គ្មានសារថ្មីទេ
                                            </div>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('school-profile.edit')"
                                        >
                                            ព័ត៌មានទូទៅសាលា
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            គណនីខ្ញុំ
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            ចាកចេញ
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            ផ្ទាំងដើម
                        </ResponsiveNavLink>
                        <template v-if="$page.props.auth.user.role === 'school_admin'">
                            <ResponsiveNavLink
                                :href="route('teachers.index')"
                                :active="route().current('teachers.*')"
                            >
                                គ្រូបង្រៀន
                            </ResponsiveNavLink>

                            <!-- Mobile Dropdown Replacements as headers -->
                            <div class="border-t border-gray-200 pt-2 pb-1 mt-2">
                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">កំណត់</div>
                                <ResponsiveNavLink :href="route('subjects.index')" :active="route().current('subjects.*')" class="pl-8">មុខវិជ្ជា</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('curricula.index')" :active="route().current('curricula.*')" class="pl-8">កម្មវិធីសិក្សា</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('classes.index')" :active="route().current('classes.*')" class="pl-8">ថ្នាក់រៀន</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('rooms.index')" :active="route().current('rooms.*')" class="pl-8">បន្ទប់រៀន</ResponsiveNavLink>
                            </div>

                            <div class="border-t border-gray-200 pt-2 pb-1 mt-2">
                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">កាលវិភាគ</div>
                                <ResponsiveNavLink :href="route('teaching-assignments.index')" :active="route().current('teaching-assignments.*')" class="pl-8">បែងចែកភារកិច្ចបង្រៀន</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('teacher-availabilities.index')" :active="route().current('teacher-availabilities.*')" class="pl-8">កំណត់ម៉ោងទំនេរគ្រូ</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('timetables.index')" :active="route().current('timetables.*')" class="pl-8">រៀបចំកាលវិភាគគ្រូបង្រៀន</ResponsiveNavLink>
                            </div>

                            <div class="border-t border-gray-200 pt-2 pb-1 mt-2">
                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">គ្រប់គ្រងអវត្តមាន</div>
                                <ResponsiveNavLink :href="route('teacher-leaves.index')" :active="route().current('teacher-leaves.*')" class="pl-8">សុំច្បាប់</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('substitute-assignments.index')" :active="route().current('substitute-assignments.*')" class="pl-8">គ្រូជំនួស</ResponsiveNavLink>
                            </div>

                            <div class="border-t border-gray-200 pt-2 pb-1 mt-2">
                                <ResponsiveNavLink :href="route('reports.index')" :active="route().current('reports.*')">
                                    របាយការណ៍
                                </ResponsiveNavLink>
                            </div>
                        </template>
                        
                        <div v-if="$page.props.auth.user.role === 'super_admin'" class="border-t border-gray-200 pt-2 pb-1 mt-2">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">គ្រប់គ្រងទិន្នន័យគំរូ</div>
                            <ResponsiveNavLink :href="route('admin.template-subject-groups.index')" :active="route().current('admin.template-subject-groups.*')" class="pl-8">
                                កម្រងមុខវិជ្ជាគំរូ
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.template-subjects.index')" :active="route().current('admin.template-subjects.*')" class="pl-8">
                                មុខវិជ្ជាគំរូ
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.template-curricula.index')" :active="route().current('admin.template-curricula.*')" class="pl-8">
                                កម្មវិធីសិក្សាគំរូ
                            </ResponsiveNavLink>
                        </div>

                        <div v-if="$page.props.auth.user.role === 'super_admin'" class="border-t border-gray-200 pt-2 pb-1 mt-2">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">គ្រប់គ្រងប្រព័ន្ធ</div>
                            <ResponsiveNavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')" class="pl-8">
                                គ្រប់គ្រងសាលា
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.audits.index')" :active="route().current('admin.audits.*')" class="pl-8">
                                ប្រវត្តិសកម្មភាព
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.health.view')" :active="route().current('admin.health.*')" class="pl-8">
                                សុខភាពប្រព័ន្ធ
                            </ResponsiveNavLink>
                        </div>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('school-profile.edit')">
                                ព័ត៌មានទូទៅសាលា
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('profile.edit')">
                                គណនីខ្ញុំ
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                ចាកចេញ
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <!-- Global Toast Notification -->
            <transition name="toast-slide">
                <div v-if="showToast" class="fixed bottom-5 right-5 z-50 flex items-center w-full max-w-sm p-4 space-x-3 text-gray-500 bg-white rounded-lg shadow-xl border" :class="toastType === 'error' ? 'border-red-100' : 'border-green-100'" role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg" :class="toastType === 'error' ? 'text-red-500 bg-red-100' : 'text-green-500 bg-green-100'">
                        <svg v-if="toastType === 'success'" class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                        <svg v-else class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>
                        </svg>
                    </div>
                    <div class="ml-3 text-sm font-normal break-words">{{ toastMessage }}</div>
                    <button type="button" @click="showToast = false" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8">
                        <span class="sr-only">Close</span>
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
            </transition>
        </div>
    </div>
</template>

<style scoped>
.toast-slide-enter-active,
.toast-slide-leave-active {
    transition: all 0.3s ease;
}
.toast-slide-enter-from,
.toast-slide-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>

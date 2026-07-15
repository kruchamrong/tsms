<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    classes: Object,
    grades: Array,
    curricula: Array,
    rooms: Array,
    shifts: Array,
    teachers: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const grade_id = ref(props.filters?.grade_id || '');
const shift_id = ref(props.filters?.shift_id || '');

watch([search, grade_id, shift_id], () => {
    router.get(route('classes.index'), {
        search: search.value,
        grade_id: grade_id.value,
        shift_id: shift_id.value,
    }, { preserveState: true, replace: true });
});

const updateClass = (schoolClass) => {
    router.put(route('classes.update', schoolClass.id), schoolClass, {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            alert('បរាជ័យក្នុងការកែប្រែ។ សូមពិនិត្យមើលទិន្នន័យឡើងវិញ។');
            router.reload({ only: ['classes'] });
        }
    });
};

const deleteClass = (schoolClass) => {
    if (confirm('តើអ្នកពិតជាចង់លុបថ្នាក់រៀននេះមែនទេ?')) {
        router.delete(route('classes.destroy', schoolClass.id), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};
</script>

<template>
    <Head title="ថ្នាក់រៀន" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">បញ្ជីថ្នាក់រៀន</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 space-y-4 sm:space-y-0">
                            <h3 class="text-lg font-medium">គ្រប់គ្រងថ្នាក់រៀន</h3>
                            <div class="flex items-center gap-2">
                                <select v-model="grade_id" class="border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="">គ្រប់កម្រិតថ្នាក់</option>
                                    <option v-for="grade in grades" :key="grade.id" :value="grade.id">{{ grade.name }}</option>
                                </select>
                                <select v-model="shift_id" class="border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="">គ្រប់វេនគោល</option>
                                    <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
                                </select>
                                <input type="text" v-model="search" placeholder="ស្វែងរកលេខកូដថ្នាក់..." class="border-gray-300 rounded-md shadow-sm text-sm" />
                                <Link :href="route('classes.create')" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition">
                                    បន្ថែមថ្មី
                                </Link>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-[1100px] w-full divide-y divide-gray-200 table-fixed">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">កម្រិតថ្នាក់</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-36">ឈ្មោះថ្នាក់</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[180px]">កម្មវិធីសិក្សា</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-36">វេនគោល</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-36">សិស្សសរុប</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[200px]">គ្រូបន្ទុកថ្នាក់</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr v-for="schoolClass in classes.data" :key="schoolClass.id" class="hover:bg-gray-50 transition-colors group">
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700 font-medium">
                                            {{ schoolClass.grade?.name }}
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <input type="text" v-model="schoolClass.class_code" @change="updateClass(schoolClass)" spellcheck="false" class="w-full bg-transparent border border-transparent hover:bg-white hover:border-gray-300 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded px-2 py-1.5 text-sm font-medium text-gray-900 transition-all placeholder-gray-400">
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <select v-model="schoolClass.curriculum_id" @change="updateClass(schoolClass)" class="w-full bg-transparent border border-transparent hover:bg-white hover:border-gray-300 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded px-2 py-1.5 text-sm text-gray-700 transition-all cursor-pointer">
                                                <option :value="null" class="text-gray-400">-- មិនទាន់កំណត់ --</option>
                                                <option v-for="curriculum in curricula" :key="curriculum.id" :value="curriculum.id">
                                                    {{ curriculum.name }}
                                                </option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <select v-model="schoolClass.shift_id" @change="updateClass(schoolClass)" class="w-full bg-transparent border border-transparent hover:bg-white hover:border-gray-300 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded px-2 py-1.5 text-sm text-gray-700 transition-all cursor-pointer">
                                                <option :value="null" class="text-gray-400">-- មិនកំណត់ --</option>
                                                <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                                    {{ shift.name }}
                                                </option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5 group-hover:bg-white group-hover:border-gray-300 border border-transparent focus-within:bg-white focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 rounded px-2 py-1 transition-all">
                                                <input type="number" v-model="schoolClass.student_count" @change="updateClass(schoolClass)" spellcheck="false" class="w-16 bg-transparent border-0 p-0 text-sm text-right focus:ring-0 text-gray-900 font-medium placeholder-gray-400">
                                                <span class="text-sm text-gray-500 select-none">នាក់</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <SearchableSelect 
                                                v-model="schoolClass.homeroom_teacher_id" 
                                                :options="teachers" 
                                                labelKey="khmer_name" 
                                                valueKey="id" 
                                                placeholder="-- មិនទាន់កំណត់ --" 
                                                @update:modelValue="updateClass(schoolClass)"
                                                :transparent="true"
                                            />
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-center">
                                            <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <Link :href="route('classes.edit', schoolClass.id)" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-full hover:bg-blue-50 transition-colors" title="កែតម្រូវលម្អិត">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </Link>
                                                <button @click="deleteClass(schoolClass)" class="text-red-600 hover:text-red-800 p-1.5 rounded-full hover:bg-red-50 transition-colors" title="លុប">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="classes.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            មិនមានទិន្នន័យ
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6 flex justify-between items-center" v-if="classes.links && classes.links.length > 3">
                            <div class="text-sm text-gray-500">
                                បង្ហាញពី {{ classes.from }} ដល់ {{ classes.to }} នៃ {{ classes.total }} ថ្នាក់រៀនសរុប
                            </div>
                            <div class="flex flex-wrap shadow-sm rounded-md">
                                <template v-for="(link, p) in classes.links" :key="p">
                                    <div v-if="link.url === null" class="px-4 py-2 text-sm text-gray-400 bg-white border border-gray-300 first:rounded-l-md last:rounded-r-md" v-html="link.label" />
                                    <Link v-else :href="link.url" class="px-4 py-2 text-sm border focus:outline-none transition-colors first:rounded-l-md last:rounded-r-md" :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'" v-html="link.label" preserve-scroll />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

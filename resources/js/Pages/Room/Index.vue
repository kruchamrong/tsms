<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    rooms: Object,
    allClasses: Array,
    buildings: Array,
    roomTypes: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const buildingFilter = ref(props.filters?.building || '');
const sort = ref(props.filters?.sort || 'room_name');
const direction = ref(props.filters?.direction || 'asc');

watch([search, buildingFilter, sort, direction], () => {
    router.get(route('rooms.index'), {
        search: search.value,
        building: buildingFilter.value,
        sort: sort.value,
        direction: direction.value,
    }, { preserveState: true, replace: true });
});

const toggleSort = (field) => {
    if (sort.value === field) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = field;
        direction.value = 'asc';
    }
};

const updateRoom = (room) => {
    router.put(route('rooms.update', room.id), room, {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            alert('បរាជ័យក្នុងការកែប្រែ។ សូមពិនិត្យមើលទិន្នន័យឡើងវិញ។');
            router.reload({ only: ['rooms'] });
        }
    });
};

const deleteRoom = (id) => {
    if (confirm('តើអ្នកពិតជាចង់លុបបន្ទប់នេះមែនទេ?')) {
        router.delete(route('rooms.destroy', id), {
            preserveScroll: true
        });
    }
};

// --- Assign Classes Modal Logic ---
const showModal = ref(false);
const activeRoom = ref(null);
const selectedClassIds = ref([]);

const openModal = (room) => {
    activeRoom.value = room;
    selectedClassIds.value = room.school_classes.map(c => c.id);
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    activeRoom.value = null;
    selectedClassIds.value = [];
};

const toggleClassSelection = (classId) => {
    const index = selectedClassIds.value.indexOf(classId);
    if (index > -1) {
        selectedClassIds.value.splice(index, 1);
    } else {
        selectedClassIds.value.push(classId);
    }
};

const sortedAvailableClasses = computed(() => {
    if (!activeRoom.value) return [];
    
    const filtered = props.allClasses.filter(c => !c.room_id || c.room_id === activeRoom.value.id);
    
    return filtered.sort((a, b) => {
        const gradeA = parseInt(a.class_code) || 0;
        const gradeB = parseInt(b.class_code) || 0;
        
        if (gradeA !== gradeB) {
            return gradeB - gradeA; // Descending order (12 down to 7)
        }
        
        // If same grade, sort alphabetically ascending
        return a.class_code.localeCompare(b.class_code);
    });
});

const form = useForm({
    class_ids: []
});

const saveClasses = () => {
    if (!activeRoom.value) return;
    
    const form = useForm({
        class_ids: selectedClassIds.value
    });
    form.post(route('rooms.assign-classes', activeRoom.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        }
    });
};

const removeClass = (room, classId) => {
    if (confirm('តើអ្នកពិតជាចង់ដកថ្នាក់នេះចេញពីបន្ទប់មែនទេ?')) {
        const updatedClassIds = room.school_classes.filter(c => c.id !== classId).map(c => c.id);
        router.post(route('rooms.assign-classes', room.id), {
            class_ids: updatedClassIds
        }, {
            preserveScroll: true
        });
    }
};

</script>

<template>
    <Head title="បន្ទប់រៀន" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">បញ្ជីបន្ទប់រៀន</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 space-y-4 sm:space-y-0">
                            <h3 class="text-lg font-medium">គ្រប់គ្រងបន្ទប់រៀន</h3>
                            <div class="flex items-center gap-2">
                                <select v-model="buildingFilter" class="border-gray-300 rounded-md shadow-sm text-sm py-2">
                                    <option value="">គ្រប់អគារ</option>
                                    <option v-for="b in buildings" :key="b" :value="b">{{ b }}</option>
                                </select>
                                <input type="text" v-model="search" placeholder="ស្វែងរកឈ្មោះបន្ទប់..." class="border-gray-300 rounded-md shadow-sm text-sm" />
                                <Link :href="route('rooms.create')" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition">
                                    បន្ថែមថ្មី
                                </Link>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-[1000px] w-full divide-y divide-gray-200 table-fixed">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" @click="toggleSort('building')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 select-none w-32">
                                            អគារ <span v-if="sort === 'building'">{{ direction === 'asc' ? '↑' : '↓' }}</span>
                                        </th>
                                        <th scope="col" @click="toggleSort('room_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 select-none w-48">
                                            ឈ្មោះបន្ទប់ <span v-if="sort === 'room_name'">{{ direction === 'asc' ? '↑' : '↓' }}</span>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-40">ចំនួនកៅអី</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ប្រភេទបន្ទប់ និង ថ្នាក់រៀន</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-40">សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="room in rooms.data" :key="room.id" class="even:bg-gray-50 hover:bg-gray-100 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="text" v-model="room.building" @change="updateRoom(room)" spellcheck="false" placeholder="-" class="w-24 border-0 rounded p-1 text-sm transition-colors bg-transparent hover:bg-black/5 focus:ring-1 focus:ring-blue-500 text-gray-900">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="text" v-model="room.room_name" @change="updateRoom(room)" spellcheck="false" class="w-full border-0 rounded p-1 text-sm font-medium transition-colors bg-transparent hover:bg-black/5 focus:ring-1 focus:ring-blue-500 text-gray-900">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-1">
                                                <input type="number" v-model="room.capacity" @change="updateRoom(room)" spellcheck="false" class="w-20 border-0 rounded p-1 text-sm transition-colors bg-transparent hover:bg-black/5 focus:ring-1 focus:ring-blue-500 text-gray-900">
                                                <span class="text-gray-500 text-sm">កៅអី</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <input type="text" v-model="room.room_type" @change="updateRoom(room)" spellcheck="false" list="roomTypesListIndex" class="w-36 border-0 rounded p-1 text-sm transition-colors bg-transparent hover:bg-black/5 focus:ring-1 focus:ring-blue-500 text-gray-900">
                                                <datalist id="roomTypesListIndex">
                                                    <option value="បន្ទប់រៀនធម្មតា">បន្ទប់រៀនធម្មតា</option>
                                                    <option value="បន្ទប់ពិសោធន៍">បន្ទប់ពិសោធន៍</option>
                                                    <option value="បន្ទប់កុំព្យូទ័រ">បន្ទប់កុំព្យូទ័រ</option>
                                                    <option value="បណ្ណាល័យ">បណ្ណាល័យ</option>
                                                    <option value="ទីលានកីឡា">ទីលានកីឡា</option>
                                                    <option value="បន្ទប់សិល្បៈ">បន្ទប់សិល្បៈ</option>
                                                    <option value="ផ្សេងៗ">ផ្សេងៗ</option>
                                                    <option v-for="rt in roomTypes" :key="rt" :value="rt">{{ rt }}</option>
                                                </datalist>
                                                
                                                <div class="flex flex-wrap items-center gap-2 border-l pl-3 border-gray-200 min-h-[28px]">
                                                    <span v-for="cls in room.school_classes" :key="cls.id" class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 text-xs font-medium px-2 py-0.5 rounded">
                                                        {{ cls.class_code }}
                                                        <button type="button" @click="removeClass(room, cls.id)" class="text-blue-400 hover:text-red-500 hover:bg-blue-100 rounded-full p-0.5 transition-colors focus:outline-none" title="ដកចេញ">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <button @click="openModal(room)" class="text-blue-500 hover:text-blue-700 p-2 rounded hover:bg-blue-50 transition-colors" title="រៀបចំបញ្ចូលថ្នាក់រៀន">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </button>
                                                <button @click="deleteRoom(room.id)" class="text-red-500 hover:text-red-700 p-2 rounded hover:bg-red-50 transition-colors" title="លុប">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="rooms.data.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            មិនមានទិន្នន័យ
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4" v-if="rooms.links && rooms.links.length > 3">
                            <div class="flex flex-wrap -mb-1">
                                <template v-for="(link, p) in rooms.links" :key="p">
                                    <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded" v-html="link.label" />
                                    <Link v-else :href="link.url" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-blue-500 text-white hover:bg-blue-600': link.active }" v-html="link.label" />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Assigning Classes -->
        <Modal :show="showModal" @close="closeModal" maxWidth="xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900">
                        បញ្ចូលថ្នាក់រៀនក្នុងបន្ទប់៖ <span class="font-bold text-blue-600">{{ activeRoom?.room_name }}</span>
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4">
                    <div v-if="sortedAvailableClasses.length === 0" class="text-gray-500 text-center py-4">
                        គ្មានថ្នាក់រៀនទំនេរសម្រាប់ជ្រើសរើសទេ (ថ្នាក់ទាំងអស់មានបន្ទប់អស់ហើយ)។
                    </div>
                    
                    <div v-else class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-2">
                        <button 
                            v-for="cls in sortedAvailableClasses" 
                            :key="cls.id"
                            type="button"
                            @click="toggleClassSelection(cls.id)"
                            :class="[
                                'py-2 px-1 text-sm rounded border transition-colors',
                                selectedClassIds.includes(cls.id) 
                                    ? 'bg-blue-600 border-blue-600 text-white font-medium shadow-sm' 
                                    : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-blue-300'
                            ]"
                        >
                            {{ cls.class_code }}
                        </button>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">បោះបង់</SecondaryButton>
                    <PrimaryButton @click="saveClasses" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        រក្សាទុក
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>

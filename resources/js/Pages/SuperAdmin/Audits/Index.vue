<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    audits: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const eventFilter = ref(props.filters.event || '');

let searchTimeout;
watch([search, eventFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.audits.index'),
            { search: search.value, event: eventFilter.value },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300);
});

const viewingAudit = ref(null);
const showModal = ref(false);

const viewDetails = (audit) => {
    viewingAudit.value = audit;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => { viewingAudit.value = null; }, 300);
};

const formatJson = (val) => {
    if (!val) return 'គ្មានទិន្នន័យ';
    return JSON.stringify(val, null, 2);
};

</script>

<template>
    <Head title="Audit Trails" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">ប្រវត្តិសកម្មភាព (Audit Trails)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search & Filters -->
                <div class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4 bg-white/80 backdrop-blur-md p-4 rounded-2xl shadow-sm border border-gray-100">
                    <div class="w-full sm:w-1/3 relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input v-model="search" type="text" placeholder="ស្វែងរកតាមឈ្មោះ, អ៊ីមែល, Event..." class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-xl leading-5 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all duration-300" />
                    </div>

                    <div class="w-full sm:w-1/4">
                        <select v-model="eventFilter" class="block w-full py-2 px-3 border border-gray-200 bg-gray-50 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all duration-300">
                            <option value="">គ្រប់សកម្មភាពទាំងអស់</option>
                            <option value="created">បង្កើតថ្មី (Created)</option>
                            <option value="updated">កែប្រែ (Updated)</option>
                            <option value="deleted">លុប (Deleted)</option>
                            <option value="restored">ស្តារឡើងវិញ (Restored)</option>
                        </select>
                    </div>
                </div>

                <!-- Main Card -->
                <div class="bg-white/90 backdrop-blur-xl overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">កាលបរិច្ឆេទ</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">អ្នកអនុវត្ត</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">សកម្មភាព</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ប្រភេទកូដ (Model)</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">សកម្មភាពបន្ថែម</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="audit in audits.data" :key="audit.id" class="hover:bg-indigo-50/30 transition-colors duration-200 group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ audit.created_at }}</div>
                                        <div class="text-xs text-gray-500">{{ audit.created_at_human }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                                {{ audit.user_name.charAt(0) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ audit.user_name }}</div>
                                                <div class="text-xs text-gray-500">{{ audit.ip_address }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full shadow-sm"
                                            :class="{
                                                'bg-green-100 text-green-800 border border-green-200': audit.event === 'created',
                                                'bg-yellow-100 text-yellow-800 border border-yellow-200': audit.event === 'updated',
                                                'bg-red-100 text-red-800 border border-red-200': audit.event === 'deleted',
                                                'bg-blue-100 text-blue-800 border border-blue-200': audit.event === 'restored',
                                            }">
                                            {{ audit.event_khmer }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono bg-gray-50/50 rounded-md">
                                        {{ audit.auditable_type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="viewDetails(audit)" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-all duration-300 transform group-hover:scale-105 inline-flex items-center gap-1 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            លម្អិត
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="audits.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                            មិនមានទិន្នន័យសវនកម្ម (Audits) ទេ
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
                        <Pagination :links="audits.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Details Modal -->
        <Modal :show="showModal" @close="closeModal" maxWidth="3xl">
            <div class="bg-white rounded-2xl overflow-hidden shadow-2xl">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                        </svg>
                        ព័ត៌មានលម្អិតនៃសកម្មភាព
                    </h3>
                    <button @click="closeModal" class="text-white/80 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="p-6" v-if="viewingAudit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div class="text-sm text-gray-500 mb-1">អ្នកអនុវត្ត (User)</div>
                            <div class="font-semibold text-gray-900">{{ viewingAudit.user_name }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div class="text-sm text-gray-500 mb-1">ប្រភេទសកម្មភាព (Event)</div>
                            <div class="font-semibold text-indigo-600 uppercase">{{ viewingAudit.event }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div class="text-sm text-gray-500 mb-1">ប្រភេទកូដ (Model)</div>
                            <div class="font-mono text-sm bg-gray-200 px-2 py-1 rounded inline-block">{{ viewingAudit.auditable_type }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div class="text-sm text-gray-500 mb-1">IP Address & User Agent</div>
                            <div class="font-semibold text-sm">{{ viewingAudit.ip_address }}</div>
                            <div class="text-xs text-gray-400 mt-1 truncate" :title="viewingAudit.user_agent">{{ viewingAudit.user_agent }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Old Values -->
                        <div>
                            <div class="flex items-center gap-2 mb-2 text-red-600 font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                ទិន្នន័យចាស់ (Old Values)
                            </div>
                            <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto shadow-inner h-64 border border-gray-700 custom-scrollbar">
                                <pre class="text-red-400 text-sm font-mono leading-relaxed">{{ formatJson(viewingAudit.old_values) }}</pre>
                            </div>
                        </div>

                        <!-- New Values -->
                        <div>
                            <div class="flex items-center gap-2 mb-2 text-green-600 font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                ទិន្នន័យថ្មី (New Values)
                            </div>
                            <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto shadow-inner h-64 border border-gray-700 custom-scrollbar">
                                <pre class="text-green-400 text-sm font-mono leading-relaxed">{{ formatJson(viewingAudit.new_values) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end border-t border-gray-100">
                    <button @click="closeModal" class="px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        បិទ (Close)
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(31, 41, 55, 0.5); 
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(107, 114, 128, 0.8); 
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 1); 
}
</style>

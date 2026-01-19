<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    tournaments: Array,
    stats: Object,
    filters: Object,
});

const selectedIds = ref([]);
const showDeleteModal = ref(false);
const showCleanupModal = ref(false);
const cleanupDays = ref(30);

const bulkDeleteForm = useForm({
    tournament_ids: [],
    confirm: false,
});

const cleanupForm = useForm({
    days: 30,
    confirm: false,
});

const isAllSelected = computed(() => {
    return props.tournaments.length > 0 && selectedIds.value.length === props.tournaments.length;
});

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.tournaments.map(t => t.id);
    }
};

const toggleSelect = (id) => {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) {
        selectedIds.value.splice(index, 1);
    } else {
        selectedIds.value.push(id);
    }
};

const openDeleteModal = () => {
    if (selectedIds.value.length === 0) return;
    showDeleteModal.value = true;
};

const confirmBulkDelete = () => {
    bulkDeleteForm.tournament_ids = selectedIds.value;
    bulkDeleteForm.confirm = true;
    bulkDeleteForm.delete(route('maintenance.bulk-delete'), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedIds.value = [];
        },
    });
};

const confirmCleanup = () => {
    cleanupForm.days = cleanupDays.value;
    cleanupForm.confirm = true;
    cleanupForm.delete(route('maintenance.cleanup'), {
        onSuccess: () => {
            showCleanupModal.value = false;
        },
    });
};

const applyFilter = (filterType, value) => {
    router.get(route('maintenance.index'), {
        ...props.filters,
        [filterType]: value || undefined,
    }, { preserveState: true });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusClass = (status) => {
    return {
        draft: 'badge-info',
        active: 'badge-success',
        completed: 'badge-primary',
        cancelled: 'badge-warning',
    }[status] || 'badge-info';
};
</script>

<template>
    <Head title="Maintenance" />
    
    <AppLayout>
        <!-- Header -->
        <div class="page-header">
            <div>
                <Link :href="route('tournaments.index')" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to tournaments
                </Link>
                <h1 class="page-title">🔧 Maintenance</h1>
                <p class="text-gray-400 mt-1">Clean up old tournament data</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="card p-4 text-center">
                <div class="text-3xl font-bold text-white">{{ stats.total }}</div>
                <div class="text-sm text-gray-400">Total</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-3xl font-bold text-blue-400">{{ stats.draft }}</div>
                <div class="text-sm text-gray-400">Draft</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-3xl font-bold text-green-400">{{ stats.active }}</div>
                <div class="text-sm text-gray-400">Active</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-3xl font-bold text-primary">{{ stats.completed }}</div>
                <div class="text-sm text-gray-400">Completed</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-3xl font-bold text-yellow-400">{{ stats.old_completed }}</div>
                <div class="text-sm text-gray-400">&gt;30 days old</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-3">
                <select 
                    :value="filters.status || ''"
                    @change="applyFilter('status', $event.target.value)"
                    class="input w-auto"
                >
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                
                <select 
                    :value="filters.older_than || ''"
                    @change="applyFilter('older_than', $event.target.value)"
                    class="input w-auto"
                >
                    <option value="">Any Age</option>
                    <option value="7">Older than 7 days</option>
                    <option value="30">Older than 30 days</option>
                    <option value="90">Older than 90 days</option>
                    <option value="180">Older than 180 days</option>
                </select>
            </div>
            
            <div class="flex gap-3">
                <button 
                    @click="showCleanupModal = true"
                    class="btn btn-secondary"
                    :disabled="stats.old_completed === 0"
                >
                    🧹 Auto Cleanup
                </button>
                <button 
                    @click="openDeleteModal"
                    class="btn btn-danger"
                    :disabled="selectedIds.length === 0"
                >
                    🗑️ Delete Selected ({{ selectedIds.length }})
                </button>
            </div>
        </div>

        <!-- Tournament List -->
        <div class="card overflow-hidden">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-12">
                            <input 
                                type="checkbox" 
                                :checked="isAllSelected"
                                @change="toggleSelectAll"
                                class="w-4 h-4"
                            />
                        </th>
                        <th>Tournament</th>
                        <th>Status</th>
                        <th>Teams</th>
                        <th>Matches</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tournament in tournaments" :key="tournament.id">
                        <td>
                            <input 
                                type="checkbox" 
                                :checked="selectedIds.includes(tournament.id)"
                                @change="toggleSelect(tournament.id)"
                                class="w-4 h-4"
                            />
                        </td>
                        <td>
                            <Link 
                                :href="route('tournaments.show', tournament.id)" 
                                class="text-white hover:text-primary"
                            >
                                {{ tournament.name }}
                            </Link>
                        </td>
                        <td>
                            <span :class="['badge', getStatusClass(tournament.status)]">
                                {{ tournament.status }}
                            </span>
                        </td>
                        <td>{{ tournament.teams_count }}</td>
                        <td>{{ tournament.matches_count }}</td>
                        <td class="text-gray-400">{{ formatDate(tournament.created_at) }}</td>
                    </tr>
                    <tr v-if="tournaments.length === 0">
                        <td colspan="6" class="text-center text-gray-400 py-8">
                            No tournaments found matching the filters.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showDeleteModal" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
                <div class="card p-6 w-full max-w-md animate-slide-up">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 mx-auto bg-red-500/20 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Delete Tournaments?</h3>
                        <p class="text-gray-400">
                            You are about to permanently delete <strong class="text-white">{{ selectedIds.length }}</strong> tournament(s).
                            This action cannot be undone.
                        </p>
                    </div>
                    
                    <div class="flex gap-3">
                        <button @click="showDeleteModal = false" class="btn btn-secondary flex-1">
                            Cancel
                        </button>
                        <button 
                            @click="confirmBulkDelete" 
                            class="btn btn-danger flex-1"
                            :disabled="bulkDeleteForm.processing"
                        >
                            {{ bulkDeleteForm.processing ? 'Deleting...' : 'Delete All' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Cleanup Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showCleanupModal" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
                <div class="card p-6 w-full max-w-md animate-slide-up">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 mx-auto bg-yellow-500/20 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Auto Cleanup</h3>
                        <p class="text-gray-400">
                            Delete all <strong class="text-white">completed</strong> tournaments older than:
                        </p>
                    </div>
                    
                    <div class="mb-6">
                        <select v-model="cleanupDays" class="input w-full">
                            <option :value="7">7 days</option>
                            <option :value="30">30 days</option>
                            <option :value="60">60 days</option>
                            <option :value="90">90 days</option>
                            <option :value="180">180 days</option>
                        </select>
                        <p class="mt-2 text-sm text-gray-500">
                            This will delete {{ stats.old_completed }} tournament(s) that are currently older than 30 days.
                        </p>
                    </div>
                    
                    <div class="flex gap-3">
                        <button @click="showCleanupModal = false" class="btn btn-secondary flex-1">
                            Cancel
                        </button>
                        <button 
                            @click="confirmCleanup" 
                            class="btn btn-warning flex-1"
                            :disabled="cleanupForm.processing"
                        >
                            {{ cleanupForm.processing ? 'Cleaning...' : 'Cleanup Now' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.text-primary {
    color: var(--color-primary);
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}
</style>

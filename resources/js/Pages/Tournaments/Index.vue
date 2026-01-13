<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    tournaments: Array,
});

const getStatusBadgeClass = (status) => {
    const classes = {
        draft: 'badge-info',
        active: 'badge-success',
        completed: 'badge-primary',
        cancelled: 'badge-warning',
    };
    return classes[status] || 'badge-info';
};
</script>

<template>
    <AppLayout>
        <Head title="Tournaments" />
        
        <div class="page-header">
            <h1 class="page-title">Tournaments</h1>
            <Link :href="route('tournaments.create')" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Tournament
            </Link>
        </div>
        
        <div v-if="tournaments.length === 0" class="card p-12 text-center">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-primary/20 to-pink-600/20 flex items-center justify-center">
                <svg class="w-10 h-10 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">No tournaments yet</h3>
            <p class="text-gray-400 mb-6">Create your first tournament to get started.</p>
            <Link :href="route('tournaments.create')" class="btn btn-primary">
                Create Tournament
            </Link>
        </div>
        
        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="tournament in tournaments"
                :key="tournament.id"
                :href="route('tournaments.show', tournament.id)"
                class="card p-6 block animate-slide-up"
            >
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-xl font-semibold text-white">{{ tournament.name }}</h3>
                    <span :class="['badge', getStatusBadgeClass(tournament.status)]">
                        {{ tournament.status }}
                    </span>
                </div>
                
                <p v-if="tournament.description" class="text-gray-400 text-sm mb-4 line-clamp-2">
                    {{ tournament.description }}
                </p>
                
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ tournament.teams_count }} teams
                    </div>
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ tournament.matches_count }} matches
                    </div>
                </div>
            </Link>
        </div>
    </AppLayout>
</template>

<style scoped>
.text-primary {
    color: var(--color-primary);
}
</style>

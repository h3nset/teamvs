<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    tournament: Object,
    matchesByRound: Object,
});

const getStatusClass = (status) => {
    return {
        scheduled: 'badge-info',
        in_progress: 'badge-warning',
        completed: 'badge-success',
        cancelled: 'badge-primary',
    }[status] || 'badge-info';
};
</script>

<template>
    <AppLayout>
        <Head :title="`Matches - ${tournament.name}`" />
        
        <div class="page-header">
            <div>
                <Link :href="route('tournaments.show', tournament.id)" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to {{ tournament.name }}
                </Link>
                <h1 class="page-title">Match Schedule</h1>
            </div>
        </div>
        
        <div v-for="(matches, round) in matchesByRound" :key="round" class="mb-8">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-3">
                <span class="px-3 py-1 rounded-full bg-primary/20 text-primary text-sm">Round {{ round }}</span>
                <span class="text-sm text-gray-500">{{ matches.length }} matches</span>
            </h2>
            
            <div class="grid gap-4 md:grid-cols-2">
                <Link 
                    v-for="match in matches" 
                    :key="match.id"
                    :href="route('tournaments.matches.show', [tournament.id, match.id])"
                    class="card p-5 block hover:bg-white/5 transition"
                >
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs text-gray-500">Match #{{ match.match_number }}</span>
                        <span :class="['badge text-xs', getStatusClass(match.status)]">
                            {{ match.status.replace('_', ' ') }}
                        </span>
                    </div>
                    
                    <div class="match-card">
                        <!-- Home Pair -->
                        <div class="text-right">
                            <div class="flex items-center justify-end gap-2 mb-1">
                                <span class="font-medium text-white">
                                    {{ match.home_pair?.player1_name }} & {{ match.home_pair?.player2_name }}
                                </span>
                                <div class="w-3 h-6 rounded" :style="{ backgroundColor: match.home_pair?.team?.color }"></div>
                            </div>
                            <div class="text-xs text-gray-500">{{ match.home_pair?.team?.name }}</div>
                        </div>
                        
                        <!-- Score -->
                        <div class="text-center px-4">
                            <div v-if="match.scores && match.scores.length > 0" class="flex items-center justify-center gap-3">
                                <span class="text-2xl font-bold text-white">
                                    {{ match.scores.reduce((a, s) => a + s.home_score, 0) }}
                                </span>
                                <span class="text-gray-600">-</span>
                                <span class="text-2xl font-bold text-white">
                                    {{ match.scores.reduce((a, s) => a + s.away_score, 0) }}
                                </span>
                            </div>
                            <div v-else class="text-gray-500 text-sm">vs</div>
                        </div>
                        
                        <!-- Away Pair -->
                        <div class="text-left">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-3 h-6 rounded" :style="{ backgroundColor: match.away_pair?.team?.color }"></div>
                                <span class="font-medium text-white">
                                    {{ match.away_pair?.player1_name }} & {{ match.away_pair?.player2_name }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500">{{ match.away_pair?.team?.name }}</div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.text-primary {
    color: var(--color-primary);
}
.bg-primary\/20 {
    background-color: rgba(233, 69, 96, 0.2);
}
</style>

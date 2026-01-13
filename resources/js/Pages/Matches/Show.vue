<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    tournament: Object,
    match: Object,
    summary: Object,
});

const startMatch = () => {
    router.post(route('tournaments.matches.start', [props.tournament.id, props.match.id]));
};

const completeMatch = () => {
    if (confirm('Mark this match as completed?')) {
        router.post(route('tournaments.matches.complete', [props.tournament.id, props.match.id]));
    }
};

const getStatusClass = (status) => {
    return {
        scheduled: 'badge-info',
        in_progress: 'badge-warning',
        completed: 'badge-success',
    }[status] || 'badge-info';
};
</script>

<template>
    <AppLayout>
        <Head :title="`Match #${match.match_number} - ${tournament.name}`" />
        
        <div class="page-header">
            <div>
                <Link :href="route('tournaments.matches.index', tournament.id)" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to matches
                </Link>
                <h1 class="page-title">Match #{{ match.match_number }}</h1>
            </div>
            
            <span :class="['badge', getStatusClass(match.status)]">
                {{ match.status.replace('_', ' ') }}
            </span>
        </div>
        
        <!-- Match Card -->
        <div class="card p-8 mb-6">
            <div class="text-center text-sm text-gray-500 mb-6">
                Round {{ match.round_number }}
            </div>
            
            <div class="grid grid-cols-3 gap-8 items-center">
                <!-- Home Team -->
                <div class="text-right">
                    <div class="flex items-center justify-end gap-3 mb-2">
                        <div>
                            <div class="text-xl font-bold text-white">
                                {{ summary?.home_pair?.name }}
                            </div>
                            <div class="text-sm text-gray-400">
                                {{ summary?.home_pair?.team }}
                            </div>
                        </div>
                        <div class="w-4 h-12 rounded" :style="{ backgroundColor: summary?.home_pair?.team_color }"></div>
                    </div>
                </div>
                
                <!-- Score -->
                <div class="text-center">
                    <div class="flex items-center justify-center gap-4">
                        <span class="score-display" :class="{ 'text-green-400': summary?.winner === 'home' }">
                            {{ summary?.total?.home || 0 }}
                        </span>
                        <span class="text-3xl text-gray-600">-</span>
                        <span class="score-display" :class="{ 'text-green-400': summary?.winner === 'away' }">
                            {{ summary?.total?.away || 0 }}
                        </span>
                    </div>
                    
                    <!-- Set scores -->
                    <div v-if="summary?.sets?.length" class="mt-4 flex justify-center gap-4">
                        <div v-for="(set, idx) in summary.sets" :key="idx" class="text-sm">
                            <div class="text-gray-500 text-xs mb-1">Set {{ set.set }}</div>
                            <div class="font-mono">{{ set.home }} - {{ set.away }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Away Team -->
                <div class="text-left">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-4 h-12 rounded" :style="{ backgroundColor: summary?.away_pair?.team_color }"></div>
                        <div>
                            <div class="text-xl font-bold text-white">
                                {{ summary?.away_pair?.name }}
                            </div>
                            <div class="text-sm text-gray-400">
                                {{ summary?.away_pair?.team }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex justify-center gap-4">
            <button 
                v-if="match.status === 'scheduled'"
                @click="startMatch"
                class="btn btn-success btn-lg"
            >
                Start Match
            </button>
            
            <Link 
                v-if="match.status === 'scheduled' || match.status === 'in_progress'"
                :href="route('tournaments.matches.score', [tournament.id, match.id])"
                class="btn btn-primary btn-lg"
            >
                Enter Scores
            </Link>
            
            <button 
                v-if="match.status === 'in_progress'"
                @click="completeMatch"
                class="btn btn-secondary btn-lg"
            >
                Complete Match
            </button>
        </div>
    </AppLayout>
</template>

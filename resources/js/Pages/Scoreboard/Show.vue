<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    tournament: Object,
    standings: Array,
    activeMatches: Array,
    recentlyCompleted: Array,
    upcomingMatches: Array,
});

const lastUpdated = ref(new Date());

// Auto-refresh every 30 seconds
let refreshInterval;
onMounted(() => {
    refreshInterval = setInterval(() => {
        window.location.reload();
    }, 30000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<template>
    <AppLayout>
        <Head :title="`Scoreboard - ${tournament.name}`" />
        
        <div class="page-header">
            <div>
                <Link :href="route('tournaments.show', tournament.id)" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to tournament
                </Link>
                <h1 class="page-title">Live Scoreboard</h1>
            </div>
            
            <Link :href="route('tournaments.tv', tournament.id)" target="_blank" class="btn btn-secondary">
                Open TV Mode
            </Link>
        </div>
        
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Active Matches -->
            <div class="lg:col-span-2">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    Live Matches
                </h2>
                
                <div v-if="activeMatches.length === 0" class="card p-8 text-center text-gray-400">
                    No matches in progress
                </div>
                
                <div v-else class="space-y-4">
                    <div v-for="match in activeMatches" :key="match.id" class="card p-6 animate-pulse-glow">
                        <div class="match-card">
                            <div class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-lg font-semibold text-white">
                                        {{ match.home_pair?.player1_name }} & {{ match.home_pair?.player2_name }}
                                    </span>
                                    <div class="w-3 h-8 rounded" :style="{ backgroundColor: match.home_pair?.team?.color }"></div>
                                </div>
                                <div class="text-sm text-gray-500">{{ match.home_pair?.team?.name }}</div>
                            </div>
                            
                            <div class="text-center">
                                <div class="flex items-center justify-center gap-4">
                                    <span class="text-4xl font-bold text-white">
                                        {{ match.scores?.reduce((a, s) => a + s.home_score, 0) || 0 }}
                                    </span>
                                    <span class="text-2xl text-gray-600">-</span>
                                    <span class="text-4xl font-bold text-white">
                                        {{ match.scores?.reduce((a, s) => a + s.away_score, 0) || 0 }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-2">
                                    Round {{ match.round_number }}
                                </div>
                            </div>
                            
                            <div class="text-left">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-8 rounded" :style="{ backgroundColor: match.away_pair?.team?.color }"></div>
                                    <span class="text-lg font-semibold text-white">
                                        {{ match.away_pair?.player1_name }} & {{ match.away_pair?.player2_name }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500">{{ match.away_pair?.team?.name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recently Completed -->
                <div v-if="recentlyCompleted.length > 0" class="mt-8">
                    <h2 class="text-lg font-semibold text-white mb-4">Recently Completed</h2>
                    <div class="space-y-3">
                        <div v-for="match in recentlyCompleted" :key="match.id" class="card p-4 opacity-75">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-4 rounded" :style="{ backgroundColor: match.home_pair?.team?.color }"></div>
                                    <span class="text-sm text-white">{{ match.home_pair?.player1_name }} & {{ match.home_pair?.player2_name }}</span>
                                </div>
                                <div class="text-lg font-bold">
                                    {{ match.scores?.reduce((a, s) => a + s.home_score, 0) }} - {{ match.scores?.reduce((a, s) => a + s.away_score, 0) }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-white">{{ match.away_pair?.player1_name }} & {{ match.away_pair?.player2_name }}</span>
                                    <div class="w-2 h-4 rounded" :style="{ backgroundColor: match.away_pair?.team?.color }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Standings -->
            <div>
                <h2 class="text-lg font-semibold text-white mb-4">Team Standings</h2>
                <div class="card overflow-hidden">
                    <div v-for="(team, index) in standings" :key="team.id" class="p-4 border-b border-white/10 last:border-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl font-bold text-gray-600">#{{ index + 1 }}</span>
                                <div class="w-3 h-8 rounded" :style="{ backgroundColor: team.color }"></div>
                                <div>
                                    <div class="font-semibold text-white">{{ team.name }}</div>
                                    <div class="text-xs text-gray-500">{{ team.total_wins }} wins</div>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-primary">{{ team.total_points }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Upcoming -->
                <div v-if="upcomingMatches.length > 0" class="mt-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Up Next</h2>
                    <div class="space-y-2">
                        <div v-for="match in upcomingMatches" :key="match.id" class="card p-3 text-sm">
                            <div class="flex items-center justify-between text-gray-400">
                                <span>{{ match.home_pair?.team?.name }}</span>
                                <span>vs</span>
                                <span>{{ match.away_pair?.team?.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Last updated -->
        <div class="mt-8 text-center text-sm text-gray-500">
            Auto-refreshes every 30 seconds
        </div>
    </AppLayout>
</template>

<style scoped>
.text-primary {
    color: var(--color-primary);
}
</style>

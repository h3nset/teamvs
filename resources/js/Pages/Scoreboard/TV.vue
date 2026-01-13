<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    tournament: Object,
    standings: Array,
    activeMatches: Array,
});

// Auto-refresh every 10 seconds for TV mode
let refreshInterval;
onMounted(() => {
    refreshInterval = setInterval(() => {
        window.location.reload();
    }, 10000);
    
    // Request fullscreen on click
    document.addEventListener('click', requestFullscreen);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
    document.removeEventListener('click', requestFullscreen);
});

const requestFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen?.();
    }
};
</script>

<template>
    <Head :title="`TV - ${tournament.name}`" />
    
    <div class="tv-mode min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">{{ tournament.name }}</h1>
            <div class="text-xl text-gray-400">Live Scoreboard</div>
        </div>
        
        <div class="grid gap-8 lg:grid-cols-2">
            <!-- Active Matches -->
            <div>
                <h2 class="text-2xl font-semibold text-white mb-6 flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-green-400 animate-pulse"></span>
                    Live Matches
                </h2>
                
                <div v-if="activeMatches.length === 0" class="card p-12 text-center">
                    <div class="text-3xl text-gray-500">No matches in progress</div>
                </div>
                
                <div v-else class="space-y-6">
                    <div v-for="match in activeMatches" :key="match.id" class="card p-8 animate-pulse-glow">
                        <div class="grid grid-cols-3 items-center gap-6">
                            <!-- Home -->
                            <div class="text-right">
                                <div class="flex items-center justify-end gap-4 mb-2">
                                    <div>
                                        <div class="text-2xl font-bold text-white">
                                            {{ match.home_pair?.player1_name }}
                                        </div>
                                        <div class="text-2xl font-bold text-white">
                                            {{ match.home_pair?.player2_name }}
                                        </div>
                                    </div>
                                    <div class="w-4 h-16 rounded" :style="{ backgroundColor: match.home_pair?.team?.color }"></div>
                                </div>
                                <div class="text-lg text-gray-500">{{ match.home_pair?.team?.name }}</div>
                            </div>
                            
                            <!-- Score -->
                            <div class="text-center">
                                <div class="flex items-center justify-center gap-6">
                                    <span class="score-display-tv text-white">
                                        {{ match.scores?.reduce((a, s) => a + s.home_score, 0) || 0 }}
                                    </span>
                                    <span class="text-6xl text-gray-600">-</span>
                                    <span class="score-display-tv text-white">
                                        {{ match.scores?.reduce((a, s) => a + s.away_score, 0) || 0 }}
                                    </span>
                                </div>
                                <div class="text-lg text-gray-500 mt-4">
                                    Round {{ match.round_number }}
                                </div>
                            </div>
                            
                            <!-- Away -->
                            <div class="text-left">
                                <div class="flex items-center gap-4 mb-2">
                                    <div class="w-4 h-16 rounded" :style="{ backgroundColor: match.away_pair?.team?.color }"></div>
                                    <div>
                                        <div class="text-2xl font-bold text-white">
                                            {{ match.away_pair?.player1_name }}
                                        </div>
                                        <div class="text-2xl font-bold text-white">
                                            {{ match.away_pair?.player2_name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-lg text-gray-500">{{ match.away_pair?.team?.name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Standings -->
            <div>
                <h2 class="text-2xl font-semibold text-white mb-6">Team Standings</h2>
                
                <div class="card overflow-hidden">
                    <div 
                        v-for="(team, index) in standings" 
                        :key="team.id" 
                        class="p-6 border-b border-white/10 last:border-0"
                        :class="{ 'bg-gradient-to-r from-yellow-500/10 to-transparent': index === 0 }"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <span class="text-4xl font-bold" :class="index === 0 ? 'text-yellow-400' : 'text-gray-600'">
                                    #{{ index + 1 }}
                                </span>
                                <div class="w-4 h-12 rounded" :style="{ backgroundColor: team.color }"></div>
                                <div>
                                    <div class="text-2xl font-bold text-white">{{ team.name }}</div>
                                    <div class="text-lg text-gray-500">{{ team.total_wins }} wins • {{ team.matches_played }} matches</div>
                                </div>
                            </div>
                            <div class="text-5xl font-bold text-primary">{{ team.total_points }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center text-gray-600">
            Click anywhere to enter fullscreen • Auto-refreshes every 10 seconds
        </div>
    </div>
</template>

<style scoped>
.tv-mode {
    font-size: 1.25rem;
}

.score-display-tv {
    font-size: 8rem;
    font-weight: 700;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}

.text-primary {
    color: var(--color-primary);
}

@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 10px rgba(233, 69, 96, 0.3);
    }
    50% {
        box-shadow: 0 0 30px rgba(233, 69, 96, 0.6);
    }
}

.animate-pulse-glow {
    animation: pulse-glow 2s ease-in-out infinite;
}
</style>

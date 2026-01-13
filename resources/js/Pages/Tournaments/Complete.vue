<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import ShareCard from '@/Components/ShareCard.vue';

const props = defineProps({
    tournament: Object,
    stats: Object,
    awards: Object,
});

const showConfetti = ref(false);
const showShareModal = ref(false);

onMounted(() => {
    // Trigger confetti after a short delay
    setTimeout(() => {
        showConfetti.value = true;
    }, 500);
});
</script>

<template>
    <Head :title="`Champion - ${tournament.name}`" />
    
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 relative overflow-hidden">
        <!-- Confetti Animation -->
        <div v-if="showConfetti" class="confetti-container">
            <div v-for="i in 50" :key="i" class="confetti" :style="{ left: `${Math.random() * 100}%`, animationDelay: `${Math.random() * 3}s` }"></div>
        </div>
        
        <div class="max-w-4xl mx-auto px-6 py-12 relative z-10">
            <!-- Back link -->
            <Link :href="route('tournaments.show', tournament.id)" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-8">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to tournament
            </Link>
            
            <!-- Tournament Complete Header -->
            <div class="text-center mb-12">
                <div class="text-6xl mb-4">🏆</div>
                <h1 class="text-3xl font-bold text-white mb-2">{{ tournament.name }}</h1>
                <p class="text-gray-400">Tournament Complete</p>
            </div>
            
            <!-- Winning Team -->
            <div v-if="awards.winning_team" class="card p-8 mb-8 text-center winner-card" :style="{ borderColor: awards.winning_team.color }">
                <div class="text-lg text-gray-400 mb-2">🏆 Champion Team</div>
                <div class="flex items-center justify-center gap-4 mb-4">
                    <div class="w-6 h-16 rounded" :style="{ backgroundColor: awards.winning_team.color }"></div>
                    <h2 class="text-5xl font-bold text-white">{{ awards.winning_team.name }}</h2>
                    <div class="w-6 h-16 rounded" :style="{ backgroundColor: awards.winning_team.color }"></div>
                </div>
                <div class="text-2xl font-bold text-primary">{{ awards.winning_team.total_points }} Points</div>
                <div class="text-gray-400 mt-2">
                    {{ awards.winning_team.wins }} Wins • {{ awards.winning_team.matches_played }} Matches Played
                </div>
            </div>
            
            <!-- Stats Summary -->
            <div class="grid gap-4 md:grid-cols-4 mb-8">
                <div class="card p-4 text-center">
                    <div class="text-3xl font-bold text-white">{{ stats.completed_matches }}</div>
                    <div class="text-sm text-gray-400">Matches Played</div>
                </div>
                <div class="card p-4 text-center">
                    <div class="text-3xl font-bold text-white">{{ stats.total_points_scored }}</div>
                    <div class="text-sm text-gray-400">Total Points</div>
                </div>
                <div class="card p-4 text-center">
                    <div class="text-3xl font-bold text-white">{{ stats.average_points_per_match }}</div>
                    <div class="text-sm text-gray-400">Avg/Match</div>
                </div>
                <div class="card p-4 text-center">
                    <div class="text-3xl font-bold text-green-400">100%</div>
                    <div class="text-sm text-gray-400">Complete</div>
                </div>
            </div>
            
            <!-- Awards -->
            <h2 class="text-xl font-semibold text-white mb-4 text-center">🏅 Special Awards</h2>
            <div class="grid gap-4 md:grid-cols-2 mb-8">
                <!-- MVP Pair -->
                <div v-if="awards.mvp_pair" class="card p-6 award-card">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-3xl">🏆</span>
                        <div>
                            <div class="text-lg font-bold text-white">MVP Pair</div>
                            <div class="text-xs text-gray-400">Highest contribution</div>
                        </div>
                    </div>
                    <div class="text-xl font-semibold text-white">{{ awards.mvp_pair.name }}</div>
                    <div class="flex items-center gap-2 mt-2">
                        <div class="w-3 h-3 rounded" :style="{ backgroundColor: awards.mvp_pair.team_color }"></div>
                        <span class="text-gray-400">{{ awards.mvp_pair.team_name }}</span>
                    </div>
                    <div class="text-sm text-gray-500 mt-2">{{ awards.mvp_pair.points_for }} pts • {{ awards.mvp_pair.win_rate }}% win rate</div>
                </div>
                
                <!-- Top Performer -->
                <div v-if="awards.top_performer" class="card p-6 award-card">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-3xl">⭐</span>
                        <div>
                            <div class="text-lg font-bold text-white">Top Performer</div>
                            <div class="text-xs text-gray-400">Highest win rate</div>
                        </div>
                    </div>
                    <div class="text-xl font-semibold text-white">{{ awards.top_performer.name }}</div>
                    <div class="flex items-center gap-2 mt-2">
                        <div class="w-3 h-3 rounded" :style="{ backgroundColor: awards.top_performer.team_color }"></div>
                        <span class="text-gray-400">{{ awards.top_performer.team_name }}</span>
                    </div>
                    <div class="text-sm text-gray-500 mt-2">{{ awards.top_performer.wins }}/{{ awards.top_performer.matches_played }} wins • {{ awards.top_performer.win_rate }}%</div>
                </div>
                
                <!-- Ironman -->
                <div v-if="awards.ironman" class="card p-6 award-card">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-3xl">💪</span>
                        <div>
                            <div class="text-lg font-bold text-white">Ironman</div>
                            <div class="text-xs text-gray-400">Most matches played</div>
                        </div>
                    </div>
                    <div class="text-xl font-semibold text-white">{{ awards.ironman.name }}</div>
                    <div class="flex items-center gap-2 mt-2">
                        <div class="w-3 h-3 rounded" :style="{ backgroundColor: awards.ironman.team_color }"></div>
                        <span class="text-gray-400">{{ awards.ironman.team_name }}</span>
                    </div>
                    <div class="text-sm text-gray-500 mt-2">{{ awards.ironman.matches_played }} matches played</div>
                </div>
                
                <!-- Clutch Pair -->
                <div v-if="awards.clutch_pair" class="card p-6 award-card">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-3xl">🎯</span>
                        <div>
                            <div class="text-lg font-bold text-white">Clutch Pair</div>
                            <div class="text-xs text-gray-400">Most close wins</div>
                        </div>
                    </div>
                    <div class="text-xl font-semibold text-white">{{ awards.clutch_pair.name }}</div>
                    <div class="flex items-center gap-2 mt-2">
                        <div class="w-3 h-3 rounded" :style="{ backgroundColor: awards.clutch_pair.team_color }"></div>
                        <span class="text-gray-400">{{ awards.clutch_pair.team_name }}</span>
                    </div>
                    <div class="text-sm text-gray-500 mt-2">{{ awards.clutch_pair.close_wins }} close victories</div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-center gap-4 flex-wrap">
                <button @click="showShareModal = true" class="btn btn-success">
                    📤 Share Result
                </button>
                <Link :href="route('tournaments.statistics', tournament.id)" class="btn btn-secondary">
                    View Full Statistics
                </Link>
                <Link :href="route('tournaments.leaderboard', tournament.id)" class="btn btn-primary">
                    View Leaderboard
                </Link>
            </div>
        </div>
        
        <!-- Share Modal -->
        <ShareCard 
            v-if="showShareModal && awards.winning_team"
            type="tournament"
            :data="awards.winning_team"
            :tournament="tournament"
            @close="showShareModal = false"
        />
    </div>
</template>

<style scoped>
.text-primary {
    color: var(--color-primary);
}

.winner-card {
    border: 2px solid;
    background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
}

.award-card {
    background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
    border: 1px solid rgba(255,255,255,0.1);
}

.confetti-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    overflow: hidden;
    z-index: 50;
}

.confetti {
    position: absolute;
    width: 10px;
    height: 10px;
    top: -10px;
    animation: fall 3s linear infinite;
}

.confetti:nth-child(odd) {
    background: var(--color-primary);
    border-radius: 50%;
}

.confetti:nth-child(even) {
    background: gold;
    clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
}

@keyframes fall {
    0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(720deg);
        opacity: 0;
    }
}
</style>

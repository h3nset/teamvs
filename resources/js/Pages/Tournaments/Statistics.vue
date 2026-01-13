<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    tournament: Object,
    stats: Object,
    highlights: Object,
    leaderboard: Array,
});
</script>

<template>
    <AppLayout>
        <Head :title="`Statistics - ${tournament.name}`" />
        
        <div class="page-header">
            <div>
                <Link :href="route('tournaments.show', tournament.id)" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to tournament
                </Link>
                <h1 class="page-title">Tournament Statistics</h1>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-4 mb-8">
            <div class="card p-5 text-center">
                <div class="text-4xl font-bold text-primary mb-1">{{ stats.total_matches }}</div>
                <div class="text-sm text-gray-400">Total Matches</div>
            </div>
            <div class="card p-5 text-center">
                <div class="text-4xl font-bold text-green-400 mb-1">{{ stats.completed_matches }}</div>
                <div class="text-sm text-gray-400">Completed</div>
            </div>
            <div class="card p-5 text-center">
                <div class="text-4xl font-bold text-white mb-1">{{ stats.total_points_scored }}</div>
                <div class="text-sm text-gray-400">Total Points</div>
            </div>
            <div class="card p-5 text-center">
                <div class="text-4xl font-bold text-white mb-1">{{ stats.average_points_per_match }}</div>
                <div class="text-sm text-gray-400">Avg Points/Match</div>
            </div>
        </div>
        
        <!-- Progress -->
        <div class="card p-5 mb-8">
            <div class="flex justify-between text-sm text-gray-400 mb-2">
                <span>Tournament Progress</span>
                <span>{{ stats.completion_rate }}%</span>
            </div>
            <div class="w-full bg-white/10 rounded-full h-3">
                <div 
                    class="bg-gradient-to-r from-primary to-pink-500 h-3 rounded-full transition-all"
                    :style="{ width: `${stats.completion_rate}%` }"
                ></div>
            </div>
        </div>
        
        <!-- Match Highlights -->
        <h2 class="text-lg font-semibold text-white mb-4">Match Highlights</h2>
        <div class="grid gap-4 md:grid-cols-3 mb-8">
            <!-- Highest Scoring -->
            <div v-if="highlights.highest_scoring" class="card p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">🔥</span>
                    <span class="text-sm text-gray-400">Highest Scoring</span>
                </div>
                <div class="text-3xl font-bold text-white mb-2">
                    {{ highlights.highest_scoring.total_points }} pts
                </div>
                <div class="text-sm text-gray-400">
                    {{ highlights.highest_scoring.home_pair.name }} vs {{ highlights.highest_scoring.away_pair.name }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ highlights.highest_scoring.home_score }} - {{ highlights.highest_scoring.away_score }}
                </div>
            </div>
            
            <!-- Closest Match -->
            <div v-if="highlights.closest_match" class="card p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">⚡</span>
                    <span class="text-sm text-gray-400">Closest Match</span>
                </div>
                <div class="text-3xl font-bold text-white mb-2">
                    {{ highlights.closest_match.point_diff }} pt diff
                </div>
                <div class="text-sm text-gray-400">
                    {{ highlights.closest_match.home_pair.name }} vs {{ highlights.closest_match.away_pair.name }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ highlights.closest_match.home_score }} - {{ highlights.closest_match.away_score }}
                </div>
            </div>
            
            <!-- Biggest Blowout -->
            <div v-if="highlights.biggest_blowout" class="card p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">💪</span>
                    <span class="text-sm text-gray-400">Biggest Win</span>
                </div>
                <div class="text-3xl font-bold text-white mb-2">
                    +{{ highlights.biggest_blowout.point_diff }} pts
                </div>
                <div class="text-sm text-gray-400">
                    {{ highlights.biggest_blowout.home_pair.name }} vs {{ highlights.biggest_blowout.away_pair.name }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ highlights.biggest_blowout.home_score }} - {{ highlights.biggest_blowout.away_score }}
                </div>
            </div>
        </div>
        
        <!-- Top Pairs -->
        <h2 class="text-lg font-semibold text-white mb-4">Top Pairs</h2>
        <div class="card overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr class="text-left text-sm text-gray-400">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Pair</th>
                        <th class="px-4 py-3">Team</th>
                        <th class="px-4 py-3 text-center">Played</th>
                        <th class="px-4 py-3 text-center">W-L</th>
                        <th class="px-4 py-3 text-center">Win %</th>
                        <th class="px-4 py-3 text-right">Points</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(pair, index) in leaderboard.slice(0, 10)" :key="pair.id" class="border-t border-white/10">
                        <td class="px-4 py-3 text-gray-500">{{ index + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-white">{{ pair.name }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded" :style="{ backgroundColor: pair.team_color }"></div>
                                <span class="text-gray-400">{{ pair.team_name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-400">{{ pair.matches_played }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-green-400">{{ pair.wins }}</span>
                            <span class="text-gray-600">-</span>
                            <span class="text-red-400">{{ pair.losses }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-400">{{ pair.win_rate }}%</td>
                        <td class="px-4 py-3 text-right font-bold text-white">{{ pair.points_for }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 text-center">
            <Link :href="route('tournaments.leaderboard', tournament.id)" class="btn btn-secondary">
                View Full Leaderboard
            </Link>
        </div>
    </AppLayout>
</template>

<style scoped>
.text-primary {
    color: var(--color-primary);
}
</style>

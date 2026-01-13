<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    tournament: Object,
    teams: Array,
    leaderboard: Array,
    stats: Object,
});

const selectedTeam = ref(null);
const sortBy = ref('points_for');
const sortDesc = ref(true);

const filteredLeaderboard = computed(() => {
    let data = [...props.leaderboard];
    
    if (selectedTeam.value) {
        data = data.filter(p => p.team_id === selectedTeam.value);
    }
    
    data.sort((a, b) => {
        const aVal = a[sortBy.value];
        const bVal = b[sortBy.value];
        return sortDesc.value ? bVal - aVal : aVal - bVal;
    });
    
    return data;
});

const toggleSort = (field) => {
    if (sortBy.value === field) {
        sortDesc.value = !sortDesc.value;
    } else {
        sortBy.value = field;
        sortDesc.value = true;
    }
};

const getSortIcon = (field) => {
    if (sortBy.value !== field) return '';
    return sortDesc.value ? '↓' : '↑';
};
</script>

<template>
    <AppLayout>
        <Head :title="`Leaderboard - ${tournament.name}`" />
        
        <div class="page-header">
            <div>
                <Link :href="route('tournaments.show', tournament.id)" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to tournament
                </Link>
                <h1 class="page-title">Leaderboard</h1>
            </div>
        </div>
        
        <!-- Team Filters -->
        <div class="flex gap-2 mb-6 flex-wrap">
            <button 
                @click="selectedTeam = null"
                :class="['btn btn-sm', !selectedTeam ? 'btn-primary' : 'btn-secondary']"
            >
                All Teams
            </button>
            <button 
                v-for="team in teams" 
                :key="team.id"
                @click="selectedTeam = team.id"
                :class="['btn btn-sm', selectedTeam === team.id ? 'btn-primary' : 'btn-secondary']"
                :style="selectedTeam === team.id ? { backgroundColor: team.color } : {}"
            >
                {{ team.name }}
            </button>
        </div>
        
        <!-- Leaderboard Table -->
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white/5">
                        <tr class="text-left text-sm text-gray-400">
                            <th class="px-4 py-3 w-12">#</th>
                            <th class="px-4 py-3">Pair</th>
                            <th class="px-4 py-3">Team</th>
                            <th class="px-4 py-3 text-center cursor-pointer hover:text-white" @click="toggleSort('matches_played')">
                                Played {{ getSortIcon('matches_played') }}
                            </th>
                            <th class="px-4 py-3 text-center cursor-pointer hover:text-white" @click="toggleSort('wins')">
                                Wins {{ getSortIcon('wins') }}
                            </th>
                            <th class="px-4 py-3 text-center cursor-pointer hover:text-white" @click="toggleSort('losses')">
                                Losses {{ getSortIcon('losses') }}
                            </th>
                            <th class="px-4 py-3 text-center cursor-pointer hover:text-white" @click="toggleSort('win_rate')">
                                Win % {{ getSortIcon('win_rate') }}
                            </th>
                            <th class="px-4 py-3 text-right cursor-pointer hover:text-white" @click="toggleSort('points_for')">
                                Points {{ getSortIcon('points_for') }}
                            </th>
                            <th class="px-4 py-3 text-right cursor-pointer hover:text-white" @click="toggleSort('points_against')">
                                Against {{ getSortIcon('points_against') }}
                            </th>
                            <th class="px-4 py-3 text-right cursor-pointer hover:text-white" @click="toggleSort('point_diff')">
                                +/- {{ getSortIcon('point_diff') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(pair, index) in filteredLeaderboard" 
                            :key="pair.id" 
                            class="border-t border-white/10 hover:bg-white/5"
                        >
                            <td class="px-4 py-3">
                                <span :class="[
                                    'font-bold',
                                    index === 0 ? 'text-yellow-400' : 
                                    index === 1 ? 'text-gray-300' : 
                                    index === 2 ? 'text-amber-600' : 'text-gray-500'
                                ]">
                                    {{ index + 1 }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-white">{{ pair.name }}</div>
                                <div class="text-xs text-gray-500">{{ pair.player1 }} & {{ pair.player2 }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded" :style="{ backgroundColor: pair.team_color }"></div>
                                    <span class="text-gray-400">{{ pair.team_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-400">{{ pair.matches_played }}</td>
                            <td class="px-4 py-3 text-center text-green-400 font-medium">{{ pair.wins }}</td>
                            <td class="px-4 py-3 text-center text-red-400">{{ pair.losses }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="pair.win_rate >= 50 ? 'text-green-400' : 'text-gray-400'">
                                    {{ pair.win_rate }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-white">{{ pair.points_for }}</td>
                            <td class="px-4 py-3 text-right text-gray-400">{{ pair.points_against }}</td>
                            <td class="px-4 py-3 text-right">
                                <span :class="pair.point_diff > 0 ? 'text-green-400' : pair.point_diff < 0 ? 'text-red-400' : 'text-gray-400'">
                                    {{ pair.point_diff > 0 ? '+' : '' }}{{ pair.point_diff }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Stats Summary -->
        <div class="mt-8 grid gap-4 md:grid-cols-4">
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-white">{{ stats.total_matches }}</div>
                <div class="text-sm text-gray-400">Total Matches</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-green-400">{{ stats.completed_matches }}</div>
                <div class="text-sm text-gray-400">Completed</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-white">{{ stats.total_points_scored }}</div>
                <div class="text-sm text-gray-400">Total Points</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-white">{{ stats.average_points_per_match }}</div>
                <div class="text-sm text-gray-400">Avg/Match</div>
            </div>
        </div>
    </AppLayout>
</template>

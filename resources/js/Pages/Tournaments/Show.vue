<script setup>
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    tournament: Object,
    standings: Array,
    scheduleStats: Object,
});

const page = usePage();

const showTeamModal = ref(false);
const showPairModal = ref(false);
const showTokenModal = ref(false);
const selectedTeam = ref(null);
const editingTeam = ref(null);
const editingPair = ref(null);
const tokenCopied = ref(false);

// Get flashed token from session (only available once after creation)
const newTournamentToken = computed(() => page.props.flash?.new_tournament_token);

onMounted(() => {
    // Show token modal if this is a newly created tournament
    if (newTournamentToken.value) {
        showTokenModal.value = true;
    }
});

const copyToken = async () => {
    if (newTournamentToken.value) {
        try {
            // Try modern Clipboard API first (requires HTTPS or localhost)
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(newTournamentToken.value);
            } else {
                // Fallback for HTTP (non-secure context)
                const textArea = document.createElement('textarea');
                textArea.value = newTournamentToken.value;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
            }
            tokenCopied.value = true;
            setTimeout(() => tokenCopied.value = false, 2000);
        } catch (err) {
            console.error('Failed to copy:', err);
            alert('Failed to copy token. Please copy manually: ' + newTournamentToken.value);
        }
    }
};

const teamForm = useForm({
    name: '',
    description: '',
    color: '#3B82F6',
});

const pairForm = useForm({
    player1_name: '',
    player2_name: '',
    display_name: '',
});

const teamColors = [
    '#3B82F6', '#EF4444', '#10B981', '#F59E0B', 
    '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'
];

const canGenerateSchedule = computed(() => {
    return props.tournament.status === 'draft' && 
           props.tournament.teams.length >= 2 &&
           props.tournament.teams.every(t => t.pairs.length >= 1);
});

const matchesByRound = computed(() => {
    if (!props.tournament.matches) return {};
    const grouped = {};
    props.tournament.matches.forEach(match => {
        if (!grouped[match.round_number]) {
            grouped[match.round_number] = [];
        }
        grouped[match.round_number].push(match);
    });
    return grouped;
});

const openAddTeamModal = () => {
    editingTeam.value = null;
    teamForm.reset();
    teamForm.color = teamColors[props.tournament.teams.length % teamColors.length];
    showTeamModal.value = true;
};

const openEditTeamModal = (team) => {
    editingTeam.value = team;
    teamForm.name = team.name;
    teamForm.description = team.description || '';
    teamForm.color = team.color;
    showTeamModal.value = true;
};

const submitTeam = () => {
    if (editingTeam.value) {
        teamForm.put(route('tournaments.teams.update', [props.tournament.id, editingTeam.value.id]), {
            onSuccess: () => {
                showTeamModal.value = false;
                teamForm.reset();
            }
        });
    } else {
        teamForm.post(route('tournaments.teams.store', props.tournament.id), {
            onSuccess: () => {
                showTeamModal.value = false;
                teamForm.reset();
            }
        });
    }
};

const deleteTeam = (team) => {
    if (confirm(`Delete team "${team.name}" and all its pairs?`)) {
        router.delete(route('tournaments.teams.destroy', [props.tournament.id, team.id]));
    }
};

const openAddPairModal = (team) => {
    selectedTeam.value = team;
    editingPair.value = null;
    pairForm.reset();
    showPairModal.value = true;
};

const openEditPairModal = (team, pair) => {
    selectedTeam.value = team;
    editingPair.value = pair;
    pairForm.player1_name = pair.player1_name;
    pairForm.player2_name = pair.player2_name;
    pairForm.display_name = pair.display_name || '';
    showPairModal.value = true;
};

const submitPair = () => {
    if (editingPair.value) {
        pairForm.put(route('tournaments.teams.pairs.update', [props.tournament.id, selectedTeam.value.id, editingPair.value.id]), {
            onSuccess: () => {
                showPairModal.value = false;
                pairForm.reset();
            }
        });
    } else {
        pairForm.post(route('tournaments.teams.pairs.store', [props.tournament.id, selectedTeam.value.id]), {
            onSuccess: () => {
                showPairModal.value = false;
                pairForm.reset();
            }
        });
    }
};

const deletePair = (team, pair) => {
    if (confirm(`Delete pair "${pair.player1_name} & ${pair.player2_name}"?`)) {
        router.delete(route('tournaments.teams.pairs.destroy', [props.tournament.id, team.id, pair.id]));
    }
};

const generateSchedule = () => {
    if (confirm('Generate match schedule? This will replace any existing schedule.')) {
        router.post(route('tournaments.generate-schedule', props.tournament.id));
    }
};

const startTournament = () => {
    if (confirm('Start the tournament? Teams and pairs cannot be modified after starting.')) {
        router.post(route('tournaments.start', props.tournament.id));
    }
};

const completeTournament = () => {
    if (confirm('Mark tournament as completed?')) {
        router.post(route('tournaments.complete', props.tournament.id));
    }
};

const getStatusBadgeClass = (status) => {
    const classes = {
        draft: 'badge-info',
        active: 'badge-success',
        completed: 'badge-primary',
        cancelled: 'badge-warning',
    };
    return classes[status] || 'badge-info';
};

const getMatchStatusClass = (status) => {
    return {
        scheduled: 'text-gray-400',
        in_progress: 'text-yellow-400',
        completed: 'text-green-400',
    }[status] || 'text-gray-400';
};
</script>

<template>
    <AppLayout>
        <Head :title="tournament.name" />
        
        <!-- Header -->
        <div class="page-header">
            <div>
                <Link :href="route('tournaments.index')" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to tournaments
                </Link>
                <div class="flex items-center gap-3">
                    <h1 class="page-title">{{ tournament.name }}</h1>
                    <span :class="['badge', getStatusBadgeClass(tournament.status)]">
                        {{ tournament.status }}
                    </span>
                </div>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap">
                <Link :href="route('tournaments.statistics', tournament.id)" class="btn btn-secondary">
                    📊 Statistics
                </Link>
                <Link :href="route('tournaments.leaderboard', tournament.id)" class="btn btn-secondary">
                    🏆 Leaderboard
                </Link>
                <Link v-if="tournament.status !== 'draft'" :href="route('tournaments.scoreboard', tournament.id)" class="btn btn-secondary">
                    Scoreboard
                </Link>
                <Link v-if="tournament.status !== 'draft'" :href="route('tournaments.tv', tournament.id)" class="btn btn-secondary" target="_blank">
                    TV Mode
                </Link>
                <Link v-if="tournament.status === 'completed'" :href="route('tournaments.complete.show', tournament.id)" class="btn btn-success">
                    🎉 View Winner
                </Link>
            </div>
        </div>
        
        <!-- Action Bar -->
        <div v-if="tournament.status === 'draft'" class="card p-4 mb-6 flex items-center justify-between">
            <div class="text-sm text-gray-400">
                <span v-if="!canGenerateSchedule">
                    Add at least 2 teams with pairs to generate schedule.
                </span>
                <span v-else>
                    Ready to generate schedule with {{ tournament.teams.length }} teams.
                </span>
            </div>
            <div class="flex gap-3">
                <button 
                    @click="generateSchedule"
                    class="btn btn-secondary"
                    :disabled="!canGenerateSchedule"
                >
                    Generate Schedule
                </button>
                <button 
                    v-if="tournament.matches && tournament.matches.length > 0"
                    @click="startTournament"
                    class="btn btn-success"
                >
                    Start Tournament
                </button>
            </div>
        </div>
        
        <div v-if="tournament.status === 'active'" class="card p-4 mb-6 flex items-center justify-between">
            <div class="text-sm text-gray-400">
                Tournament in progress. {{ scheduleStats.total_matches }} matches scheduled.
            </div>
            <button @click="completeTournament" class="btn btn-primary">
                Complete Tournament
            </button>
        </div>
        
        <!-- Teams Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-white">Teams</h2>
                <button 
                    v-if="tournament.status === 'draft'"
                    @click="openAddTeamModal"
                    class="btn btn-sm btn-secondary"
                >
                    + Add Team
                </button>
            </div>
            
            <div v-if="tournament.teams.length === 0" class="card p-8 text-center text-gray-400">
                No teams yet. Add teams to get started.
            </div>
            
            <div v-else class="grid gap-4 md:grid-cols-2">
                <div v-for="team in tournament.teams" :key="team.id" class="card p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-8 rounded" :style="{ backgroundColor: team.color }"></div>
                            <h3 class="text-lg font-semibold text-white">{{ team.name }}</h3>
                        </div>
                        <div v-if="tournament.status === 'draft'" class="flex gap-2">
                            <button @click="openEditTeamModal(team)" class="text-gray-400 hover:text-white">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button @click="deleteTeam(team)" class="text-gray-400 hover:text-red-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Pairs -->
                    <div class="space-y-2">
                        <div v-for="pair in team.pairs" :key="pair.id" class="flex items-center justify-between p-2 rounded bg-white/5">
                            <div>
                                <span class="text-sm text-white">{{ pair.player1_name }}</span>
                                <span class="text-gray-500 mx-2">&</span>
                                <span class="text-sm text-white">{{ pair.player2_name }}</span>
                            </div>
                            <div v-if="tournament.status === 'draft'" class="flex gap-2">
                                <button @click="openEditPairModal(team, pair)" class="text-gray-400 hover:text-white">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button @click="deletePair(team, pair)" class="text-gray-400 hover:text-red-400">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <button 
                            v-if="tournament.status === 'draft'"
                            @click="openAddPairModal(team)"
                            class="w-full p-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded border border-dashed border-gray-700 transition"
                        >
                            + Add Pair
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Standings -->
        <div v-if="standings && standings.length > 0" class="mb-8">
            <h2 class="text-xl font-semibold text-white mb-4">Standings</h2>
            <div class="card overflow-hidden">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Team</th>
                            <th class="text-right">Wins</th>
                            <th class="text-right">Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(team, index) in standings" :key="team.id">
                            <td class="font-semibold">{{ index + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded" :style="{ backgroundColor: team.color }"></div>
                                    {{ team.name }}
                                </div>
                            </td>
                            <td class="text-right">{{ team.total_wins }}</td>
                            <td class="text-right font-semibold text-primary">{{ team.total_points }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Schedule -->
        <div v-if="tournament.matches && tournament.matches.length > 0" class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-white">Match Schedule</h2>
                <Link :href="route('tournaments.matches.index', tournament.id)" class="btn btn-sm btn-secondary">
                    View All Matches
                </Link>
            </div>
            
            <div v-for="(matches, round) in matchesByRound" :key="round" class="mb-6">
                <h3 class="text-sm font-semibold text-gray-400 uppercase mb-3">Round {{ round }}</h3>
                <div class="grid gap-3 md:grid-cols-2">
                    <Link 
                        v-for="match in matches.slice(0, 4)" 
                        :key="match.id"
                        :href="route('tournaments.matches.show', [tournament.id, match.id])"
                        class="card p-4 match-card hover:bg-white/5 transition"
                    >
                        <div class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <span class="text-sm text-white">{{ match.home_pair?.player1_name }} & {{ match.home_pair?.player2_name }}</span>
                                <div class="w-2 h-2 rounded" :style="{ backgroundColor: match.home_pair?.team?.color }"></div>
                            </div>
                            <div class="text-xs text-gray-500">{{ match.home_pair?.team?.name }}</div>
                        </div>
                        
                        <div class="text-center">
                            <div v-if="match.status === 'completed'" class="flex items-center justify-center gap-2">
                                <span class="score-display text-2xl">{{ match.scores?.reduce((a, s) => a + s.home_score, 0) }}</span>
                                <span class="text-gray-500">-</span>
                                <span class="score-display text-2xl">{{ match.scores?.reduce((a, s) => a + s.away_score, 0) }}</span>
                            </div>
                            <div v-else :class="['text-xs uppercase', getMatchStatusClass(match.status)]">
                                {{ match.status.replace('_', ' ') }}
                            </div>
                        </div>
                        
                        <div class="text-left">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded" :style="{ backgroundColor: match.away_pair?.team?.color }"></div>
                                <span class="text-sm text-white">{{ match.away_pair?.player1_name }} & {{ match.away_pair?.player2_name }}</span>
                            </div>
                            <div class="text-xs text-gray-500">{{ match.away_pair?.team?.name }}</div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
        
        <!-- Team Modal -->
        <Teleport to="body">
            <div v-if="showTeamModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card p-6 w-full max-w-md animate-slide-up" @click.stop>
                    <h3 class="text-lg font-semibold text-white mb-4">
                        {{ editingTeam ? 'Edit Team' : 'Add Team' }}
                    </h3>
                    
                    <form @submit.prevent="submitTeam" class="space-y-4">
                        <div>
                            <label class="label">Team Name</label>
                            <input v-model="teamForm.name" type="text" class="input" placeholder="e.g., Team Alpha" required />
                        </div>
                        
                        <div>
                            <label class="label">Description (optional)</label>
                            <textarea v-model="teamForm.description" class="input" rows="2"></textarea>
                        </div>
                        
                        <div>
                            <label class="label">Team Color</label>
                            <div class="flex gap-2 flex-wrap">
                                <button
                                    v-for="color in teamColors"
                                    :key="color"
                                    type="button"
                                    @click="teamForm.color = color"
                                    class="w-8 h-8 rounded-lg transition-transform"
                                    :class="{ 'ring-2 ring-white ring-offset-2 ring-offset-gray-900 scale-110': teamForm.color === color }"
                                    :style="{ backgroundColor: color }"
                                ></button>
                            </div>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="showTeamModal = false" class="btn btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="teamForm.processing">
                                {{ editingTeam ? 'Save Changes' : 'Add Team' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
        
        <!-- Pair Modal -->
        <Teleport to="body">
            <div v-if="showPairModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="card p-6 w-full max-w-md animate-slide-up" @click.stop>
                    <h3 class="text-lg font-semibold text-white mb-4">
                        {{ editingPair ? 'Edit Pair' : 'Add Pair' }} - {{ selectedTeam?.name }}
                    </h3>
                    
                    <form @submit.prevent="submitPair" class="space-y-4">
                        <div>
                            <label class="label">Player 1 Name</label>
                            <input v-model="pairForm.player1_name" type="text" class="input" placeholder="First player name" required />
                        </div>
                        
                        <div>
                            <label class="label">Player 2 Name</label>
                            <input v-model="pairForm.player2_name" type="text" class="input" placeholder="Second player name" required />
                        </div>
                        
                        <div>
                            <label class="label">Display Name (optional)</label>
                            <input v-model="pairForm.display_name" type="text" class="input" placeholder="e.g., The Topspin Twins" />
                            <p class="mt-1 text-xs text-gray-500">Leave blank to use "Player1 & Player2"</p>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="showPairModal = false" class="btn btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="pairForm.processing">
                                {{ editingPair ? 'Save Changes' : 'Add Pair' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
        
        <!-- Access Token Modal (shows after creation) -->
        <Teleport to="body">
            <div v-if="showTokenModal && newTournamentToken" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
                <div class="card p-6 w-full max-w-md animate-slide-up" @click.stop>
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 mx-auto bg-green-500/20 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Tournament Created!</h3>
                        <p class="text-gray-400 text-sm">
                            Save this access token to manage your tournament later.
                            <strong class="text-yellow-400">This is the only time you'll see it!</strong>
                        </p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="label">Your Access Token</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                :value="newTournamentToken" 
                                readonly 
                                class="input font-mono text-sm pr-24"
                            />
                            <button 
                                @click="copyToken"
                                class="absolute right-2 top-1/2 -translate-y-1/2 btn btn-sm"
                                :class="tokenCopied ? 'btn-success' : 'btn-primary'"
                            >
                                {{ tokenCopied ? '✓ Copied!' : 'Copy' }}
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Share this token with co-organizers who need to manage the tournament.
                        </p>
                    </div>
                    
                    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="text-sm text-yellow-200">
                                <strong>Important:</strong> Store this token somewhere safe. 
                                You'll need it to edit teams, start matches, or make changes to the tournament.
                            </div>
                        </div>
                    </div>
                    
                    <button @click="showTokenModal = false" class="btn btn-primary w-full">
                        I've Saved My Token
                    </button>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.text-primary {
    color: var(--color-primary);
}
</style>

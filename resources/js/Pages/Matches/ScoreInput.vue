<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    tournament: Object,
    match: Object,
    pointsPerSet: Number,
});

const currentSet = ref(1);
const homeScore = ref(0);
const awayScore = ref(0);

// Initialize from existing scores
if (props.match.scores && props.match.scores.length > 0) {
    const lastSet = props.match.scores[props.match.scores.length - 1];
    currentSet.value = lastSet.set_number;
    homeScore.value = lastSet.home_score;
    awayScore.value = lastSet.away_score;
}

const form = useForm({
    set_number: currentSet.value,
    home_score: homeScore.value,
    away_score: awayScore.value,
    is_tiebreak: false,
    device_id: localStorage.getItem('device_id') || generateDeviceId(),
});

function generateDeviceId() {
    const id = 'device_' + Math.random().toString(36).substring(2, 15);
    localStorage.setItem('device_id', id);
    return id;
}

const incrementHome = () => {
    if (homeScore.value < props.pointsPerSet) {
        homeScore.value++;
        saveScore();
    }
};

const decrementHome = () => {
    if (homeScore.value > 0) {
        homeScore.value--;
        saveScore();
    }
};

const incrementAway = () => {
    if (awayScore.value < props.pointsPerSet) {
        awayScore.value++;
        saveScore();
    }
};

const decrementAway = () => {
    if (awayScore.value > 0) {
        awayScore.value--;
        saveScore();
    }
};

const saveScore = () => {
    form.set_number = currentSet.value;
    form.home_score = homeScore.value;
    form.away_score = awayScore.value;
    
    form.post(route('tournaments.matches.score.store', [props.tournament.id, props.match.id]), {
        preserveScroll: true,
        onSuccess: () => {
            // Score saved
        }
    });
};

const nextSet = () => {
    currentSet.value++;
    homeScore.value = 0;
    awayScore.value = 0;
};

const prevSet = () => {
    if (currentSet.value > 1) {
        currentSet.value--;
        // Load previous set scores if available
        const prevSetData = props.match.scores?.find(s => s.set_number === currentSet.value);
        if (prevSetData) {
            homeScore.value = prevSetData.home_score;
            awayScore.value = prevSetData.away_score;
        } else {
            homeScore.value = 0;
            awayScore.value = 0;
        }
    }
};

const completeMatch = () => {
    if (confirm('Complete this match? Final scores will be locked.')) {
        router.post(route('tournaments.matches.complete', [props.tournament.id, props.match.id]));
    }
};

const totalHome = computed(() => {
    let total = 0;
    props.match.scores?.forEach(s => {
        if (s.set_number < currentSet.value) {
            total += s.home_score;
        }
    });
    return total + homeScore.value;
});

const totalAway = computed(() => {
    let total = 0;
    props.match.scores?.forEach(s => {
        if (s.set_number < currentSet.value) {
            total += s.away_score;
        }
    });
    return total + awayScore.value;
});
</script>

<template>
    <AppLayout>
        <Head :title="`Score - Match #${match.match_number}`" />
        
        <div class="page-header">
            <Link :href="route('tournaments.matches.show', [tournament.id, match.id])" class="text-gray-400 hover:text-white flex items-center gap-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </Link>
            
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-400">Set {{ currentSet }}</span>
                <div class="flex gap-1">
                    <button @click="prevSet" :disabled="currentSet <= 1" class="btn btn-sm btn-secondary">
                        ← Prev
                    </button>
                    <button @click="nextSet" class="btn btn-sm btn-secondary">
                        Next →
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Score Display -->
        <div class="card p-8 mb-6">
            <div class="text-center text-sm text-gray-500 mb-4">
                Round {{ match.round_number }} • Match #{{ match.match_number }}
            </div>
            
            <div class="grid grid-cols-3 gap-4 items-center">
                <!-- Home Side -->
                <div class="text-center">
                    <div class="flex items-center justify-center gap-2 mb-4">
                        <div class="w-4 h-8 rounded" :style="{ backgroundColor: match.home_pair?.team?.color }"></div>
                        <div class="text-left">
                            <div class="font-semibold text-white text-lg">{{ match.home_pair?.player1_name }}</div>
                            <div class="font-semibold text-white text-lg">{{ match.home_pair?.player2_name }}</div>
                        </div>
                    </div>
                    
                    <!-- Score controls -->
                    <div class="space-y-4">
                        <button 
                            @click="incrementHome"
                            class="w-full py-8 text-6xl font-bold text-white bg-white/10 rounded-xl hover:bg-white/20 active:bg-white/30 transition touch-manipulation"
                        >
                            +
                        </button>
                        
                        <div class="score-display text-7xl text-white py-4">
                            {{ homeScore }}
                        </div>
                        
                        <button 
                            @click="decrementHome"
                            class="w-full py-6 text-4xl font-bold text-gray-400 bg-white/5 rounded-xl hover:bg-white/10 active:bg-white/15 transition touch-manipulation"
                        >
                            −
                        </button>
                    </div>
                </div>
                
                <!-- Center - Total -->
                <div class="text-center flex flex-col items-center justify-center h-full">
                    <div class="text-gray-500 text-sm mb-2">Total</div>
                    <div class="flex items-center gap-4">
                        <span class="text-4xl font-bold text-white">{{ totalHome }}</span>
                        <span class="text-2xl text-gray-600">-</span>
                        <span class="text-4xl font-bold text-white">{{ totalAway }}</span>
                    </div>
                    
                    <div class="mt-6 text-sm text-gray-500">
                        Set {{ currentSet }} Score
                    </div>
                    <div class="text-2xl font-semibold text-gray-300">
                        {{ homeScore }} - {{ awayScore }}
                    </div>
                </div>
                
                <!-- Away Side -->
                <div class="text-center">
                    <div class="flex items-center justify-center gap-2 mb-4">
                        <div class="text-right">
                            <div class="font-semibold text-white text-lg">{{ match.away_pair?.player1_name }}</div>
                            <div class="font-semibold text-white text-lg">{{ match.away_pair?.player2_name }}</div>
                        </div>
                        <div class="w-4 h-8 rounded" :style="{ backgroundColor: match.away_pair?.team?.color }"></div>
                    </div>
                    
                    <!-- Score controls -->
                    <div class="space-y-4">
                        <button 
                            @click="incrementAway"
                            class="w-full py-8 text-6xl font-bold text-white bg-white/10 rounded-xl hover:bg-white/20 active:bg-white/30 transition touch-manipulation"
                        >
                            +
                        </button>
                        
                        <div class="score-display text-7xl text-white py-4">
                            {{ awayScore }}
                        </div>
                        
                        <button 
                            @click="decrementAway"
                            class="w-full py-6 text-4xl font-bold text-gray-400 bg-white/5 rounded-xl hover:bg-white/10 active:bg-white/15 transition touch-manipulation"
                        >
                            −
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex justify-center gap-4">
            <button @click="completeMatch" class="btn btn-success btn-lg">
                Complete Match
            </button>
        </div>
        
        <!-- Saving indicator -->
        <div v-if="form.processing" class="fixed bottom-4 right-4 bg-primary text-white px-4 py-2 rounded-full text-sm flex items-center gap-2">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Saving...
        </div>
    </AppLayout>
</template>

<style scoped>
.touch-manipulation {
    touch-action: manipulation;
}
</style>

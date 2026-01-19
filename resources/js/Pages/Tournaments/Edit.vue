<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    tournament: Object,
});

const form = useForm({
    name: props.tournament.name,
    description: props.tournament.description || '',
    pairs_per_team: props.tournament.pairs_per_team,
    rounds: props.tournament.rounds,
    max_matches_per_pair: props.tournament.max_matches_per_pair,
    points_per_set: props.tournament.points_per_set,
    scoring_mode: props.tournament.scoring_mode || 'unlimited',
});

const submit = () => {
    form.put(route('tournaments.update', props.tournament.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="`Edit ${tournament.name}`" />
        
        <div class="max-w-2xl mx-auto">
            <div class="page-header">
                <div>
                    <Link :href="route('tournaments.show', tournament.id)" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to tournament
                    </Link>
                    <h1 class="page-title">Edit Tournament</h1>
                </div>
            </div>
            
            <form @submit.prevent="submit" class="card p-6 space-y-6">
                <div>
                    <label class="label">Tournament Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="input"
                        required
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
                </div>
                
                <div>
                    <label class="label">Description</label>
                    <textarea
                        v-model="form.description"
                        class="input"
                        rows="3"
                    ></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Pairs per Team</label>
                        <input
                            v-model.number="form.pairs_per_team"
                            type="number"
                            class="input"
                            min="2"
                            max="10"
                            required
                            :disabled="tournament.status !== 'draft'"
                        />
                    </div>
                    
                    <div>
                        <label class="label">Number of Rounds</label>
                        <input
                            v-model.number="form.rounds"
                            type="number"
                            class="input"
                            min="1"
                            max="20"
                            required
                            :disabled="tournament.status !== 'draft'"
                        />
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Max Matches per Pair</label>
                        <input
                            v-model.number="form.max_matches_per_pair"
                            type="number"
                            class="input"
                            min="1"
                            max="20"
                            required
                            :disabled="tournament.status !== 'draft'"
                        />
                    </div>
                    
                    <div>
                        <label class="label">Points per Set</label>
                        <input
                            v-model.number="form.points_per_set"
                            type="number"
                            class="input"
                            min="1"
                            max="100"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            <span v-if="form.scoring_mode === 'unlimited'">Max points per side</span>
                            <span v-else>Total points to end match</span>
                        </p>
                    </div>
                </div>

                <!-- Scoring Mode -->
                <div>
                    <label class="label">Scoring Mode</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                        <label 
                            class="relative flex items-start p-4 rounded-lg border-2 cursor-pointer transition-all"
                            :class="[
                                form.scoring_mode === 'unlimited' ? 'border-primary bg-primary/10' : 'border-gray-700 hover:border-gray-600',
                                tournament.status !== 'draft' ? 'opacity-60 cursor-not-allowed' : ''
                            ]"
                        >
                            <input 
                                type="radio" 
                                v-model="form.scoring_mode" 
                                value="unlimited" 
                                class="sr-only"
                                :disabled="tournament.status !== 'draft'"
                            />
                            <div>
                                <div class="font-semibold text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Unlimited
                                </div>
                                <p class="text-sm text-gray-400 mt-1">Each side can score up to max points. Manual match completion.</p>
                            </div>
                        </label>
                        
                        <label 
                            class="relative flex items-start p-4 rounded-lg border-2 cursor-pointer transition-all"
                            :class="[
                                form.scoring_mode === 'americano' ? 'border-primary bg-primary/10' : 'border-gray-700 hover:border-gray-600',
                                tournament.status !== 'draft' ? 'opacity-60 cursor-not-allowed' : ''
                            ]"
                        >
                            <input 
                                type="radio" 
                                v-model="form.scoring_mode" 
                                value="americano" 
                                class="sr-only"
                                :disabled="tournament.status !== 'draft'"
                            />
                            <div>
                                <div class="font-semibold text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Americano
                                </div>
                                <p class="text-sm text-gray-400 mt-1">Match ends when combined score reaches target (e.g., 12+9=21).</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <p v-if="tournament.status !== 'draft'" class="text-sm text-yellow-400">
                    Some settings cannot be changed after the tournament has started.
                </p>
                
                <div class="flex justify-end gap-3 pt-4">
                    <Link :href="route('tournaments.show', tournament.id)" class="btn btn-secondary">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="form.processing"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    name: '',
    description: '',
    pairs_per_team: 4,
    rounds: 4,
    max_matches_per_pair: 4,
    points_per_set: 21,
    scoring_mode: 'americano',
});

const submit = () => {
    form.post(route('tournaments.store'));
};
</script>

<template>
    <AppLayout>
        <Head title="Create Tournament" />
        
        <div class="max-w-2xl mx-auto">
            <div class="page-header">
                <div>
                    <Link :href="route('tournaments.index')" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to tournaments
                    </Link>
                    <h1 class="page-title">Create Tournament</h1>
                </div>
            </div>
            
            <form @submit.prevent="submit" class="card p-6 space-y-6">
                <div>
                    <label class="label">Tournament Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="input"
                        placeholder="e.g., 2026 Alumni Cup"
                        required
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
                </div>
                
                <div>
                    <label class="label">Description (optional)</label>
                    <textarea
                        v-model="form.description"
                        class="input"
                        rows="3"
                        placeholder="Tournament Description | Court name | Date and Time"
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
                        />
                        <p class="mt-1 text-xs text-gray-500">Typical: 4 pairs per team</p>
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
                        />
                        <p class="mt-1 text-xs text-gray-500">Typical: 4 rounds</p>
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
                        />
                        <p class="mt-1 text-xs text-gray-500">Limits each pair's matches</p>
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
                            :class="form.scoring_mode === 'unlimited' ? 'border-primary bg-primary/10' : 'border-gray-700 hover:border-gray-600'"
                        >
                            <input 
                                type="radio" 
                                v-model="form.scoring_mode" 
                                value="unlimited" 
                                class="sr-only"
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
                            :class="form.scoring_mode === 'americano' ? 'border-primary bg-primary/10' : 'border-gray-700 hover:border-gray-600'"
                        >
                            <input 
                                type="radio" 
                                v-model="form.scoring_mode" 
                                value="americano" 
                                class="sr-only"
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
                
                <div class="flex justify-end gap-3 pt-4">
                    <Link :href="route('tournaments.index')" class="btn btn-secondary">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Tournament</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

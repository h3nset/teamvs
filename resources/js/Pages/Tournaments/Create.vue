<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    name: '',
    description: '',
    pairs_per_team: 4,
    rounds: 4,
    max_matches_per_pair: 4,
    points_per_set: 24,
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
                        placeholder="e.g., Alumni Cup 2024"
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
                        <p class="mt-1 text-xs text-gray-500">Max points in a set</p>
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

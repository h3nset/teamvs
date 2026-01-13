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

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    tournament: Object,
});

const form = useForm({
    access_token: '',
});

const showToken = ref(false);

const submit = () => {
    form.post(route('tournaments.unlock.submit', props.tournament.id));
};
</script>

<template>
    <Head :title="`Unlock - ${tournament.name}`" />
    
    <AppLayout>
        <div class="max-w-md mx-auto py-12">
            <!-- Back Link -->
            <Link 
                :href="route('tournaments.index')" 
                class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-8"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to tournaments
            </Link>

            <div class="card p-8">
                <!-- Lock Icon -->
                <div class="text-center mb-6">
                    <div class="w-20 h-20 mx-auto bg-primary/20 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    
                    <h1 class="text-2xl font-bold text-white mb-2">Tournament Protected</h1>
                    <p class="text-gray-400">
                        <strong class="text-white">{{ tournament.name }}</strong> is protected.
                        <br>Enter the access token to manage this tournament.
                    </p>
                </div>

                <!-- Token Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="label">Access Token</label>
                        <div class="relative">
                            <input
                                v-model="form.access_token"
                                :type="showToken ? 'text' : 'password'"
                                class="input pr-12 font-mono"
                                placeholder="Enter your access token"
                                autocomplete="off"
                                autofocus
                            />
                            <button
                                type="button"
                                @click="showToken = !showToken"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white"
                            >
                                <svg v-if="!showToken" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.access_token" class="text-red-400 text-sm mt-2">
                            {{ form.errors.access_token }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || !form.access_token"
                        class="btn btn-primary w-full"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                        </svg>
                        {{ form.processing ? 'Unlocking...' : 'Unlock Tournament' }}
                    </button>
                </form>

                <!-- Help Text -->
                <div class="mt-6 pt-6 border-t border-white/10 text-center">
                    <p class="text-sm text-gray-500">
                        Don't have the token? Ask the tournament creator.
                    </p>
                </div>
            </div>

            <!-- View Only Option -->
            <div class="text-center mt-6">
                <Link 
                    :href="route('tournaments.show', tournament.id)" 
                    class="text-gray-400 hover:text-primary text-sm"
                >
                    View tournament (read-only) →
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.text-primary {
    color: var(--color-primary);
}

.bg-primary\/20 {
    background-color: rgba(233, 69, 96, 0.2);
}
</style>

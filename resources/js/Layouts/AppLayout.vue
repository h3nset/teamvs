<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isOnline = ref(navigator.onLine);
const syncPending = ref(0);

const updateOnlineStatus = () => {
    isOnline.value = navigator.onLine;
};

onMounted(() => {
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
});

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
});
</script>

<template>
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="glass sticky top-0 z-50">
            <div class="container-app">
                <div class="flex items-center justify-between h-16">
                    <Link :href="route('tournaments.index')" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary to-pink-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">Padel Tournament</span>
                    </Link>
                    
                    <div class="flex items-center gap-4">
                        <!-- Sync Status -->
                        <div v-if="syncPending > 0" class="sync-indicator pending">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ syncPending }} pending</span>
                        </div>
                        
                        <!-- Online/Offline indicator -->
                        <div :class="['sync-indicator', isOnline ? 'synced' : 'error']">
                            <div :class="['w-2 h-2 rounded-full', isOnline ? 'bg-green-400' : 'bg-red-400']"></div>
                            <span>{{ isOnline ? 'Online' : 'Offline' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content -->
        <main class="container-app">
            <slot />
        </main>
        
        <!-- Offline Banner -->
        <div v-if="!isOnline" class="offline-banner">
            You're offline. Changes will sync when connection is restored.
        </div>
        
        <!-- Footer -->
        <footer class="text-center py-6 mt-8 border-t border-white/10">
            <p class="text-sm font-bold text-gray-400">2026 with ❤️ from REZIM70</p>
        </footer>
    </div>
</template>

<style scoped>
.from-primary {
    --tw-gradient-from: var(--color-primary);
}

.to-pink-600 {
    --tw-gradient-to: #db2777;
}
</style>

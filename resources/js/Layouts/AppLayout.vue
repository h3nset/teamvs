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
                        <div class="w-10 h-10 bg-gradient-to-br from-primary to-pink-600 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="brand-logo pl-3">
                            <span class="brand-title text-lg">Rezimainpadel</span>
                            <span class="brand-versus">VERSUS</span>
                        </div>
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
            <div class="flex items-center justify-center gap-2 text-sm">
                <span class="font-bold text-gray-400">From REZIM70 with ❤️ 2026</span>
                <span class="text-gray-600">•</span>
                <span class="text-gray-500 font-mono text-xs">{{ $page.props.app?.version || 'v1.0.0' }}</span>
                <a href="https://github.com/h3nset/teamvs" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="text-gray-500 hover:text-gray-300 transition ml-1"
                   title="Open Source on GitHub">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </a>
            </div>
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

<script setup>
import { ref, onMounted } from 'vue';
import html2canvas from 'html2canvas';

const props = defineProps({
    type: {
        type: String,
        default: 'match', // 'match', 'pair', 'tournament'
    },
    data: Object,
    tournament: Object,
});

const emit = defineEmits(['close']);

const cardRef = ref(null);
const isGenerating = ref(false);

const downloadImage = async () => {
    if (!cardRef.value) return;
    
    isGenerating.value = true;
    
    try {
        const canvas = await html2canvas(cardRef.value, {
            backgroundColor: '#1a1a2e',
            scale: 2,
        });
        
        const link = document.createElement('a');
        link.download = `${props.tournament?.name || 'result'}-${Date.now()}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    } catch (error) {
        console.error('Failed to generate image:', error);
    } finally {
        isGenerating.value = false;
    }
};

const shareImage = async () => {
    if (!cardRef.value) return;
    if (!navigator.share) {
        alert('Sharing not supported on this browser. Please download instead.');
        return;
    }
    
    isGenerating.value = true;
    
    try {
        const canvas = await html2canvas(cardRef.value, {
            backgroundColor: '#1a1a2e',
            scale: 2,
        });
        
        canvas.toBlob(async (blob) => {
            const file = new File([blob], 'result.png', { type: 'image/png' });
            
            await navigator.share({
                title: props.tournament?.name || 'Match Result',
                files: [file],
            });
        });
    } catch (error) {
        console.error('Failed to share:', error);
    } finally {
        isGenerating.value = false;
    }
};
</script>

<template>
    <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
        <div class="max-w-lg w-full">
            <!-- Card Preview -->
            <div ref="cardRef" class="share-card rounded-xl overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-primary to-pink-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="text-white font-bold text-lg">{{ tournament?.name }}</div>
                        <div class="text-white/70 text-sm">🏆 Padel Tournament</div>
                    </div>
                </div>
                
                <!-- Match Result -->
                <div v-if="type === 'match'" class="bg-gray-900 p-6">
                    <div class="text-center text-gray-400 text-sm mb-4">
                        Round {{ data.round_number }} • Match #{{ data.match_number }}
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4 items-center">
                        <div class="text-right">
                            <div class="flex items-center justify-end gap-2 mb-1">
                                <span class="font-semibold text-white">{{ data.home_pair?.name }}</span>
                                <div class="w-3 h-6 rounded" :style="{ backgroundColor: data.home_pair?.team_color }"></div>
                            </div>
                            <div class="text-xs text-gray-500">{{ data.home_pair?.team }}</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="flex items-center justify-center gap-3">
                                <span class="text-4xl font-bold text-white">{{ data.home_score }}</span>
                                <span class="text-2xl text-gray-600">-</span>
                                <span class="text-4xl font-bold text-white">{{ data.away_score }}</span>
                            </div>
                        </div>
                        
                        <div class="text-left">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-3 h-6 rounded" :style="{ backgroundColor: data.away_pair?.team_color }"></div>
                                <span class="font-semibold text-white">{{ data.away_pair?.name }}</span>
                            </div>
                            <div class="text-xs text-gray-500">{{ data.away_pair?.team }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Pair Stats -->
                <div v-if="type === 'pair'" class="bg-gray-900 p-6">
                    <div class="flex items-center justify-center gap-3 mb-4">
                        <div class="w-4 h-10 rounded" :style="{ backgroundColor: data.team_color }"></div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">{{ data.name }}</div>
                            <div class="text-gray-400">{{ data.team_name }}</div>
                        </div>
                        <div class="w-4 h-10 rounded" :style="{ backgroundColor: data.team_color }"></div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-3xl font-bold text-white">{{ data.matches_played }}</div>
                            <div class="text-xs text-gray-400">Played</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-green-400">{{ data.wins }}</div>
                            <div class="text-xs text-gray-400">Wins</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-white">{{ data.points_for }}</div>
                            <div class="text-xs text-gray-400">Points</div>
                        </div>
                    </div>
                </div>
                
                <!-- Tournament Winner -->
                <div v-if="type === 'tournament'" class="bg-gray-900 p-6">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-2">🏆</div>
                        <div class="text-gray-400 text-sm">Champion Team</div>
                    </div>
                    
                    <div class="flex items-center justify-center gap-3 mb-4">
                        <div class="w-4 h-12 rounded" :style="{ backgroundColor: data.color }"></div>
                        <div class="text-3xl font-bold text-white">{{ data.name }}</div>
                        <div class="w-4 h-12 rounded" :style="{ backgroundColor: data.color }"></div>
                    </div>
                    
                    <div class="text-center text-2xl font-bold text-primary">
                        {{ data.total_points }} Points
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="bg-gray-800 px-6 py-3 flex items-center justify-between text-xs text-gray-500">
                    <span>{{ new Date().toLocaleDateString() }}</span>
                    <span>padel.app</span>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-3 mt-4">
                <button @click="downloadImage" :disabled="isGenerating" class="btn btn-primary flex-1">
                    {{ isGenerating ? 'Generating...' : '📥 Download' }}
                </button>
                <button @click="shareImage" :disabled="isGenerating" class="btn btn-secondary flex-1">
                    📤 Share
                </button>
                <button @click="emit('close')" class="btn btn-secondary">
                    ✕
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.share-card {
    background: #1a1a2e;
}

.text-primary {
    color: var(--color-primary);
}

.from-primary {
    --tw-gradient-from: var(--color-primary);
}
</style>

<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    conferences: {
        type: Array,
        required: true
    }
});

// --- LÓGICA DEL MODAL DE DETALLES ---
const showDetailModal = ref(false);
const selectedConf = ref(null); // Guardará el objeto de la conferencia clickeada

const openDetails = (conf) => {
    selectedConf.value = conf;
    showDetailModal.value = true;
};

const closeDetails = () => {
    showDetailModal.value = false;
    // Pequeño delay para limpiar data y evitar parpadeo visual
    setTimeout(() => { selectedConf.value = null; }, 200);
};
</script>

<template>
    <div>
        <div class="flex flex-wrap justify-center gap-6 mb-8">
            
            <div 
                v-for="conf in conferences" 
                :key="conf.id" 
                class="
                    group flex flex-col
                    bg-white dark:bg-gray-800 
                    border border-gray-200 dark:border-gray-700 
                    rounded-xl p-5 
                    transition-all duration-300
                    hover:border-indigo-300 dark:hover:border-indigo-500 
                    hover:shadow-xl dark:hover:shadow-indigo-900/20
                    hover:-translate-y-1
                    
                    /* ANCHOS CALCULADOS PARA LOGRAR EL EFECTO */
                    w-full 
                    md:w-[calc(50%-0.75rem)] 
                    lg:w-[calc(33.333%-1rem)]
                "
            >
                <div class="flex items-center gap-5 mb-4">
                    <div class="flex-shrink-0 relative">
                        <div class="absolute inset-0 bg-indigo-500 rounded-full opacity-0 group-hover:opacity-20 transition-opacity duration-300 blur-md"></div>
                        <img 
                            :src="conf.imagen" 
                            :alt="conf.speaker"
                            class="relative z-10 w-20 h-20 rounded-full object-cover border-2 border-indigo-100 dark:border-gray-600 group-hover:border-indigo-500 dark:group-hover:border-indigo-400 transition-colors duration-300"
                        >
                    </div>

                    <div class="flex-1">
                        <h4 class="font-bold text-lg text-gray-800 dark:text-gray-100 leading-tight mb-1 group-hover:text-indigo-700 dark:group-hover:text-indigo-400 transition-colors">
                            {{ conf.titulo }}
                        </h4>
                        <p class="text-sm text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-wide flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
                            {{ conf.speaker }}
                        </p>
                    </div>
                </div>

                <div class="mt-auto flex justify-between items-center pt-4 border-t border-gray-100 dark:border-gray-700 transition-colors duration-300">
                    <span class="inline-flex items-center gap-1 text-xs bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 px-2.5 py-1 rounded-md border border-gray-200 dark:border-gray-600 font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ conf.hora }}
                    </span>
                    
                    <button 
                        @click="openDetails(conf)"
                        class="text-sm text-indigo-600 dark:text-indigo-400 font-semibold hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center gap-1 transition-all group/btn"
                    >
                        Ver detalles
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>

        <Modal :show="showDetailModal" @close="closeDetails" maxWidth="2xl">
            <div v-if="selectedConf" class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden transition-colors duration-300">
                
                <div class="bg-indigo-600 dark:bg-indigo-700 p-6 text-white relative transition-colors duration-300 overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-20 h-20 bg-indigo-900 opacity-20 rounded-full blur-xl"></div>

                    <button @click="closeDetails" class="absolute top-4 right-4 text-indigo-200 hover:text-white text-xl font-bold transition-colors z-10 bg-indigo-800/20 hover:bg-indigo-800/50 rounded-full w-8 h-8 flex items-center justify-center">✕</button>
                    
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 relative z-0">
                        <img 
                            :src="selectedConf.imagen" 
                            class="w-24 h-24 rounded-full border-4 border-white/90 dark:border-gray-800 shadow-lg object-cover transition-colors duration-300"
                        >
                        <div class="text-center sm:text-left">
                            <h3 class="text-xl sm:text-2xl font-bold leading-tight mb-2">{{ selectedConf.titulo }}</h3>
                            <div class="inline-flex items-center gap-2 bg-indigo-800/30 px-3 py-1 rounded-full">
                                <span class="font-medium text-indigo-100 text-sm uppercase tracking-wider">{{ selectedConf.speaker }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    <div>
                        <h4 class="flex items-center gap-2 text-lg font-bold text-gray-800 dark:text-gray-100 mb-3 border-b pb-2 border-gray-100 dark:border-gray-700 transition-colors">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Resumen de la Charla
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-justify text-sm sm:text-base">
                            {{ selectedConf.resumen || 'Descripción pendiente por confirmar.' }}
                        </p>
                    </div>

                    <div>
                        <h4 class="flex items-center gap-2 text-lg font-bold text-gray-800 dark:text-gray-100 mb-3 border-b pb-2 border-gray-100 dark:border-gray-700 transition-colors">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Semblanza del Ponente
                        </h4>
                        <div class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-xl border border-gray-100 dark:border-gray-700/50 transition-colors relative">
                            <span class="absolute top-2 left-2 text-4xl text-indigo-200 dark:text-indigo-900 font-serif leading-none">“</span>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-justify italic text-sm sm:text-base relative z-10 px-2">
                                {{ selectedConf.semblanza || 'Información biográfica pendiente.' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </Modal>

    </div>
</template>
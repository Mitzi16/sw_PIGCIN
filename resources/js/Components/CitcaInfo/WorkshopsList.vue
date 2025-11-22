<script setup>
import { ref } from 'vue'; 
import Modal from '@/Components/Modal.vue'; 

const props = defineProps({
    workshops: {
        type: Array,
        required: true
    }
});

const showModal = ref(false);
const selectedWorkshop = ref(null);

const openDetails = (workshop) => {
    selectedWorkshop.value = workshop;
    showModal.value = true;
};

const closeDetails = () => {
    showModal.value = false;
    setTimeout(() => { selectedWorkshop.value = null; }, 200);
};


const joinArray = (arr, key, separator = ', ') => {
    if (!arr) return '';
    return [...new Set(arr.map(item => item[key]).filter(Boolean))].join(separator);
};
</script>

<template>
    <div>
        <div class="flex flex-wrap justify-center gap-6 mb-12">
            
            <div 
                v-for="workshop in workshops" 
                :key="workshop.id" 
                class="
                    group flex flex-col
                    bg-white dark:bg-gray-800 
                    border border-gray-200 dark:border-gray-700 
                    rounded-2xl 
                    p-6
                    transition-all duration-300 
                    hover:shadow-xl dark:hover:shadow-black/40
                    hover:border-indigo-300 dark:hover:border-indigo-500
                    hover:-translate-y-1
                    
                    /* ANCHOS RESPONSIVOS (Misma lógica que Conferencias) */
                    w-full 
                    md:w-[calc(50%-0.75rem)] 
                    lg:w-[calc(33.333%-1rem)]
                "
            >
                <div class="flex -space-x-3 mb-4 overflow-hidden pt-2 px-1">
                    <img 
                        v-for="(inst, index) in workshop.instructores"
                        :key="index"
                        :src="inst.imagen" 
                        :alt="inst.nombre"
                        class="w-16 h-16 rounded-full object-cover border-4 border-white dark:border-gray-800 shadow-sm relative z-10 transition-transform duration-300 group-hover:scale-110"
                    >
                </div>

                <div class="flex-1 flex flex-col min-h-[120px]">
                    <div class="flex flex-wrap gap-2 mb-3">
                         <span class="px-2.5 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 text-xs font-bold border border-indigo-100 dark:border-indigo-800 uppercase tracking-wider">
                            {{ workshop.modalidad }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-md bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-300 text-xs font-bold border border-green-100 dark:border-green-800">
                            {{ workshop.nivel }}
                        </span>
                    </div>

                    <h4 class="font-extrabold text-gray-800 dark:text-gray-100 text-xl leading-tight mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                        {{ workshop.titulo }}
                    </h4>
                    
                    <div class="mt-auto">
                        <p class="text-gray-500 dark:text-gray-400 font-medium text-sm mb-1 line-clamp-2">
                            <span class="text-indigo-500 font-semibold">Por:</span> {{ joinArray(workshop.instructores, 'nombre') }}
                        </p>
                        
                        <p class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1 line-clamp-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.496 2.132a1 1 0 00-.992 0l-7 4A1 1 0 003 6.973v6.885a1 1 0 001.24.97l5-1.5a1 1 0 00.76-1.24l-1-5a1 1 0 00-1.24-.76l-1.5.45V6.973l6-3.429 6 3.429v6.885l-1.5-.45a1 1 0 00-1.24.76l-1 5a1 1 0 00.76 1.24l5 1.5a1 1 0 001.24-.97V6.973a1 1 0 00-.504-.868l-7-4z" clip-rule="evenodd" /></svg>
                            {{ joinArray(workshop.instructores, 'institucion', ' / ') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div class="flex items-center gap-1 text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                        <span>👥</span> Cupo: {{ workshop.cupo }}
                    </div>
                    
                    <button 
                        @click="openDetails(workshop)" 
                        class="text-sm text-white bg-gray-900 hover:bg-indigo-600 dark:bg-indigo-600 dark:hover:bg-indigo-500 px-4 py-2 rounded-lg font-medium shadow-md transition-all flex items-center gap-2 transform group-hover:scale-105"
                    >
                        Detalles
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>

            </div>
        </div>

        <Modal :show="showModal" @close="closeDetails" maxWidth="3xl">
            <div v-if="selectedWorkshop" class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden transition-colors duration-300">
                
                <div class="bg-gray-900 p-6 text-white relative">
                     <button @click="closeDetails" class="absolute top-4 right-4 text-gray-400 hover:text-white transition text-2xl font-bold bg-white/10 rounded-full w-8 h-8 flex items-center justify-center">&times;</button>
                     <div class="flex flex-col md:flex-row items-start md:items-center gap-5">
                        
                        <div class="flex -space-x-4 flex-shrink-0 pl-2">
                            <img 
                                v-for="(inst, index) in selectedWorkshop.instructores"
                                :key="index"
                                :src="inst.imagen" 
                                class="w-20 h-20 rounded-full border-4 border-gray-800 object-cover bg-gray-800 relative z-10"
                            >
                        </div>

                        <div>
                            <h3 class="text-2xl font-bold mb-1 leading-tight">{{ selectedWorkshop.titulo }}</h3>
                            <p class="text-indigo-300 font-medium text-lg">
                                {{ joinArray(selectedWorkshop.instructores, 'nombre') }}
                            </p>
                        </div>
                     </div>
                </div>

                <div class="p-6 space-y-8 max-h-[75vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Área Temática</span>
                            <span class="inline-block px-3 py-1 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-md font-semibold text-sm border border-indigo-100 dark:border-indigo-800">
                                {{ selectedWorkshop.area }}
                            </span>
                            
                            <div class="mt-4">
                                <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Detalles</span>
                                <div class="flex flex-wrap gap-2">
                                     <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-gray-600 dark:text-gray-300">Nivel: {{ selectedWorkshop.nivel }}</span>
                                     <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-gray-600 dark:text-gray-300">Mod: {{ selectedWorkshop.modalidad }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Descripción</span>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                                {{ selectedWorkshop.descripcion }}
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                        <h4 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Contenido del Taller
                        </h4>
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg border border-gray-200 dark:border-gray-600">
                            <img :src="selectedWorkshop.tabla_img" alt="Tabla de contenidos" class="w-full h-auto rounded shadow-sm object-contain max-h-[300px] mx-auto">
                        </div>
                    </div>

                    <div class="bg-indigo-50 dark:bg-indigo-900/10 rounded-xl p-5 border border-indigo-100 dark:border-indigo-800/30 space-y-6">
                        <h4 class="text-md font-bold text-indigo-800 dark:text-indigo-200 mb-2 border-b border-indigo-200 dark:border-indigo-800 pb-2">Instructores</h4>
                        
                        <div v-for="(inst, idx) in selectedWorkshop.instructores" :key="idx">
                             <div class="flex items-start gap-3">
                                <div class="mt-1 min-w-[24px]">
                                    <svg class="w-6 h-6 text-indigo-300 dark:text-indigo-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21L14.017 18C14.017 16.8954 13.1216 16 12.017 16H7.19945C6.29492 16 5.63229 16.8367 5.83419 17.722L6.73867 21H14.017ZM18.017 21L18.017 18C18.017 16.6642 17.0969 15.535 15.8535 15.1473C16.5681 14.2737 17.017 13.1873 17.017 12C17.017 9.23858 14.7784 7 12.017 7C9.25554 7 7.01697 9.23858 7.01697 12C7.01697 13.1873 7.46589 14.2737 8.18046 15.1473C6.93707 15.535 6.01697 16.6642 6.01697 18L6.01697 21H3.01697V18C3.01697 14.6863 5.70326 12 9.01697 12C9.01697 10.3431 10.3601 9 12.017 9C13.6738 9 15.017 10.3431 15.017 12C18.3307 12 21.017 14.6863 21.017 18V21H18.017Z"></path></svg>
                                </div>
                                <div>
                                    <h5 class="font-bold text-gray-800 dark:text-gray-100">{{ inst.nombre }}</h5>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mt-1">{{ inst.semblanza }}</p>
                                </div>
                             </div>
                        </div>
                    </div>

                </div>
            </div>
        </Modal>
    </div>
</template>
<script setup>
import Card from './CardCall.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from './ModalCall.vue';
import { provide, ref } from 'vue';
import BaseIcon from '@/Components/BaseIcon.vue';
import CardBoxComponentEmpty from '@/Components/CardBoxComponentEmpty.vue';
import { router } from "@inertiajs/vue3";
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/css/index.css";

import { 
    mdiMagnify, 
    mdiClose, 
    mdiFilePdfBox, 
    mdiFilterVariant, 
    mdiChevronDown, 
    mdiCalendarOutline, 
    mdiMapMarker, 
    mdiSchool, 
    mdiSort 
} from '@mdi/js';

const props = defineProps({
    calls: { type: Object, required: true },
    search: { type: String, required: true },
});

// --- ESTADOS ---
const showModal = ref(false);
// CAMBIO CLAVE: Renombramos 'call' a 'selectedCall' para evitar conflictos con el v-for
const selectedCall = ref(null); 

const search = ref(props.search);
const isLoading = ref(false);

// Estados para el modal del PDF
const showPdfModal = ref(false);
const pdfCall = ref(null);

// --- MÉTODOS ---
const setShowModal = (value) => {
    showModal.value = value;
};

const openModal = (item) => {
    selectedCall.value = item; // Guardamos la tarjeta exacta
    setShowModal(true);
};

const openPdf = (item) => {
    pdfCall.value = item; // Guardamos el PDF exacto
    showPdfModal.value = true;
};

const getCalls = () => {
    isLoading.value = true;
    router.visit(route("welcome", { search: search.value }), {
        preserveScroll: true,
        onFinish: () => (isLoading.value = false)
    });
}

// Proveemos el estado al Modal (si es que tu ModalCall lo usa)
provide('showModal', { showModal, setShowModal });
</script>

<template>
    <div class="vl-parent">
        <loading v-model:active="isLoading" :can-cancel="false" :is-full-page="true" />
    </div>
    
    <section class="relative py-8 bg-white dark:bg-gray-900 min-h-screen overflow-hidden">
        
        <div class="relative max-w-screen-xl mx-auto px-4 md:px-8 z-10">
            
            <!-- 1. BANNER SUPERIOR -->
            <div class="relative bg-[#1B396A] rounded-2xl overflow-hidden shadow-lg mb-6 flex flex-col md:flex-row justify-between items-center p-6 md:p-8">
                <div class="absolute inset-0 opacity-10 pointer-events-none">
                    <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 100">
                        <circle cx="90" cy="50" r="40" fill="white" />
                        <circle cx="80" cy="20" r="20" fill="white" />
                    </svg>
                </div>

                <div class="relative z-10 flex items-center gap-4">
                    <div class="bg-white/10 p-3 rounded-xl backdrop-blur-sm border border-white/10">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white text-2xl md:text-3xl font-bold tracking-tight mb-1">Convocatorias</h3>
                        <p class="text-blue-100 text-sm md:text-base">Consulta los congresos, encuentros y actividades académicas disponibles.</p>
                    </div>
                </div>

                <div class="relative z-10 mt-4 md:mt-0">
                    <button class="bg-white/10 hover:bg-white/20 transition-colors border border-white/20 text-white text-sm font-semibold px-5 py-2.5 rounded-full flex items-center gap-2 backdrop-blur-sm">
                        <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
                        Eventos Académicos
                    </button>
                </div>
            </div>

            <!-- 2. BARRA DE BÚSQUEDA -->
            <div class="bg-white rounded-full shadow-[0_2px_15px_rgb(0,0,0,0.04)] p-1.5 flex items-center mb-6 border border-gray-200">
                <div class="pl-4 pr-2 text-gray-400">
                    <BaseIcon :path="mdiMagnify" size="22" />
                </div>
                <input type="search" 
                    class="w-full bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 py-2 px-2 text-sm font-medium" 
                    placeholder="Buscar convocatorias, congresos o eventos..." 
                    v-model="search" 
                    @keyup.enter="getCalls" />
                <button @click.prevent="getCalls" 
                    class="bg-[#1B396A] text-white text-sm font-bold px-6 py-2.5 rounded-full hover:bg-[#132A4F] transition-colors shadow-md flex items-center gap-2">
                    <BaseIcon :path="mdiMagnify" size="18" />
                    Buscar
                </button>
            </div>

            <!-- 3. FILTROS Y ORDENAMIENTO -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="flex items-center gap-2 text-sm font-bold text-gray-700">
                        <BaseIcon :path="mdiFilterVariant" size="20" /> Filtrar por:
                    </span>
                    
                    <button class="bg-white border border-gray-200 rounded-full px-4 py-1.5 flex items-center gap-2 text-sm text-gray-600 font-medium hover:bg-gray-50 transition-colors shadow-sm">
                        <BaseIcon :path="mdiCalendarOutline" size="16" /> Fecha <BaseIcon :path="mdiChevronDown" size="16" class="text-gray-400" />
                    </button>
                    
                    <button class="bg-white border border-gray-200 rounded-full px-4 py-1.5 flex items-center gap-2 text-sm text-gray-600 font-medium hover:bg-gray-50 transition-colors shadow-sm">
                        <BaseIcon :path="mdiMapMarker" size="16" /> Modalidad <BaseIcon :path="mdiChevronDown" size="16" class="text-gray-400" />
                    </button>

                    <button class="bg-white border border-gray-200 rounded-full px-4 py-1.5 flex items-center gap-2 text-sm text-gray-600 font-medium hover:bg-gray-50 transition-colors shadow-sm">
                        <BaseIcon :path="mdiSchool" size="16" /> Área Temática <BaseIcon :path="mdiChevronDown" size="16" class="text-gray-400" />
                    </button>
                </div>

                <div class="flex items-center gap-3 ml-auto">
                    <span class="text-sm text-gray-500 font-medium flex items-center gap-2">
                        <BaseIcon :path="mdiSort" size="18" /> Ordenar por:
                    </span>
                    <button class="bg-white border border-gray-200 rounded-full px-4 py-1.5 flex items-center gap-2 text-sm text-gray-700 font-semibold hover:bg-gray-50 transition-colors shadow-sm">
                        Más recientes <BaseIcon :path="mdiChevronDown" size="16" class="text-[#1B396A]" />
                    </button>
                </div>
            </div>

            <!-- ZONA DE TARJETAS -->
            <div class="relative mt-4">
                <!-- IMAGEN DECORATIVA -->
                <div class="absolute top-0 -left-4 md:-left-8 w-72 h-72 md:w-96 md:h-96 lg:w-[500px] lg:h-[500px] pointer-events-none z-0">
                    <img src="/img/bg-decor-left.png" alt="Decoración fondo" class="w-full h-full object-contain object-left-top" />
                </div>

                <!-- GRID DE TARJETAS -->
                <div v-if="calls.data.length > 0" class="relative z-10 gap-6 grid sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="item in calls.data" :key="item.id" class="flex justify-center h-full">
                        <!-- IMPORTANTE: Pasamos 'item' en lugar de 'call' para que no choque con variables -->
                        <Card 
                            @open-modal="openModal" 
                            @open-pdf="openPdf" 
                            :call="item" 
                        />
                    </div>
                </div>
                
                <!-- Estado Vacío -->
                <div v-else class="relative z-10 bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center dark:bg-gray-800 dark:border-gray-700">
                    <CardBoxComponentEmpty />
                    <p class="text-gray-500 mt-4 font-medium">No se encontraron convocatorias que coincidan con tu búsqueda.</p>
                </div>

                <!-- Paginación -->
                <div v-if="calls.links.length > 3" class="relative z-10 mt-12 flex justify-center">
                    <div class="inline-flex bg-white rounded-full shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-gray-100 p-2 dark:bg-gray-800 dark:border-gray-700">
                        <Pagination :links="calls.links" />
                    </div>
                </div>
            </div>
            
        </div>
    </section>

    <!-- MODAL DE DETALLES -->
    <!-- Le pasamos 'selectedCall' que es la que se actualiza dinámicamente -->
    <Modal :call="selectedCall" />

    <!-- MODAL DEL VISOR PDF -->
    <div v-if="showPdfModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-md p-4 sm:p-6"
         @click.self="showPdfModal = false">
        
        <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden flex flex-col h-[90vh] border border-gray-200">
            <div class="bg-[#1B396A] px-5 py-4 flex justify-between items-center text-white shrink-0 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-full bg-white/10 pointer-events-none" style="clip-path: polygon(100% 0, 100% 100%, 0 100%, 50% 0);"></div>
                
                <div class="flex items-center gap-3 relative z-10">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <BaseIcon :path="mdiFilePdfBox" size="20" class="text-white" />
                    </div>
                    <span class="font-bold text-sm md:text-base truncate max-w-sm md:max-w-md">
                        {{ pdfCall?.file?.name || 'Visualizando Convocatoria' }}
                    </span>
                </div>
                <button @click="showPdfModal = false" class="text-white/80 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/20 relative z-10">
                    <BaseIcon :path="mdiClose" size="24" />
                </button>
            </div>
            
            <div class="flex-grow bg-gray-100 relative">
                <iframe v-if="pdfCall?.file" 
                        :src="'/storage/' + pdfCall.file.path" 
                        class="w-full h-full border-0 absolute inset-0" 
                        title="Visor de PDF">
                </iframe>
                <div v-else class="flex items-center justify-center h-full text-gray-500">
                    No hay archivo PDF disponible para esta convocatoria.
                </div>
            </div>
        </div>
    </div>
</template>
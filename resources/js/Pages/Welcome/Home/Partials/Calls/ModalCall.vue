<script setup>
import BaseIcon from "@/Components/BaseIcon.vue";
import Modal from '@/Components/Modal.vue';
import moment from "moment";
import { inject, ref } from 'vue';
import { mdiOpenInNew, mdiCalendarRange, mdiClockOutline, mdiCalendarRemove, mdiFilePdfBox, mdiClose } from "@mdi/js";
import CardBox from "@/Components/CardBox.vue";

const { showModal, setShowModal } = inject('showModal');

defineProps({
    call: Object
})

const readingTime = (text) => {
    if(!text) return 0;
    const words = text.split(/\s+/).length;
    return Math.ceil(words / 200);
};

const showPdf = ref(false)

const togglePdf = () => {
    // Ya NO ocultamos el modal principal, solo abrimos el PDF encima
    showPdf.value = !showPdf.value;
}
</script>

<template>
    <!-- MODAL PRINCIPAL DE DETALLES -->
    <Modal :show="showModal" @close="setShowModal(false)" :closeable="true">
        <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden pb-4">
            <!-- Imagen superior -->
            <div class="relative h-64 w-full">
                <img :src="call.image != null ? '/storage/' + call.image.path : '/img/login-image.jpg'"
                    class="w-full h-full object-cover" alt="imagen convocatoria" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <h2 class="absolute bottom-4 left-6 right-6 text-white text-2xl font-bold leading-tight drop-shadow-md">
                    {{ call.title }}
                </h2>
            </div>

            <div class="px-6 py-4">
                <!-- Grid de metadatos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="flex items-center text-gray-700 dark:text-gray-300">
                        <BaseIcon :path="mdiCalendarRange" size="18" class="text-[#003366] mr-2" />
                        <span class="text-sm">Inicio:</span>
                        <span class="ml-auto font-semibold text-gray-900 dark:text-white">
                            {{ moment(call.start_date, "YYYY-MM-DD").format("DD-MM-YYYY") }}
                        </span>
                    </div>
                    
                    <div class="flex items-center text-gray-700 dark:text-gray-300">
                        <BaseIcon :path="mdiCalendarRemove" size="18" class="text-red-500 mr-2" />
                        <span class="text-sm">Cierre:</span>
                        <span class="ml-auto font-semibold text-gray-900 dark:text-white">
                            {{ moment(call.end_date, "YYYY-MM-DD").format("DD-MM-YYYY") }}
                        </span>
                    </div>

                    <div class="flex items-center text-gray-700 dark:text-gray-300">
                        <BaseIcon :path="mdiClockOutline" size="18" class="text-[#b2b2b2] mr-2" />
                        <span class="text-sm">Tiempo de lectura:</span>
                        <span class="ml-auto font-semibold text-gray-900 dark:text-white">
                            {{ readingTime(call.description) }} min
                        </span>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <!-- Botón Visitar URL -->
                    <a v-if="call.url" target="_blank" :href="call.url"
                        class="inline-flex items-center px-4 py-2 bg-[#003366] text-white text-sm font-medium rounded-md hover:bg-[#002244] transition-colors">
                        <BaseIcon :path="mdiOpenInNew" size="16" class="mr-2" />
                        Visitar URL
                    </a>

                    <!-- Botón Ver PDF (Abre el visor interno) -->
                    <button v-if="call.file" @click="togglePdf()"
                        class="inline-flex items-center px-4 py-2 border-2 border-[#003366] text-[#003366] text-sm font-medium rounded-md hover:bg-[#003366] hover:text-white transition-colors">
                        <BaseIcon :path="mdiFilePdfBox" size="16" class="mr-2" />
                        Ver Convocatoria (PDF)
                    </button>

                    <!-- NUEVO: Botón para abrir el PDF en una nueva pestaña (opcional) -->
                    <a v-if="call.file" :href="'/storage/' + call.file.path" target="_blank"
                        class="inline-flex items-center px-4 py-2 text-gray-500 hover:text-[#003366] text-sm font-medium transition-colors">
                        <BaseIcon :path="mdiOpenInNew" size="16" class="mr-2" />
                        Abrir en nueva pestaña
                    </a>
                </div>

                <hr class="border-gray-200 dark:border-gray-700 mb-4" />

                <!-- Descripción -->
                <div>
                    <h3 class="text-lg font-bold text-[#003366] dark:text-blue-400 mb-2">Descripción</h3>
                    <p class="text-base text-justify text-gray-600 dark:text-gray-300 leading-relaxed">
                        {{ call.description }}
                    </p>
                </div>
            </div>
        </div>
    </Modal>

    <!-- VISOR DE PDF (Se abre encima del modal principal) -->
    <div v-if="showPdf"
        class="fixed top-0 left-0 w-full h-full flex items-center justify-center z-[100] bg-slate-900/80 backdrop-blur-md"
        tabindex="0" @keydown.esc="showPdf = false">
        <div class="relative w-full max-w-5xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow-2xl overflow-hidden flex flex-col h-[90vh]">
                <div class="flex justify-between items-center p-4 bg-[#003366] text-white shrink-0">
                    <span class="font-bold text-sm flex items-center">
                        <BaseIcon :path="mdiFilePdfBox" class="mr-2" />
                        Archivo: {{ call.file?.name || 'Documento' }}
                    </span>
                    <button @click="showPdf = false" class="text-white hover:text-red-400 transition-colors bg-white/10 p-1.5 rounded-lg">
                        <BaseIcon :path="mdiClose" size="24" />
                    </button>
                </div>
                <div class="bg-gray-100 flex-grow relative">
                    <iframe :src="'/storage/' + call.file?.path" class="w-full h-full border-0 absolute inset-0" />
                </div>
            </div>
        </div>
    </div>
</template>
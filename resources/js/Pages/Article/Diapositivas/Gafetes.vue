<script setup>
import { ref, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import HeadLogo from "@/Components/HeadLogo.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import CardBox from "@/Components/CardBox.vue";
import Modal from "@/Components/Modal.vue";
import BaseButton from "@/Components/BaseButton.vue"; // Agregado por si queremos botón de reintentar
import axios from "axios";
import {
    mdiIdCard, // Icono apropiado para gafetes
    mdiClose,
    mdiLoading,
    mdiAlertCircle,
    mdiCheckCircle
} from "@mdi/js";

// Importamos la función del archivo JS que creamos en el paso anterior
// Asegúrate de que la ruta sea correcta según donde guardaste el archivo
import { generateGafetesPdf } from "@/Pages/Article/Partials/Pdf/GenerationGafetes.js"
// O si lo guardaste en Pages: import { generateGafetesPdf } from "./generateGafetesPdf.js";

const props = defineProps({
    name: "Gafetes",
    title: { type: String, required: true },
});

// --- ESTADOS ---
const isLoading = ref(true);
const statusMessage = ref("Conectando con el servidor...");
const notification = ref({ show: false, color: "danger", message: "" });
const showPdfModal = ref(false);
const pdfUrl = ref("");
const generatedCount = ref(0);

/**
 * Proceso principal: Obtener datos y generar PDF masivo.
 */
const startGenerationProcess = async () => {
    isLoading.value = true;
    notification.value.show = false;
    statusMessage.value = "Obteniendo lista de usuarios...";

    try {
        // 1. Petición al Controller (getGafetesData)
        const response = await axios.get(route("generate.gafetes.data"));
        
        if (!response.data.success) {
            throw new Error("El servidor reportó un error al obtener los datos.");
        }

        const usersList = response.data.data;
        generatedCount.value = response.data.count;

        if (generatedCount.value === 0) {
            isLoading.value = false;
            notification.value = { 
                show: true, 
                color: "warning", 
                message: "No se encontraron usuarios aptos para generar gafetes (Postulantes presenciales o Comité)." 
            };
            return;
        }

        // 2. Llamada al Script JS para dibujar el PDF
        statusMessage.value = `Generando ${generatedCount.value} gafetes, esto puede tardar unos segundos...`;
        
        // Damos un pequeño timeout para que el UI se actualice antes de congelarse por el proceso de dibujo
        setTimeout(async () => {
            try {
                const url = await generateGafetesPdf(usersList);
                
                if (url) {
                    pdfUrl.value = url;
                    showPdfModal.value = true; // Abrir modal automáticamente
                    statusMessage.value = "¡Gafetes generados exitosamente!";
                } else {
                    throw new Error("Error en la generación gráfica del PDF.");
                }
            } catch (drawError) {
                console.error("Error dibujando PDF:", drawError);
                notification.value = { show: true, color: "danger", message: "Error al crear el archivo PDF visual." };
            } finally {
                isLoading.value = false;
            }
        }, 100);

    } catch (error) {
        console.error("Error en el proceso de gafetes:", error);
        let errorMessage = "Ocurrió un error inesperado.";
        
        if (error.response) {
             errorMessage = error.response.data.error || `Error del servidor: ${error.response.status}`;
        } else {
            errorMessage = error.message;
        }

        notification.value = { show: true, color: "danger", message: errorMessage };
        isLoading.value = false;
    }
};

/**
 * Se activa al cerrar el modal del PDF.
 */
const handleModalClose = () => {
    showPdfModal.value = false;
    // Liberar memoria del Blob
    if (pdfUrl.value.startsWith("blob:")) {
        URL.revokeObjectURL(pdfUrl.value);
    }
    pdfUrl.value = "";
    
    // Opcional: Redirigir al dashboard o quedarse aquí
    // router.get(route("dashboard")); 
};

/**
 * Permite reintentar manualmente si algo falló o se cerró
 */
const retry = () => {
    startGenerationProcess();
};

// Iniciar automáticamente al cargar la vista
onMounted(() => {
    startGenerationProcess();
});

</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <SectionTitleLineWithButton
            :icon="mdiIdCard"
            :title="title"
            main
        />

        <NotificationBar
            v-if="notification.show"
            :color="notification.color"
            :icon="mdiAlertCircle"
            :outline="false"
        >
            {{ notification.message }}
            <template #right>
                <button @click="notification.show = false">
                    <BaseIcon :path="mdiClose" class="w-6 h-6" />
                </button>
            </template>
        </NotificationBar>

        <CardBox class="mb-6 text-center min-h-[200px] flex flex-col justify-center items-center">
            
            <div v-if="isLoading" class="py-12">
                <BaseIcon
                    :path="mdiLoading"
                    class="w-16 h-16 text-blue-500 animate-spin mx-auto mb-4"
                />
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
                    Procesando Solicitud
                </h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">
                    {{ statusMessage }}
                </p>
            </div>

            <div v-else-if="!isLoading && !notification.show && generatedCount > 0" class="py-8">
                <BaseIcon
                    :path="mdiCheckCircle"
                    class="w-16 h-16 text-green-500 mx-auto mb-4"
                />
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                    Proceso Finalizado
                </h2>
                <p class="mb-6 text-gray-600">
                    Se han generado {{ generatedCount }} gafetes.
                </p>
                <!--<BaseButton
                    label="Ver PDF Nuevamente"
                    color="info"
                    @click="showPdfModal = true"
                    class="mr-2"
                />-->
                 <BaseButton
                    label="Volver al Inicio"
                    color="lightDark"
                    @click="router.get(route('dashboard'))"
                />
            </div>
            
             <div v-else-if="!isLoading" class="py-8">
                 <BaseButton
                    label="Reintentar Generación"
                    color="info"
                    @click="retry"
                />
            </div>

        </CardBox>

        <Modal
            :show="showPdfModal"
            @close="handleModalClose"
            :closeable="true"
            maxWidth="7xl"
        >
            <div class="relative">
                <!--<div class="absolute right-0 top-0 p-2 z-10">
                     <button @click="handleModalClose" class="bg-gray-800 text-white rounded-full p-1 hover:bg-gray-700 transition">
                        <BaseIcon :path="mdiClose" class="w-6 h-6" />
                    </button>
                </div>-->

                <iframe
                    v-if="pdfUrl"
                    :src="pdfUrl"
                    class="w-full h-[75vh] lg:h-[85vh]"
                    frameborder="0"
                ></iframe>
            </div>
        </Modal>

    </LayoutMain>
</template>
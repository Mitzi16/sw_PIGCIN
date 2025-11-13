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
import FormControl from "@/Components/FormControl.vue";
import FormField from "@/Components/FormField.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import axios from "axios";
import {
    mdiBallotOutline,
    mdiInformation,
    mdiClose,
    mdiLoading,
    mdiAlertCircle,
} from "@mdi/js";
import { generatePersonalRecognition } from "@/Pages/Article/Partials/Pdf/GenerateParticipation.js" 

const props = defineProps({
    name: "Participation",
    title: { type: String, required: true },
});

// --- ESTADOS ---
const isLoading = ref(true);
const notification = ref({ show: false, color: "danger", message: "" });
const showPdfModal = ref(false);
const pdfUrl = ref("");
const showExtraInfoModal = ref(false);
const modalPrompt = ref("");
const extraInfo = ref("");
const userData = ref(null); // Almacena los datos base del controlador


const generationQueue = ref([]); // La cola de roles a generar
const currentRoleForModal = ref(null); // Para saber qué rol está pidiendo info

/**
 * Paso 1: Obtiene datos y prepara la cola de generación.
 */
const startGenerationProcess = async () => {
    isLoading.value = true;
    notification.value.show = false;
    
    try {
        console.log("Iniciando proceso... obteniendo datos.");
        const response = await axios.get(route("reconocimiento.data"));
        userData.value = response.data; // Guardamos los datos base

        if (!userData.value || !userData.value.user_id) {
            throw new Error("El servidor no devolvió datos válidos.");
        }
        
        const role = userData.value.participant_role;
        console.log("Rol principal detectado:", role);

        if (role === 'DUAL_EDITOR_REVISOR') {
            // Caso especial Añadimos ambos roles a la cola
            generationQueue.value = ['EDITOR', 'REVISOR'];
            console.log("Encolados: EDITOR, REVISOR");
        } else if (role) {
            // Caso normal, añadimos solo ese rol
            generationQueue.value = [role];
            console.log("Encolado:", role);
        } else {
            throw new Error("No se detectó ningún rol válido.");
        }
        
        // Iniciar el procesamiento de la cola
        await processNextInQueue();

    } catch (error) {
        console.error("Error obteniendo datos del reconocimiento:", error);
        let errorMessage = "Ocurrió un error inesperado. " + error.message;
        if (error.response) {
            if (error.response.status === 403) {
                errorMessage = error.response.data.error || "No tiene permiso para generar este reconocimiento.";
            } else {
                errorMessage = "Error del servidor: " + (error.response.data.message || error.message);
            }
        }
        notification.value = { show: true, color: "danger", message: errorMessage };
        isLoading.value = false;
    }
};

/**
 * Paso 2 (Intermedio): Procesa el siguiente ítem de la cola.
 */
const processNextInQueue = async () => {
    if (generationQueue.value.length === 0) {
        console.log("Cola vacía. Proceso completado.");
        router.get(route("dashboard"));
        return;
    }

    const nextRole = generationQueue.value.shift(); // Tomar el primer rol de la cola
    console.log("Procesando rol:", nextRole);

    if (nextRole === 'TALLERISTA' || nextRole === 'CONFERENCISTA') {
        // Este rol necesita una pregunta
        currentRoleForModal.value = nextRole; 
        modalPrompt.value = (nextRole === 'TALLERISTA')
            ? "Por favor, ingresa el nombre del Taller impartido:"
            : "Por favor, ingresa el nombre de la Conferencia impartida:";
        extraInfo.value = "";
        showExtraInfoModal.value = true;
        isLoading.value = false; 
    } else {
        // Rol normal (Editor, Revisor, Staff, etc.)
        // Generar el PDF directamente
        await finishGeneration(nextRole, null);
    }
};

/**
 * Paso 3: Dibuja el PDF para un rol específico.
 */
const finishGeneration = async (role, extraInfoFromModal = null) => {
    isLoading.value = true;
    showExtraInfoModal.value = false;
    notification.value.show = false;

    try {
        console.log(`Iniciando generación de PDF para: ${role}`);
        
        // Modificamos los datos base con el rol actual de la cola
        // y creamos un folio único para este PDF específico.
        const modifiedData = { 
            ...userData.value, 
            participant_role: role,
            reconocimiento_folio: `REC-${userData.value.user_id}-${role}-${Date.now()}`
        };
        
        // Actualizamos la URL de verificación con el nuevo folio
        modifiedData.verification_url = route('reconocimiento.verify', modifiedData.reconocimiento_folio);

        const pdfBlobUrl = await generatePersonalRecognition(
            modifiedData, // Usamos los datos modificados
            extraInfoFromModal
        );

        if (pdfBlobUrl) {
            pdfUrl.value = pdfBlobUrl;
            showPdfModal.value = true; 
            isLoading.value = false;
        } else {
            throw new Error("La generación del PDF no devolvió una URL.");
        }
    } catch (error) {
        console.error(`Error en finishGeneration para ${role}:`, error);
        notification.value = { show: true, color: "danger", message: "Error al dibujar el PDF: " + error.message };
        isLoading.value = false;
    }
};

/**
 * Se activa al "Aceptar" en el modal de pregunta.
 */
const handleExtraInfoSubmit = () => {
    if (extraInfo.value.trim() === "") {
        alert("Por favor, ingrese el nombre solicitado.");
        return;
    }
    // Llama a finishGeneration con el rol y la info extra
    finishGeneration(currentRoleForModal.value, extraInfo.value);
    currentRoleForModal.value = null; // Limpiar
};

/**
 * ¡MODIFICADO! Se activa al cerrar el modal del PDF.
 * Ahora procesa el siguiente ítem de la cola en lugar de redirigir.
 */
const handleModalClose = () => {
    showPdfModal.value = false;
    if (pdfUrl.value.startsWith("blob:")) {
        URL.revokeObjectURL(pdfUrl.value);
    }
    pdfUrl.value = "";
    
    console.log("Modal de PDF cerrado. Procesando siguiente ítem...");
    processNextInQueue(); 
};

/**
 * ¡MODIFICADO! Cancela la generación (desde el modal de pregunta).
 * Limpia la cola y redirige.
 */
const cancelExtraInfo = () => {
    showExtraInfoModal.value = false;
    generationQueue.value = []; // Vaciar la cola
    console.log("Generación cancelada por el usuario.");
    router.get(route("dashboard"));
};

onMounted(() => {
    startGenerationProcess(); // ¡Iniciar todo el proceso!
});

</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <SectionTitleLineWithButton
            :icon="mdiBallotOutline"
            :title="title"
            main
        />

        <!-- Notificación de Error -->
        <NotificationBar
            v-if="notification.show"
            color="danger"
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

        <CardBox
            v
            v-if="isLoading"
            class="mb-6 text-center"
        >
            <div class="py-12">
                <BaseIcon
                    :path="mdiLoading"
                    class="w-12 h-12 text-blue-500 animate-spin mx-auto"
                />
                <p class="mt-4 text-gray-600 dark:text-gray-300">
                    Generando su reconocimiento, por favor espere...
                </p>
            </div>
        </CardBox>

        <Modal
            :show="showPdfModal"
            @close="handleModalClose" 
            :closeable="true"
            maxWidth="7xl"
        >
            <iframe
                v-if="pdfUrl"
                :src="pdfUrl"
                class="w-full h-[700px] lg:h-[80vh]"
                frameborder="0"
            />
        </Modal>
        
        <Modal
            :show="showExtraInfoModal"
            @close="cancelExtraInfo"
            :closeable="true"
            maxWidth="2xl"
        >
            <CardBox class="p-6">
                <h3 class="text-xl font-bold mb-4 dark:text-white">
                    Información Adicional Requerida
                </h3>
                
                <FormField :label="modalPrompt" class="mb-4">
                    <FormControl
                        v-model="extraInfo"
                        type="text"
                        placeholder="Escriba aquí..."
                        required
                        @keydown.enter="handleExtraInfoSubmit"
                    />
                </FormField>
                
                <BaseButtons>
                    <BaseButton
                        label="Aceptar y Generar"
                        color="info"
                        @click="handleExtraInfoSubmit"
                        :disabled="!extraInfo.trim()"
                    />
                    <BaseButton
                        label="Cancelar"
                        color="lightDark"
                        @click="cancelExtraInfo"
                    />
                </BaseButtons>
            </CardBox>
        </Modal>

    </LayoutMain>
</template>
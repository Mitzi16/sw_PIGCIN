<script setup>
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import debounce from "lodash.debounce";
import {
    mdiBallotOutline,
    mdiInformation,
    mdiPencil,
    mdiBroom,
    mdiMagnify,
    mdiPlus,
    mdiCheckDecagram,
    mdiReceiptText,
    mdiCached,
    mdiClose,
} from "@mdi/js";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import HeadLogo from "@/Components/HeadLogo.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";
import axios from "axios";

// --- IMPORTACIÓN CORREGIDA ---
// El alias '@' ya apunta a 'resources/js', así que empezamos desde 'Pages/...'
import { generateAcceptanceLetter } from "@/Pages/Article/Partials/Pdf/ConstansGeneral.js"; 

const props = defineProps({
    name: "Constancias",
    title: { type: String, required: true },
    articles: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// --- Estado y funciones para el Modal ---
const showModal = ref(false);
const selectedCertificateUrl = ref("");
const isGeneratingId = ref(null); 
const notification = ref({ 
    show: false,
    color: "danger",
    message: "",
});

// --- Nueva función para generar la constancia ---
const handleGenerateCertificate = async (article) => {
    if (isGeneratingId.value) return; 

    isGeneratingId.value = article.id;
    notification.value.show = false; 

    try {
        // 1. Obtener los datos (incluyendo el sello) desde el controlador
        const response = await axios.get(article.letter_data_url);
        
        const articleData = response.data;

        if (!articleData || !articleData.signedText) {
             throw new Error("La respuesta del servidor no incluyó los datos necesarios o el sello.");
        }

        // 2. Generar el PDF en el cliente con pdf-lib
        const pdfBlobUrl = await generateAcceptanceLetter(articleData);

        if (pdfBlobUrl) {
            // 3. Mostrar el PDF en el modal
            selectedCertificateUrl.value = pdfBlobUrl;
            showModal.value = true;
        } else {
            throw new Error("El script 'generateAcceptanceLetter' no devolvió una URL.");
        }

    } catch (error) {
        console.error("Error al generar la constancia:", error);
        notification.value = {
            show: true,
            color: "danger",
            message: "Error al generar la constancia. " + (error.response?.data?.message || error.message),
        };
    } finally {
        isGeneratingId.value = null; // Terminar el estado de carga
    }
};

const closeModal = () => {
    showModal.value = false;
    
    if (selectedCertificateUrl.value.startsWith("blob:")) {
        URL.revokeObjectURL(selectedCertificateUrl.value);
    }
    selectedCertificateUrl.value = "";
};
// --- Fin de la nueva lógica ---

const searchQuery = ref(props.filters.search || "");

const statusMap = {
    3: "Aceptado con observaciones",
    4: "Aceptado",
};

const filteredArticles = computed(() => {
    return props.articles.data || [];
});

watch(
    searchQuery,
    debounce((value) => {
        router.get(
            route('constancias.index'), // Usar el nombre de la ruta es más seguro
            { search: value },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            }
        );
    }, 300)
);
</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <SectionTitleLineWithButton
            :icon="mdiBallotOutline"
            :title="title"
            main
        />

        <!-- Notificación de Éxito (Inertia Flash) -->
        <NotificationBar
            v-if="$page.props.flash.success" 
            color="success"
            :icon="mdiInformation"
            :outline="false"
        >
            {{ $page.props.flash.success }}
        </NotificationBar>

        <!-- Notificación de Error (Inertia Flash) -->
        <NotificationBar
            v-if="$page.props.flash.error" 
            color="danger"
            :icon="mdiInformation"
            :outline="false"
        >
            {{ $page.props.flash.error }}
        </NotificationBar>

        <!-- Notificación de Error (Error de Generación de PDF) -->
        <NotificationBar
            v-if="notification.show" 
            :color="notification.color"
            :icon="mdiInformation"
            :outline="false"
        >
            {{ notification.message }}
            <template #right>
                <button @click="notification.show = false">
                    <BaseIcon :path="mdiClose" class="w-6 h-6" />
                </button>
            </template>
        </NotificationBar>


        <CardBox class="mb-6">
            <div class="relative mb-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por título de artículo..."
                    class="w-full py-2 pl-10 pr-4 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:placeholder-gray-400 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <div
                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                >
                    <BaseIcon
                        :path="mdiMagnify"
                        class="w-5 h-5 text-gray-500 dark:text-gray-400"
                    />
                </div>
            </div>

            <div
                class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm"
            >
                <table
                    class="min-w-full bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                >
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th
                                scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-600 dark:text-gray-300 uppercase"
                            >
                                Título del Artículo
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-600 dark:text-gray-300 uppercase"
                            >
                                Folio
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-600 dark:text-gray-300 uppercase"
                            >
                                Autor(es)
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-600 dark:text-gray-300 uppercase"
                            >
                                Estatus
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-600 dark:text-gray-300 uppercase"
                            >
                                Acción
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <tr v-if="filteredArticles.length === 0"> <!-- CORREGIDO: v-if -->
                            <td
                                colspan="5" 
                                class="px-6 py-4 text-center text-gray-500 dark:text-gray-400 whitespace-nowrap"
                            >
                                No se encontraron artículos con los estatus
                                "Aceptado" o "Aceptado con observaciones".
                            </td>
                        </tr>

                        <tr
                            v-for="article in filteredArticles"
                            :key="article.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <td
                                class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                            >
                                {{ article.title }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                            >
                                {{ article.article_folio }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap"
                            >
                                {{ article.author_name || "No especificado" }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        article.status_id === 4 // 4 = Aceptado (Verde)
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', // 3 = Obs. (Amarillo)
                                    ]"
                                >
                                    {{ statusMap[article.status_id] }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
                            >
                                <button
                                    type="button"
                                    @click="handleGenerateCertificate(article)"
                                    :disabled="isGeneratingId === article.id"
                                    class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
                                    :class="{ 'opacity-50 cursor-not-allowed': isGeneratingId === article.id }"
                                >
                                    <span v-if="isGeneratingId === article.id" class="inline-flex items-center">
                                        <BaseIcon
                                            :path="mdiCached"
                                            class="w-4 h-4 mr-2 animate-spin"
                                        />
                                        Generando...
                                    </span>
                                    <span v-else class="inline-flex items-center">
                                        <BaseIcon
                                            :path="mdiReceiptText"
                                            class="w-4 h-4 mr-2"
                                        />
                                        Descargar 
                                    </span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination
                v-if="articles.data && articles.data.length > 0" 
                :links="articles.links"
                class="mt-6"
            />
        </CardBox>

        <Modal
            :show="showModal"
            @close="closeModal"
            :closeable="true"
            :maxWidth="'7xl'"
        >
            <iframe
                v-if="selectedCertificateUrl" 
                :src="selectedCertificateUrl"
                class="w-full h-[700px] lg:h-[80vh]"
                frameborder="0"
            />
        </Modal>
    </LayoutMain>
</template>
<script setup>
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import {
    mdiEye,
    mdiClose,
    mdiInformation,
    mdiArrowLeft,
    mdiUpload,
    mdiDownload,
    mdiVideo, // Importado para el icono de video
    mdiFileDocument,
} from "@mdi/js";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import FormField from "@/Components/FormField.vue";
import { ref, computed } from "vue";
import { Tabs, Tab } from "flowbite-vue";
import { Link } from '@inertiajs/vue3';
import NotificationBar from "@/Components/NotificationBar.vue";
import HeadLogo from "@/Components/HeadLogo.vue";
import LabelControl from "@/Components/LabelControl.vue";
import BaseIcon from "@/Components/BaseIcon.vue";

const props = defineProps({
    name: 'Show',
    title: { type: String, required: true },
    routeName: { type: String, required: true },
    article: { type: Object, required: true },
    diapositiva: { type: Object, default: null }, // El modelo File
    diapositivaUrl: { type: String, default: null }, // La URL firmada
    canUpload: { type: Boolean, required: true },
});

const activeTab = ref('postulant');

// Helper para formatear el tipo de exposición
const expoTypeLabel = computed(() => {
    if (props.article.expo_type === 'presencial') return 'Presencial';
    if (props.article.expo_type === 'virtual') return 'Virtual';
    return 'No especificado';
});

// Helper para formatear el tamaño del archivo
const formattedSize = computed(() => {
    if (!props.diapositiva || !props.diapositiva.size) {
        return 'N/A';
    }
    const bytes = props.diapositiva.size;
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
});

// Helper para determinar el tipo de archivo y el icono
const fileType = computed(() => {
    if (!props.diapositiva) return { type: 'unknown', icon: mdiFileDocument };

    const mime = props.diapositiva.mime_type || '';
    
    // Lógica para VIDEO
    if (mime === 'video/mp4') {
        return { type: 'video', icon: mdiVideo };
    }

    if (mime === 'application/pdf') {
        return { type: 'pdf', icon: mdiFileDocument  };
    }

    if (mime.includes('powerpoint') || mime.includes('presentationml')) {
        return { type: 'ppt', icon: mdiFileDocument };
    }

    return { type: 'unknown', icon: mdiFileDocument };
});

</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <SectionTitleLineWithButton :icon="mdiEye" :title="title" main>
            <Link :href="route(`${routeName}index`)">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                    class="bi bi-x" viewBox="0 0 16 16">
                    <path
                        d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                </svg>
            </Link>
        </SectionTitleLineWithButton>

        <NotificationBar v-if="$page.props.flash.success" color="success" :icon="mdiInformation" :outline="false">
            {{ $page.props.flash.success }}
        </NotificationBar>

        <div class="md:grid md:grid-cols-5 gap-4 md:space-y-0 space-y-5">
            <CardBox class="col-span-3">

                <div v-if="diapositiva">
                    <FormField label="Tipo de Exposición Seleccionado:">
                        <LabelControl :value="expoTypeLabel" class="text-lg font-semibold" />
                    </FormField>

                    <hr class="my-4">

                    <FormField label="Archivo Cargado:">
                        <LabelControl :value="diapositiva.original_name" />
                    </FormField>

                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2 mb-5">
                        <FormField label="Tamaño:">
                            <LabelControl :value="formattedSize" />
                        </FormField>
                        <FormField label="Tipo MIME:">
                            <LabelControl :value="diapositiva.mime_type" />
                        </FormField>
                    </div>

                    <div class="border-2 border-dashed dark:border-gray-700 rounded-lg p-4">
                        <!-- Opción 1: Video MP4 -->
                        <div v-if="fileType.type === 'video' && diapositivaUrl" class="flex flex-col items-center">
                            <video controls class="w-full max-h-96 rounded shadow-md">
                                <source :src="diapositivaUrl" type="video/mp4">
                                Tu navegador no soporta la reproducción de videos.
                            </video>
                        </div>

                        <!-- Opción 2: PDF -->
                        <div v-else-if="fileType.type === 'pdf' && diapositivaUrl">
                            <iframe :src="diapositivaUrl" class="w-full h-96" frameborder="0"></iframe>
                        </div>
                        
                        <!-- Opción 3: Otros (PPT, etc) - Muestra "No disponible" -->
                        <div v-else class="flex flex-col items-center justify-center text-center p-8">
                             <BaseIcon :path="fileType.icon" class="w-24 h-24 text-gray-400 mb-4" />
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                Previsualización no disponible
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                Este tipo de archivo ({{ fileType.type }}) no se puede mostrar aquí.
                            </p>
                            <BaseButton
                                v-if="diapositivaUrl"
                                :href="diapositivaUrl"
                                :icon="mdiDownload"
                                label="Descargar Archivo"
                                color="info"
                                class="mt-4"
                            />
                        </div>
                    </div>

                </div>

                <div v-else class="text-center p-8 text-gray-500 dark:text-gray-400">
                    <p>No se encontró un archivo de diapositiva para este artículo.</p>
                </div>

                <BaseButtons class="mt-6">
                    <BaseButton 
                        :href="route(`${routeName}index`)" 
                        :icon="mdiArrowLeft" 
                        color="white"
                        label="Volver al Listado" 
                    />
                    <!-- El botón descargar aparece si hay URL y NO es PDF (porque el PDF ya tiene toolbar) -->
                    <BaseButton 
                        v-if="diapositivaUrl && fileType.type !== 'pdf'"
                        :href="diapositivaUrl" 
                        :icon="mdiDownload" 
                        color="info"
                        label="Descargar" 
                    />
                    <BaseButton 
                        v-if="canUpload"
                        :href="route(`${routeName}create`, article.id)" 
                        :icon="mdiUpload" 
                        color="success"
                        label="Volver a Cargar" 
                    />
                </BaseButtons>

            </CardBox>

            <div class="col-span-2 h-full lg:relative">
                <CardBox class="lg:sticky lg:top-14 lg:overflow-y-auto">
                    <Tabs v-model="activeTab" variant="underline" class="p-5">
                        <Tab name="postulant" title="Postulante">
                            <FormField label="Nombre:">
                                <LabelControl :value="article.postulant.name" />
                            </FormField>
                            <FormField label="Email:">
                                <LabelControl :value="article.postulant.email" />
                            </FormField>
                            <FormField label="Institución:">
                                <LabelControl :value="article.postulant?.institution?.name" />
                            </FormField>
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 mb-5">
                                <FormField label="Pais:">
                                    <LabelControl
                                        :value="article.postulant?.institution?.country?.name ?? 'Sin información'" />
                                </FormField>
                                <FormField label="Estado:">
                                    <LabelControl
                                        :value="article.postulant?.institution?.state?.name ?? 'Sin información'" />
                                </FormField>
                            </div>
                        </Tab>
                        <Tab name="article" title="Articulo">
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 mb-5">
                                <FormField label="Identificador:">
                                    <LabelControl :value="article.article_folio" />
                                </FormField>
                                <FormField label="Titulo:">
                                    <LabelControl :value="article.title" />
                                </FormField>
                                <FormField label="Tipo:">
                                    <LabelControl :value="article.article_type.name" />
                                </FormField>
                                <FormField label="Editor:">
                                    <LabelControl :value="article.editor.name" />
                                </FormField>
                                <FormField label="Estatus del artículo:">
                                    <LabelControl :value="article.article_status.name" />
                                </FormField>
                            </div>
                        </Tab>
                    </Tabs>
                </CardBox>
            </div>
        </div>
    </LayoutMain>
</template>
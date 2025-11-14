<script setup>
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import {
    mdiContentSave,
    mdiClose,
    mdiInformation,
    mdiReceiptTextSend, 
    mdiDownload
} from "@mdi/js";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import { ref } from "vue";
import { Tabs, Tab } from "flowbite-vue";
import { Link, useForm } from '@inertiajs/vue3';
import NotificationBar from "@/Components/NotificationBar.vue";
import HeadLogo from "@/Components/HeadLogo.vue";
import LabelControl from "@/Components/LabelControl.vue";

const props = defineProps({
    name: 'Create',
    title: { type: String, required: true },
    routeName: { type: String, required: true },
    article: { type: Object, required: true },
});

const activeTab = ref('postulant')

const form = useForm({
    article_id: props.article.id,
    // expo_type: props.article.expo_type || null,
    expo_type: props.article.expo_type || 'presencial',
    diapositiva: null,
});

const saveForm = () => {
    form.post(route(`${props.routeName}store`));
};

const handleDiapositivaInput = (event) => {
    form.diapositiva = event.target.files[0];
};

</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <SectionTitleLineWithButton :icon="mdiReceiptTextSend" :title="title" main>
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

        <NotificationBar v-if="$page.props.flash.error" color="danger" :icon="mdiInformation" :outline="false">
            {{ $page.props.flash.error }}
        </NotificationBar>

        <div class="md:grid md:grid-cols-5 gap-4 md:space-y-0 space-y-5">
            <CardBox isForm @submit.prevent="saveForm" class="col-span-3">

                <FormField 
                    label="Tipo de Exposición:" 
                    required 
                    help="Selecciona cómo realizarás tu exposición"
                    :error="form.errors.expo_type">
                    <FormControl
                        v-model="form.expo_type"
                        :options="[
                            { id: 'presencial', label: 'Presencial' },
                            { id: 'virtual', label: 'Virtual' },
                        ]"
                        component="select"
                        optionLabel="label"
                    />
                </FormField>

                <FormField 
                    label="Descargar formato de presentación" 
                    help="Si no tienes el formato de exposición, descárgalo aquí."
                >
                    <BaseButton
                        href="/templates/PLANTILLA 5TO CITCA.pptx"
                        :icon="mdiDownload"
                        label="Descargar PLANTILLA 5TO CITCA.pptx"
                        color="info"
                        target="_blank"
                        download
                    />
                </FormField>
                
                <FormField label="Subir Diapositiva:" required help="Archivos PDF o PowerPoint (.pdf, .ppt, .pptx) (MAX 25MB)"
                    :error="form.errors.diapositiva">
                    <div
                        class="bg-slate-100 border-4 border-dashed border-gray-400 flex flex-col items-center justify-center rounded-lg shadow-lg p-6 md:p-10 mb-4 dark:bg-gray-800 dark:border-gray-600">
                        <div class="w-auto mb-2 text-sm md:text-base font-medium text-gray-700 dark:text-gray-300">
                            <FormControl 
                                @input="handleDiapositivaInput" 
                                height="h-10.5" 
                                name="diapositiva" 
                                type="file"
                                class="w-full"
                                accept=".pdf, .ppt, .pptx, application/pdf, application/vnd.ms-powerpoint, application/vnd.openxmlformats-officedocument.presentationml.presentation" 
                            />
                            <p v-if="form.diapositiva?.name" class="font-semibold mt-2">
                                Nombre del archivo: {{ form.diapositiva?.name }}
                            </p>
                            <p v-if="form.diapositiva?.size" class="font-semibold">
                                Tamaño: {{ (form.diapositiva?.size / 1000).toFixed(2) }} KB
                            </p>
                        </div>
                    </div>
                </FormField>

                <BaseButtons class="mt-6">
                    <BaseButton :routeName="`${props.routeName}index`" :icon="mdiClose" color="white"
                        label="Cancelar" />
                    <BaseButton @click="saveForm" :icon="mdiContentSave" type="submit" color="success"
                        label="Guardar Diapositiva" :disabled="form.processing" />
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
                                        :value="article.postulante?.institution?.state?.name ?? 'Sin información'" />
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
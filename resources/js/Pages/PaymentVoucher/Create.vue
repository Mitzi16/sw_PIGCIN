<script setup>
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import {
    mdiPencil,
    mdiTrashCan,
    mdiContentSave,
    mdiClose,
    mdiInformation,
    mdiReceiptTextSend
} from "@mdi/js";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import { computed, watch, ref, onMounted } from "vue";
import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";
import { Tabs, Tab } from "flowbite-vue";
import { provide } from "vue";
import { Link, useForm, usePage, Head } from '@inertiajs/vue3';
import NotificationBar from "@/Components/NotificationBar.vue";
import HeadLogo from "@/Components/HeadLogo.vue";
import LabelControl from "@/Components/LabelControl.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    name: 'Create',
    title: { type: String, required: true },
    routeName: { type: String, required: true },
    article: { type: Object, required: true },
});

const activeTab = ref('postulant')

const form = useForm({
    article_id: props.article.id,
    reference: null,
    amount: null,
    payment_voucher_status_id: null,
    user_id: null,
    file: null,
});

const saveForm = () => {
    form.post(route(`${props.routeName}store`));
};

const handleFileInput = (event) => {
    form.file = event.target.files[0];
};

const getFileUrl = computed(() => {
    if (form.file !== null) {
        return URL.createObjectURL(form.file)
    }
});

</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <SectionTitleLineWithButton :icon="mdiReceiptTextSend" :title="title" main>
            <Link :href="route(`${routeName}index`)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x"
                viewBox="0 0 16 16">
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
                <div class="grid grid-cols-1 gap-2 md:grid-cols-2 mb-5">
                    <FormField label="Referencia:" required help="Ingresa los datos del comprobante"
                        :error="form.errors.reference">
                        <FormControl v-model="form.reference" placeholder="Ingresa la referencia" />
                    </FormField>
                    <FormField label="Monto:" required :error="form.errors.amount">
                        <FormControl v-model="form.amount" placeholder="Ingresa el monto" type="number" />
                    </FormField>
                </div>
                <FormField label="Archivo:" required help="Solo archivos PDF e imagenes (MAX 10M)"
                    :error="form.errors.file">
                    <div
                        class="bg-slate-100 border-4 border-dashed border-gray-400 flex flex-col items-center justify-center rounded-lg shadow-lg p-6 md:p-10 mb-4 dark:bg-gray-800 dark:border-gray-600">
                        <div class="w-auto mb-2 text-sm md:text-base font-medium text-gray-700 dark:text-gray-300">
                            <FormControl @input="handleFileInput" height="h-10.5" type="file" class="w-full" />
                            <p class="font-semibold mt-2">
                                Nombre del archivo: {{ form.file?.name }}
                            </p>
                            <p class="font-semibold">
                                Tamaño: {{ (form.file?.size / 1000).toFixed(2) }} KB
                            </p>
                        </div>
                        <div class="w-full flex justify-center mt-8 mb-4">
                            <template v-if="form.file">
                                <iframe v-if="form.file.type === 'application/pdf'"
                                    class="w-full h-96 border rounded-lg shadow-md" :src="getFileUrl" />
                                <img v-else :src="getFileUrl"
                                    class="max-w-full max-h-96 rounded-lg shadow-md object-contain"
                                    alt="Vista previa de imagen" />
                            </template>
                        </div>
                    </div>
                </FormField>

                <BaseButtons>
                    <BaseButton :routeName="`${props.routeName}index`" :icon="mdiClose" color="white"
                        label="Cancelar" />
                    <BaseButton @click="saveForm" :icon="mdiContentSave" type="submit" color="success"
                        label="Guardar" />
                </BaseButtons>
            </CardBox>
            <div class="col-span-2 h-full lg:relative">
                <CardBox class="lg:sticky lg:top-14 lg:overflow-y-auto">
                    <Tabs v-model="activeTab" variant="underline" class="p-5">
                        <Tab name="postulant" title="Postulante" :disabled="false">
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
                        <Tab name="article" title="Articulo" :disabled="false">
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 mb-5">
                                <FormField label="Titulo:">
                                    <LabelControl :value="article.title" />
                                </FormField>
                                <FormField label="Tipo:">
                                    <LabelControl :value="article.type" />
                                </FormField>
                                <FormField label="Editor:">
                                    <LabelControl :value="article.editor.name" />
                                </FormField>
                                <FormField label="Estatus:">
                                    <LabelControl :value="article.article_status.name" />
                                </FormField>
                            </div>
                            <FormField label="Comprobante:">
                                <LabelControl value="Sin comprobante" />
                            </FormField>
                        </Tab>
                    </Tabs>
                </CardBox>
            </div>
        </div>
    </LayoutMain>
</template>

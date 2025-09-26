<script setup>
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import {
    mdiContentSave,
    mdiClose,
    mdiInformation,
    mdiReceiptTextSend
} from "@mdi/js";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import { computed, ref } from "vue";
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
    reference: null,
    amount: null,
    payment_voucher_status_id: null,
    user_id: null,
    file: null,

    // Facturación
    requires_invoice: null,
    constancia: null,
    rfc: null,
    direccion_fiscal: null,
    regimen_fiscal: null,
    uso_cfdi: null,
});

const saveForm = () => {
    form.post(route(`${props.routeName}store`));
};

const handleFileInput = (event) => {
    form.file = event.target.files[0];
};

const handleConstanciaInput = (event) => {
    form.constancia = event.target.files[0];
};

const getFileUrl = computed(() => {
    if (form.file !== null) {
        return URL.createObjectURL(form.file)
    }
});

const getConstanciaUrl = computed(() => {
    if (form.constancia !== null) {
        return URL.createObjectURL(form.constancia)
    }
});
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

        <!-- Notificaciones -->
        <NotificationBar v-if="$page.props.flash.success" color="success" :icon="mdiInformation" :outline="false">
            {{ $page.props.flash.success }}
        </NotificationBar>

        <NotificationBar v-if="$page.props.flash.error" color="danger" :icon="mdiInformation" :outline="false">
            {{ $page.props.flash.error }}
        </NotificationBar>

        <div class="md:grid md:grid-cols-5 gap-4 md:space-y-0 space-y-5">
            <CardBox isForm @submit.prevent="saveForm" class="col-span-3">
                <!-- Referencia y monto -->
                <div class="grid grid-cols-1 gap-2 md:grid-cols-2 mb-5">
                    <FormField label="Referencia:" required help="Ingresa los datos del comprobante"
                        :error="form.errors.reference">
                        <FormControl v-model="form.reference" placeholder="Ingresa la referencia" />
                    </FormField>
                    <FormField label="Monto:" required :error="form.errors.amount">
                        <FormControl v-model="form.amount" placeholder="Ingresa el monto" type="number" />
                    </FormField>
                </div>

                <!-- Archivo comprobante -->
                <FormField label="Archivo:" required help="Solo archivos PDF e imagenes (MAX 10M)"
                    :error="form.errors.file">
                    <div
                        class="bg-slate-100 border-4 border-dashed border-gray-400 flex flex-col items-center justify-center rounded-lg shadow-lg p-6 md:p-10 mb-4 dark:bg-gray-800 dark:border-gray-600">
                        <div class="w-auto mb-2 text-sm md:text-base font-medium text-gray-700 dark:text-gray-300">
                            <FormControl @input="handleFileInput" height="h-10.5" name="file" type="file" class="w-full" />
                            <p v-if="form.file?.name" class="font-semibold mt-2">
                                Nombre del archivo: {{ form.file?.name }}
                            </p>
                            <p v-if="form.file?.size" class="font-semibold">
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

                <!-- ================== BLOQUE FACTURACIÓN ================== -->
                <div class="border-t border-gray-300 dark:border-gray-700 pt-6 mt-6 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        Opciones de Facturación
                    </h3>

                    <!-- Selector -->
                    <FormField label="¿Requieres factura?" required>
                        <select v-model="form.requires_invoice"
                            class="w-full border rounded-md p-2 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200">
                            <option value="">Selecciona una opción</option>
                            <option value="yes">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </FormField>

                    <!-- Si requiere factura -->
                    <div v-if="form.requires_invoice === 'yes'" class="space-y-5">
                        <!-- Constancia PDF -->
                        <FormField label="Constancia de situación fiscal (PDF)" help="Solo archivos PDF (MAX 10M)"
                            :error="form.errors.constancia">
                            <div
                                class="bg-slate-100 border-4 border-dashed border-gray-400 flex flex-col items-center justify-center rounded-lg shadow-lg p-6 md:p-10 mb-4 dark:bg-gray-800 dark:border-gray-600">
                                <div
                                    class="w-auto mb-2 text-sm md:text-base font-medium text-gray-700 dark:text-gray-300">
                                    <FormControl @input="handleConstanciaInput" height="h-10.5" type="file" name="constancia"
                                        accept="application/pdf" class="w-full" />
                                    <p v-if="form.constancia?.name" class="font-semibold mt-2">
                                        Nombre del archivo: {{ form.constancia?.name }}
                                    </p>
                                    <p v-if="form.constancia?.size" class="font-semibold">
                                        Tamaño: {{ (form.constancia?.size / 1000).toFixed(2) }} KB
                                    </p>
                                </div>
                                <div class="w-full flex justify-center mt-8 mb-4">
                                    <template v-if="form.constancia">
                                        <iframe v-if="form.constancia.type === 'application/pdf'"
                                            class="w-full h-96 border rounded-lg shadow-md"
                                            :src="getConstanciaUrl" />
                                    </template>
                                </div>
                            </div>
                        </FormField>

                        <!-- Si no hay constancia, capturar datos -->
                        <div v-if="!form.constancia" class="space-y-4">
                            <!-- <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <FormField label="Nombre:" required :error="form.errors.nombre">
                                    <FormControl v-model="form.nombre" type="text" placeholder="Nombre sin acentos" />
                                </FormField>
                                <FormField label="Apellido Paterno:" required :error="form.errors.paterno">
                                    <FormControl v-model="form.paterno" type="text" placeholder="Apellido Paterno sin acentos" />
                                </FormField>
                                <FormField label="Apellido Materno:" required :error="form.errors.materno">
                                    <FormControl v-model="form.materno" type="text" placeholder="Apellido Materno sin acentos" />
                                </FormField>
                            </div> -->

                            <FormField label="RFC:" required :error="form.errors.rfc"> 
                                <FormControl v-model="form.rfc"  placeholder="RFC" />
                            </FormField>

                            <FormField label="Dirección Fiscal:" required :error="form.errors.direccion_fiscal">
                                <FormControl v-model="form.direccion_fiscal"  placeholder="Dirección fiscal completa" />
                            </FormField>

                            <FormField label="Régimen Fiscal:" required :error="form.errors.regimen_fiscal">
                                <FormControl v-model="form.regimen_fiscal"  placeholder="Régimen Fiscal" />
                            </FormField>

                            <FormField label="Uso de CFDI:" required :error="form.errors.uso_cfdi">
                                <FormControl v-model="form.uso_cfdi"  placeholder="Uso de CFDI" />
                            </FormField>
                        </div>
                    </div>
                </div>
                <!-- ================== FIN BLOQUE FACTURACIÓN ================== -->

                <!-- Botones -->
                <BaseButtons class="mt-6">
                    <BaseButton :routeName="`${props.routeName}index`" :icon="mdiClose" color="white"
                        label="Cancelar" />
                    <BaseButton @click="saveForm" :icon="mdiContentSave" type="submit" color="success"
                        label="Guardar" />
                </BaseButtons>
            </CardBox>

            <!-- Panel lateral -->
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
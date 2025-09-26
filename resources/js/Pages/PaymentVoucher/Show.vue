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
import { Link, useForm, usePage, Head, router } from '@inertiajs/vue3';
import NotificationBar from "@/Components/NotificationBar.vue";
import HeadLogo from "@/Components/HeadLogo.vue";
import LabelControl from "@/Components/LabelControl.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    name: 'Show',
    title: { type: String, required: true },
    routeName: { type: String, required: true },
    paymentVoucher: { type: Object, required: true },
    filePath: { type: [String, null], required: true },
    constanciaPath: { type: [String, null], required: false },
});

const activeTab = ref('paymentVoucher')

const reload = () => {
    router.visit(route("${routeName}show", props.paymentVoucher.id), {
        preserveScroll: true,
    });
};

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
            <CardBox class="col-span-3">
                <div class="grid grid-cols-1 gap-2 md:grid-cols-2 mb-5">
                    <FormField label="Referencia:">
                        <LabelControl :value="paymentVoucher.reference" />
                    </FormField>
                    <FormField label="Monto:">
                        <LabelControl :value="paymentVoucher.amount" />
                    </FormField>
                </div>
                <FormField label="Archivo del Comprobante:">
                    <div v-if="paymentVoucher.file"
                        class="bg-slate-100 border-4 border-dashed border-gray-400 flex flex-col items-center justify-center rounded-lg shadow-lg p-6 md:p-10 mb-4 dark:bg-gray-800 dark:border-gray-600">
                        <div class="w-auto mb-2 text-sm md:text-base font-medium text-gray-700 dark:text-gray-300">
                            <p class="font-semibold mt-2">
                                Nombre del archivo: {{ paymentVoucher.file.original_name }}
                            </p>
                            <p class="font-semibold">
                                Tamaño: {{ (paymentVoucher.file.size / 1000).toFixed(2) }} KB
                            </p>
                        </div>
                        <div class="w-full flex justify-center mt-8 mb-4">
                            <template v-if="filePath">
                                <iframe v-if="paymentVoucher.file.mime_type === 'application/pdf'"
                                    class="w-full h-96 border rounded-lg shadow-md" :src="filePath" />
                                <img v-else :src="filePath"
                                    class="max-w-full max-h-96 rounded-lg shadow-md object-contain"
                                    alt="Vista previa de imagen" />
                            </template>
                        </div>
                    </div>
                    <div v-else
                        class="bg-slate-100 border-4 border-dashed border-gray-400 flex flex-col items-center justify-center rounded-lg shadow-lg p-6 md:p-10 mb-4 dark:bg-gray-800 dark:border-gray-600">
                        <p class="font-semibold text-gray-500">No se ha subido ningún archivo.</p>
                    </div>
                </FormField>
                
                <div class="border-t border-gray-300 dark:border-gray-700 pt-6 mt-6 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        Datos de Facturación
                    </h3>

                    <FormField label="¿Requiere factura?">
                        <LabelControl :value="paymentVoucher.requires_invoice ? 'Sí' : 'No'" />
                    </FormField>

                    <div v-if="paymentVoucher.requires_invoice" class="space-y-5">

                        <FormField label="RFC:">
                            <LabelControl :value="paymentVoucher.rfc" />
                        </FormField>

                        <FormField label="Dirección Fiscal:">
                            <LabelControl :value="paymentVoucher.direccion_fiscal" />
                        </FormField>

                        <FormField label="Régimen Fiscal:">
                            <LabelControl :value="paymentVoucher.regimen_fiscal" />
                        </FormField>

                        <FormField label="Uso de CFDI:">
                            <LabelControl :value="paymentVoucher.uso_cfdi" />
                        </FormField>
                        
                        <FormField label="Constancia de Situación Fiscal:">
                            <div v-if="paymentVoucher.constancia"
                                class="bg-slate-100 border-4 border-dashed border-gray-400 flex flex-col items-center justify-center rounded-lg shadow-lg p-6 dark:bg-gray-800 dark:border-gray-600">
                                <div class="w-auto text-center font-medium text-gray-700 dark:text-gray-300">
                                    <p class="font-semibold">
                                        {{ paymentVoucher.constancia.original_name }}
                                    </p>
                                    <a :href="constanciaPath" target="_blank" class="text-blue-500 hover:underline mt-2 inline-block">
                                        Ver Constancia
                                    </a>
                                </div>
                            </div>
                            <div v-else
                                class="bg-slate-100 border-4 border-dashed border-gray-400 flex items-center justify-center rounded-lg p-6 dark:bg-gray-800 dark:border-gray-600">
                                <p class="font-semibold text-gray-500">No se adjuntó constancia fiscal.</p>
                            </div>
                        </FormField>
                    </div>
                </div>
                <BaseButtons class="mt-6">
                    <BaseButton :icon="mdiClose" :routeName="`${routeName}index`" label="Salir" color="info" outline />
                </BaseButtons>

            </CardBox>
            <div class="col-span-2 h-full lg:relative">
                <CardBox class="lg:sticky lg:top-14 lg:overflow-y-auto">
                    <Tabs v-model="activeTab" variant="underline" class="p-5">
                        <Tab name="paymentVoucher" title="Comprobante" :disabled="false">
                            <FormField label="Estatus:">
                                <LabelControl :value="paymentVoucher.payment_voucher_status?.name" />
                            </FormField>
                            <FormField label="Comentarios:">
                                <LabelControl :value="paymentVoucher.comments || 'Sin comentarios'" height="h-20" />
                            </FormField>
                        </Tab>
                        <Tab name="postulant" title="Postulante" :disabled="false">
                            <FormField label="Nombre:">
                                <LabelControl :value="paymentVoucher.article.postulant.name" />
                            </FormField>
                            <FormField label="Email:">
                                <LabelControl :value="paymentVoucher.article.postulant.email" />
                            </FormField>
                            <FormField label="Institución:">
                                <LabelControl :value="paymentVoucher.article.postulant?.institution?.name" />
                            </FormField>
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 mb-5">
                                <FormField label="Pais:">
                                    <LabelControl
                                        :value="paymentVoucher.article.postulant?.institution?.country?.name ?? 'Sin información'" />
                                </FormField>
                                <FormField label="Estado:">
                                    <LabelControl
                                        :value="paymentVoucher.article.postulant?.institution?.state?.name ?? 'Sin información'" />
                                </FormField>
                            </div>
                        </Tab>
                        <Tab name="article" title="Articulo" :disabled="false">

                            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 mb-5">
                                <FormField label="Identificador:">
                                    <LabelControl :value="paymentVoucher.article.article_folio" />
                                </FormField>
                                <FormField label="Titulo:">
                                    <LabelControl :value="paymentVoucher.article.title" />
                                </FormField>
                                <FormField label="Tipo:">
                                    <LabelControl :value="paymentVoucher.article.article_type?.name" />
                                </FormField>
                                <FormField label="Editor:">
                                    <LabelControl :value="paymentVoucher.article.editor?.name" />
                                </FormField>
                                <FormField label="Estatus:">
                                    <LabelControl :value="paymentVoucher.article.article_status?.name" />
                                </FormField>
                            </div>
                            <FormField label="Comprobante:">
                                <LabelControl :value="paymentVoucher.payment_voucher_status?.name" />
                            </FormField>
                        </Tab>
                    </Tabs>
                </CardBox>
            </div>
        </div>
    </LayoutMain>
</template>

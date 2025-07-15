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
    mdiFilePdfBox
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
import Article from "./Partials/Article.vue";
import Status from "./Partials/Status.vue";
import Assigns from "./Partials/Assigns.vue";
import Evaluation from "./Partials/Evaluation.vue";
import { Link, useForm, usePage, Head } from '@inertiajs/vue3';
import NotificationBar from "@/Components/NotificationBar.vue";
import getRecognition from './Partials/Pdf/RecognitionGeneral.js'
import Modal from "@/Components/Modal.vue";
import HeadLogo from "@/Components/HeadLogo.vue";

const props = defineProps({
    name: 'Show',
    title: {
        type: String,
        required: true
    },
    routeName: {
        type: String,
        required: true
    },
    article: { type: Object, required: true },
    filePath: { type: String, required: true },
    articleStatuses: { type: Object, required: true },
    editors: { type: Object, required: true },
    reviewers: { type: Object, required: true },
    criteria: { type: Object, required: true },
    user: { type: Object, required: true },
    areas: { type: Object, default: {}, required: true },
    institutions: { type: Object, default: {}, required: true },
    statusReviewer: { type: Boolean, default: true, required: true },
});

const activeTab = ref('status')
const urlPdf = ref(null)
const showModal = ref(false)

const form = useForm({
    // _method: 'show',
    id: props.article.id,
    title: props.article.title,
    type: props.article.type,
    abstract: props.article.abstract,
    key_works: props.article.key_works,
    knowledge_area_id: props.article?.knowledge_area?.id,
    article_status_id: props.article.article_status_id,
    call: props.article?.call?.title,
    file: null,

    authors: props.article.authors,
    selectedArea: props.article?.knowledge_area?.parent_id,
});

const getRole = (roleNames) => {
    if (!Array.isArray(roleNames)) {
        roleNames = [roleNames];
    }
    return Array.isArray(props.user.roles) && props.user.roles.some(role => roleNames.includes(role.name));
}

const getPdf = async (type) => {
    try {
        const user = props.user.name;
        const authors = props.article.authors
            .map(author => `${author.name} ${author.surname}`) // Combina nombre y apellido
            .join(', ');
        const title = props.article.title;
        const id = props.article.id;
        const name = type ? user : authors;

        const url = await getRecognition(name, title, id);
        urlPdf.value = url;
        showModal.value = true;
    } catch (error) {
        console.error("Error al obtener el PDF:", error);
    }
};

provide('form', form);
provide('props', props);
provide('getRole', getRole);

</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <SectionTitleLineWithButton :icon="mdiPencil" :title="title" main>
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
        <div class="md:grid md:grid-cols-5 gap-4">
            <CardBox class="col-span-3">
                <Article />

                <BaseButtons class="mt-5">
                    <BaseButton :routeName="`${props.routeName}index`" :icon="mdiClose" color="white"
                        label="Cancelar" />
                </BaseButtons>
            </CardBox>
            <div class="col-span-2 h-full lg:relative">
                <CardBox class="lg:sticky lg:top-14 lg:overflow-y-auto">
                    <Tabs v-model="activeTab" variant="underline" class="p-5">
                        <Tab name="status" title="Estatus" :disabled="false">
                            <Status />
                        </Tab>
                        <Tab v-if="getRole(['Revisor', 'Editor', 'Admin'])" name="assign" title="Asignación">
                            <Assigns />
                        </Tab>

                        <Tab v-if="getRole(['Revisor', 'Editor', 'Admin'])" name="evaluation" title="Evaluación">
                            <Evaluation />
                        </Tab>
                        <Tab v-if="props.article.article_status_id === 4" name="recognitions" title="Reconocimientos">
                            <template v-if="props.article?.payment_voucher?.payment_voucher_status_id === 3">
                                <div class="justify-between flex p-2 mb-10 border-b-2">
                                    <div class="">
                                        <h3 class="font-medium text-black dark:text-white leading-tight">
                                            Reconocimiento personal
                                        </h3>
                                        <p class="text-sm">
                                            Dirigido al {{ props.user.roles[0].name }}
                                        </p>
                                    </div>
                                    <BaseButton :icon="mdiFilePdfBox" label="Ver" color="info" @click="getPdf(true)" />
                                </div>

                                <div class="justify-between flex p-2 border-b-2">
                                    <div class="">
                                        <h3 class="font-medium text-black dark:text-white leading-tight">
                                            Reconocimiento grupal
                                        </h3>
                                        <p class="text-sm">
                                            Dirigido para todos los autores
                                        </p>
                                    </div>
                                    <BaseButton :icon="mdiFilePdfBox" label="Ver" color="info" @click="getPdf()" />
                                </div>
                            </template>
                            <div v-else
                                class="max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="48"
                                    height="48" stroke-width="1">
                                    <path d="M14 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path>
                                    <path d="M12 9.765a3 3 0 1 0 0 4.47"></path>
                                    <path
                                        d="M3 5m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z">
                                    </path>
                                </svg>
                                <a href="#">
                                    <h5
                                        class="mb-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                                        Necesitar cargar tu comprobante de pago!
                                    </h5>
                                </a>
                                <p class="mb-3 font-normal text-gray-500 dark:text-gray-400">
                                    Es necesario cargar el comprobante de pago y que se valide para poder liberar reconocimientos.
                                </p>
                                <Link :href="route('paymentVoucher.create', props.article.id)"
                                    class="inline-flex font-medium items-center text-blue-600 hover:underline">
                                Cargar comprobante
                                <svg class="w-3 h-3 ms-2.5 rtl:rotate-[270deg]" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 11v4.833A1.166 1.166 0 0 1 13.833 17H2.167A1.167 1.167 0 0 1 1 15.833V4.167A1.166 1.166 0 0 1 2.167 3h4.618m4.447-2H17v5.768M9.111 8.889l7.778-7.778" />
                                </svg>
                                </Link>
                            </div>
                        </Tab>
                    </Tabs>
                </CardBox>
            </div>
        </div>
    </LayoutMain>
    <Modal :show="showModal" @close="showModal = false" :closeable="true" :maxWidth="'7xl'">
        <iframe :src="urlPdf" class="w-full h-[700px]" />
    </Modal>
</template>

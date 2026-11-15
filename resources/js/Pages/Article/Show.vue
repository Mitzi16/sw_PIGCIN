<script setup>
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import {
    mdiPencil,
    mdiClose,
    mdiInformation,
    mdiFilePdfBox
} from "@mdi/js";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { computed, ref, provide } from "vue";
import Article from "./Partials/Article.vue";
import Status from "./Partials/Status.vue";
import Assigns from "./Partials/Assigns.vue";
import Evaluation from "./Partials/Evaluation.vue";
import { Link, useForm } from '@inertiajs/vue3';
import NotificationBar from "@/Components/NotificationBar.vue";
import getRecognition from './Partials/Pdf/RecognitionGeneral.js';
import { generateGroupCertificate } from './Partials/Pdf/RecognitionGeneral.js';
import Modal from "@/Components/Modal.vue";
import HeadLogo from "@/Components/HeadLogo.vue";
import axios from "axios";

const props = defineProps({
    name: 'Show',
    title: { type: String, required: true },
    routeName: { type: String, required: true },
    article: { type: Object, required: true },
    filePath: { type: String, default: null },
    fileName: { type: String, default: null },
    articleStatuses: { type: Object, required: true },
    editors: { type: Object, required: true },
    reviewers: { type: Object, required: true },
    criteria: { type: Object, required: true },
    user: { type: Object, required: true },
    areas: { type: Object, default: {}, required: true },
    institutions: { type: Object, default: {}, required: true },
    statusReviewer: { type: Boolean, default: true, required: true },
});

// CONTROLADOR DE SECCIONES
const activeSection = ref('article'); 
const urlPdf = ref(null);
const showModal = ref(false); 

// 👇 REFS PARA LOS MODALES FLOTANTES
const showAssignsModal = ref(false);
const showEvaluationModal = ref(false);

const form = useForm({
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

// 👇 FUNCIÓN INTERCEPTORA
const handleSectionChange = (section) => {
    if (section === 'assigns') {
        showAssignsModal.value = true;
    } else if (section === 'evaluation') {
        showEvaluationModal.value = true;
    } else {
        activeSection.value = section;
    }
}

const getPdf = async (isPersonal) => {
    try {
        let url;

        if (isPersonal) {
            console.log("Obteniendo datos del artículo (para personal):", props.article.id);
            const response = await axios.get(route("article.getAuthors", props.article.id));
            const { article_type_name, knowledge_area_name, postulant_name } = response.data;

            if (!article_type_name || !knowledge_area_name || !postulant_name) {
                console.error("Faltan datos: ", response.data);
                Swal.fire('Error', 'Faltan datos del artículo o del autor (postulante) en el servidor.', 'error');
                return; 
            }

            const userName = postulant_name; 
            const title = props.article.title;
            const id = props.article.id;

            url = await getRecognition(
                userName, title, id, article_type_name, knowledge_area_name  
            );
        } else {
            url = await generateGroupCertificate(props.article.id, props.article.title);
        }
        
        if (url) {
            urlPdf.value = url;
            showModal.value = true;
        } else {
            Swal.fire('Error', 'No se pudo generar el reconocimiento.', 'error');
        }
    } catch (error) {
        console.error(`Error al obtener el PDF (${isPersonal ? 'Personal' : 'Grupal'}):`, error);
        Swal.fire('Error', 'Hubo un error al generar el reconocimiento.', 'error');
    }
};

provide('form', form);
provide('props', props);
provide('getRole', getRole);

const cancelRouteName = computed(() => {
    const userRoleNames = props.user?.roles?.map(role => role.name) ?? [];
    if (userRoleNames.includes('Editor') && userRoleNames.includes('Revisor')) {
        if (props.article?.editor_id === props.user?.id) {
            return 'article.diffuse';
        } else {
            return 'article.qualify';
        }
    }
    return `${props.routeName}index`;
});
</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <!-- ENCABEZADO -->
       

        <NotificationBar v-if="$page.props.flash.success" color="success" :icon="mdiInformation" :outline="false">
            {{ $page.props.flash.success }}
        </NotificationBar>
        <NotificationBar v-if="$page.props.flash.error" color="danger" :icon="mdiInformation" :outline="false">
            {{ $page.props.flash.error }}
        </NotificationBar>

        <div class="flex flex-col gap-4">
            
            <!-- PANEL SUPERIOR -->
            <CardBox class="w-full relative">
                <Status @changeSection="handleSectionChange" />
            </CardBox>

            <!-- CONTENIDO DINÁMICO INLINE -->
            
            <!-- VISTA: DATOS DEL ARTÍCULO -->
            <CardBox v-if="activeSection === 'article'" class="w-full">
                <h2 class="text-xl font-bold mb-4 text-gray-700 dark:text-gray-200 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19M17,17H7V15H17V17M17,13H7V11H17V13M17,9H7V7H17V9Z" /></svg>
                    Datos del Artículo
                </h2>
                <Article />
                <BaseButtons class="mt-5">
                    <BaseButton :routeName="cancelRouteName" :icon="mdiClose" color="white" label="Cancelar" />
                </BaseButtons>
            </CardBox>

            <!-- VISTA: RECONOCIMIENTOS -->
            <CardBox v-if="activeSection === 'recognitions'" class="w-full">
                <h2 class="text-xl font-bold mb-4 text-gray-700 dark:text-gray-200">Reconocimientos</h2>
                
                <template v-if="props.article?.payment_voucher?.payment_voucher_status_id === 3">
                    <div class="justify-between flex p-2 mb-10 border-b-2 dark:border-gray-700">
                        <div>
                            <h3 class="font-medium text-black dark:text-white leading-tight">Reconocimiento personal</h3>
                            <p class="text-sm text-gray-500">Dirigido al {{ props.user.roles[0].name }}</p>
                        </div>
                        <BaseButton :icon="mdiFilePdfBox" label="Ver" color="info" @click="getPdf(true)" />
                    </div>

                    <div class="justify-between flex p-2 border-b-2 dark:border-gray-700">
                        <div>
                            <h3 class="font-medium text-black dark:text-white leading-tight">Reconocimiento grupal</h3>
                            <p class="text-sm text-gray-500">Dirigido para todos los autores</p>
                        </div>
                        <BaseButton :icon="mdiFilePdfBox" label="Ver" color="info" @click="getPdf()" />
                    </div>
                </template>
                
                <div v-else class="max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="48" height="48" stroke-width="1">
                        <path d="M14 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path>
                        <path d="M12 9.765a3 3 0 1 0 0 4.47"></path>
                        <path d="M3 5m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z"></path>
                    </svg>
                    <a href="#">
                        <h5 class="mb-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Necesitas cargar tu comprobante de pago!</h5>
                    </a>
                    <p class="mb-3 font-normal text-gray-500 dark:text-gray-400">
                        Es necesario cargar el comprobante de pago y que se valide para poder liberar reconocimientos.
                    </p>
                    <Link :href="route('paymentVoucher.create', props.article.id)" class="inline-flex font-medium items-center text-blue-600 hover:underline">
                        Cargar comprobante
                        <svg class="w-3 h-3 ms-2.5 rtl:rotate-[270deg]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11v4.833A1.166 1.166 0 0 1 13.833 17H2.167A1.167 1.167 0 0 1 1 15.833V4.167A1.166 1.166 0 0 1 2.167 3h4.618m4.447-2H17v5.768M9.111 8.889l7.778-7.778" />
                        </svg>
                    </Link>
                </div>
            </CardBox>
        </div>
    </LayoutMain>
    
    <!-- ============================================== -->
    <!-- MODAL ASIGNAR REVISORES -->
    <!-- ============================================== -->
    <Modal :show="showAssignsModal" @close="showAssignsModal = false" :maxWidth="'5xl'">
        <div class="flex flex-col max-h-[90vh] bg-white dark:bg-slate-800 rounded-lg overflow-hidden shadow-2xl">
            <!-- CABECERA AZUL OSCURO -->
            <div class="bg-[#15396F] text-white p-5 flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="currentColor">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                    </svg>
                    <div>
                        <h3 class="text-xl font-bold">Asignar editor y revisores</h3>
                        <p class="text-sm text-blue-200 mt-0.5">Gestión de responsables del artículo</p>
                    </div>
                </div>
                <button @click="showAssignsModal = false" class="text-white hover:text-gray-300 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>
            </div>
            <!-- CUERPO DEL MODAL -->
            <div class="p-6 overflow-y-auto">
                <Assigns @close="showAssignsModal = false" />
            </div>
        </div>
    </Modal>

    <!-- ============================================== -->
    <!-- MODAL EVALUACIÓN -->
    <!-- ============================================== -->
    <Modal :show="showEvaluationModal" @close="showEvaluationModal = false" :maxWidth="'5xl'">
        <div class="flex flex-col max-h-[90vh] bg-white dark:bg-slate-800 rounded-lg overflow-hidden shadow-2xl">
            <!-- CABECERA AZUL OSCURO -->
            <div class="bg-[#15396F] text-white p-5 flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="currentColor">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M11,13H13V15H11V13M11,17H17V19H11V17Z" />
                    </svg>
                    <div>
                        <h3 class="text-xl font-bold">Evaluación del artículo</h3>
                        <p class="text-sm text-blue-200 mt-0.5">Criterios y comentarios de revisión</p>
                    </div>
                </div>
                <button @click="showEvaluationModal = false" class="text-white hover:text-gray-300 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>
            </div>
            <!-- CUERPO DEL MODAL -->
            <div class="p-6 overflow-y-auto">
                <Evaluation @close="showEvaluationModal = false" />
            </div>
        </div>
    </Modal>

    <!-- MODAL PARA VER EL PDF -->
    <Modal :show="showModal" @close="showModal = false" :closeable="true" :maxWidth="'7xl'">
        <iframe :src="urlPdf" class="w-full h-[700px]" />
    </Modal>
</template>
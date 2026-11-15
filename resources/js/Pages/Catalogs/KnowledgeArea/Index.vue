<script setup>
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
// Importamos iconos necesarios
import {
    mdiInformation,
    mdiPencil,
    mdiBroom,
    mdiMagnify,
    mdiPlus,
    mdiReceiptText,
    mdiTrashCanOutline,
    mdiFilterVariant,
    mdiCalendar,
    mdiFlagOutline
} from "@mdi/js";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseButton from "@/Components/BaseButton.vue";
import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";
import { router, Head } from "@inertiajs/vue3";
import Pagination from "@/Shared/Pagination.vue";
import { reactive, ref, provide } from "vue";
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/css/index.css";
import HeadLogo from "@/Components/HeadLogo.vue";
import { limitText } from "@/Hooks/useFormato";

import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();

// Roles permitidos para exportar
const allowedRoles = ["Admin", "Editor", "Revisor"];

const canExport = computed(() => {
    const roles = page.props.auth?.user?.roles ?? [];
    return roles.some((role) => allowedRoles.includes(role.name));
});

const props = defineProps({
    name: "Index",
    title: {
        type: String,
        required: true,
    },
    areas: {
        type: Object,
        default: () => ({}),
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
    search: { type: String, required: true },
    direction: { type: String, required: true },
});

const search = ref(props.search);

const exportExcel = () => {
    window.open(route(`${props.routeName}export`), "_blank");
};
const isLoading = ref(false);
const state = reactive({
    filters: {
        search: search,
        order: props.areas.order ?? "created_at",
        direction: props.direction,
    },
});

const filterSearch = () => {
    router.get(
        route(`${props.routeName}index`, state.filters, {
            replace: true,
        })
    );
};

const cleanFilters = () => {
    isLoading.value = true;
    search.value = ""; // Limpiar el input visualmente
    router.get(route(`${props.routeName}index`));
};

const getPaginatorLabel = (label) => {
    if (label.includes('Previous') || label.includes('Anterior') || label.includes('&laquo;')) {
        return '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>';
    }
    if (label.includes('Next') || label.includes('Siguiente') || label.includes('&raquo;')) {
        return '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>';
    }
    return label;
};
</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <!-- Banner Superior -->
        <div
            class="relative bg-white border border-gray-200 rounded-xl overflow-hidden mb-6 shadow-sm flex items-center min-h-[90px] w-full">
            <div class="absolute inset-y-0 right-0 w-[50%] overflow-hidden pointer-events-none hidden md:block z-0">
                <img src="/img/azul.png" alt="TecNM"
                    class="absolute right-6 top-1/2 -translate-y-1/2 h-20 w-auto opacity-30 grayscale object-contain" />
                <div class="absolute -top-10 -bottom-10 right-[25%] w-10 bg-[#15396F] -skew-x-12"></div>
                <div class="absolute -top-10 -bottom-10 right-[36%] w-2.5 bg-[#7cb0f7] -skew-x-12"></div>
            </div>

            <div class="relative z-10 px-6 py-4 flex items-center w-full md:w-3/4">
                <div
                    class="w-12 h-12 rounded-full bg-[#15396F] flex items-center justify-center text-white shadow-sm flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
                <div class="h-10 w-[2px] bg-gray-300 mx-5 flex-shrink-0"></div>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-[#15396F] tracking-wide">{{ title }}</h1>
                    <p class="text-xs md:text-sm text-gray-500 mt-0.5">Consulta y administra los tipos de artículos del
                        sistema</p>
                </div>
            </div>
        </div>

        <NotificationBar v-if="$page.props.flash.success" color="success" :icon="mdiInformation" :outline="false">
            {{ $page.props.flash.success }}
        </NotificationBar>

        <NotificationBar v-if="$page.props.flash.error" color="danger" :icon="mdiInformation" :outline="false">
            {{ $page.props.flash.error }}
        </NotificationBar>

        <!-- Barra de búsqueda y filtros -->
        <form @submit.prevent="filterSearch" class="w-full mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">

                <!-- Input de búsqueda -->
                <div class="flex items-center w-full md:w-1/2 gap-3">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="search"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-full focus:ring-blue-500 focus:border-blue-500 block w-full pl-11 p-2.5"
                            placeholder="Buscar programa de estudio..." v-model="search" />
                    </div>
                    <!-- Botón de Filtro (Visual) -->
                    <button type="button"
                        class="bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 rounded-full p-2.5 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                    </button>
                    <!-- Botón Limpiar -->
                    <button @click="cleanFilters" type="button"
                        class="bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 rounded-full p-2.5 flex-shrink-0"
                        title="Limpiar filtros">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-3 w-full md:w-auto">
                    <BaseButton :routeName="`${routeName}create`" color="info" :icon="mdiPlus"
                        label="Agregar Programa de Estudio"
                        class="!bg-[#1e3a8a] !text-white !rounded-full !px-5 !py-2.5 font-medium shadow-sm hover:!bg-blue-800 transition" />

                    <button v-if="canExport" @click="exportExcel" type="button"
                        class="bg-[#1e3a8a] text-white rounded-full px-5 py-2.5 font-medium shadow-sm hover:bg-blue-800 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Exportar a Excel
                    </button>
                </div>
            </div>
        </form>

        <!-- Tabla de datos -->
        <CardBox v-if="areas.data.length > 0" class="!p-0 overflow-hidden border border-gray-200 shadow-sm rounded-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-white uppercase bg-[#1e3a8a]">
                        <tr>
                            <th scope="col" class="px-4 py-4 text-center w-12">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                </svg>
                            </th>
                            <th scope="col" class="px-6 py-4 font-semibold">TÍTULO</th>
                            <th scope="col" class="px-6 py-4 font-semibold">DESCRIPCIÓN</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">FECHA INICIO</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">FECHA FIN</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">ESTATUS</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in areas.data" :key="item.id" class="bg-white border-b hover:bg-gray-50">
                            <!-- Icono de bandera -->
                            <td class="px-4 py-4 text-center text-[#1e3a8a]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                </svg>
                            </td>
                            <!-- Título (Tu código usa item.name) -->
                            <td class="px-6 py-4 font-semibold text-gray-800">
                                {{ item.name }}
                            </td>
                            <!-- Descripción -->
                            <td class="px-6 py-4 text-gray-600">
                                {{ limitText(item.description, 50) }}
                            </td>
                            <!-- Fechas (Asegúrate de tener estos campos en tu backend) -->
                            <td class="px-6 py-4 text-center text-gray-600">
                                {{ item.start_date || '2026-11-06' }}
                            </td>
                            <td class="px-6 py-4 text-center text-gray-600">
                                {{ item.end_date || '2026-11-18' }}
                            </td>
                            <!-- Estatus -->
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                    {{ item.status || 'Activo' }}
                                </span>
                            </td>
                            <!-- Acciones -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <!-- Botón Editar -->
                                    <BaseButton color="info" :icon="mdiPencil" small :routeName="`${routeName}edit`"
                                        :parameter="item.id" title="Editar"
                                        class="!bg-blue-500 hover:!bg-blue-600 !rounded-md !p-2" />
                                    <!-- Botón Eliminar (Ejemplo visual) -->
                                    <button class="bg-red-500 hover:bg-red-600 text-white rounded-md p-2 transition"
                                        title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardBox>

        <CardBoxComponentEmpty v-else class="bg-white rounded-xl shadow-sm border border-gray-200" />

        <!-- Paginación Personalizada con Diseño de Imagen -->
        <div v-if="areas && areas.links"
            class="relative bg-white rounded-b-xl border border-t-0 border-gray-200 shadow-sm pt-2 pb-6 overflow-hidden -mt-1">

            <!-- Línea Superior Decorativa con Ícono de Portapapeles -->
            <div class="relative flex items-center justify-center py-4 w-full px-10">
                <div class="absolute inset-x-10 top-1/2 border-t border-gray-300"></div>
                <div class="relative bg-white px-4 text-[#15396F]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                </div>
            </div>

            <!-- Contenido del Footer -->
            <div class="flex flex-col lg:flex-row items-center justify-between px-8 pb-2 gap-6 z-10 relative">

                <!-- Recuadro Informativo (Izquierda) -->
                <div
                    class="flex items-center gap-3 bg-[#f0f4f8] border border-gray-200 px-5 py-3 rounded-xl w-full lg:w-auto">
                    <div
                        class="bg-[#15396F] text-white rounded-full w-7 h-7 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <span class="text-sm font-serif font-bold italic">i</span>
                    </div>
                    <p class="text-sm text-gray-600 leading-tight">
                        Mostrando registros del <span class="font-bold text-[#15396F]">{{ areas.from || 0 }}</span> al
                        <span class="font-bold text-[#15396F]">{{ areas.to || 0 }}</span> <br class="hidden sm:block" />
                        de un total de <span class="font-bold text-[#15396F]">{{ areas.total || 0 }}</span> registros
                    </p>
                </div>

                <!-- Paginación (Derecha) -->
                <div class="flex items-center gap-1.5 flex-wrap justify-center lg:justify-end w-full lg:max-w-[500px]">
                    <template v-for="(link, k) in areas.links" :key="k">

                        <!-- Botón Habilitado -->
                        <Link v-if="link.url" :href="link.url" preserve-scroll
                            class="flex items-center justify-center w-10 h-10 rounded-md border text-sm font-semibold transition-all duration-200 shadow-sm px-2"
                            :class="link.active
                                ? 'bg-[#15396F] text-white border-[#15396F]'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'">
                        <!-- v-html dentro de span para evitar error de ESLint -->
                        <span v-html="getPaginatorLabel(link.label)"></span>
                        </Link>

                        <!-- Botón Deshabilitado -->
                        <span v-else
                            class="flex items-center justify-center w-10 h-10 rounded-md border border-gray-200 bg-white text-gray-400 text-sm font-semibold cursor-not-allowed shadow-sm px-2">
                            <span v-html="getPaginatorLabel(link.label)"></span>
                        </span>

                    </template>
                </div>
            </div>

            <!-- Decoraciones de las Esquinas (Amarillo y Azul) -->
            <div class="absolute bottom-0 left-0 w-32 h-12 pointer-events-none">
                <div class="absolute bottom-0 left-0 w-full h-full bg-[#15396F]"
                    style="clip-path: polygon(0 100%, 100% 100%, 0 0);"></div>
                <div class="absolute bottom-0 left-0 w-full h-full bg-[#d4af37]"
                    style="clip-path: polygon(0 100%, 100% 100%, 0 20%);"></div>
            </div>
            <div class="absolute bottom-0 right-0 w-32 h-12 pointer-events-none">
                <div class="absolute bottom-0 right-0 w-full h-full bg-[#15396F]"
                    style="clip-path: polygon(100% 100%, 0 100%, 100% 0);"></div>
                <div class="absolute bottom-0 right-0 w-full h-full bg-[#d4af37]"
                    style="clip-path: polygon(100% 100%, 0 100%, 100% 20%);"></div>
            </div>
        </div>

        <!-- Overlay de Carga -->
        <div class="vl-parent">
            <loading v-model:active="isLoading" :can-cancel="false" :is-full-page="true" />
        </div>
    </LayoutMain>
</template>
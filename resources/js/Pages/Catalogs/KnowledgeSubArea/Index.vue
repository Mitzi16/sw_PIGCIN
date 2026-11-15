<script setup>
import LayoutMain from "@/Layouts/LayoutMain.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import { router, Head, Link, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/css/index.css";
import { limitText } from "@/Hooks/useFormato";

const page = usePage();
const allowedRoles = ["Admin", "Editor", "Revisor"];
const canExport = computed(() => {
    const roles = page.props.auth?.user?.roles ?? [];
    return roles.some((role) => allowedRoles.includes(role.name));
});

const props = defineProps({
    name: { type: String, default: "Index" },
    title: { type: String, required: true },
    subareas: {
        type: Object,
        default: () => ({ data: [], links: [], total: 0, from: 0, to: 0 }),
        required: true,
    },
    routeName: { type: String, required: true },
    search: { type: String, default: "" },
});

const search = ref(props.search);
const isLoading = ref(false);

const filterSearch = () => {
    isLoading.value = true;
    router.get(route(`${props.routeName}index`, { search: search.value }), {}, { onFinish: () => (isLoading.value = false) });
};

const cleanFilters = () => {
    search.value = "";
    isLoading.value = true;
    router.get(route(`${props.routeName}index`), {}, { onFinish: () => (isLoading.value = false) });
};

const exportExcel = () => {
    window.open(route(`${props.routeName}export`), "_blank");
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
    <div>
        <Head :title="title" />
        <LayoutMain>
            <!-- Banner Superior -->
            <div class="relative bg-white border border-gray-200 rounded-xl overflow-hidden mb-5 shadow-sm flex items-center min-h-[85px] w-full">
                <div class="absolute inset-y-0 right-0 w-[50%] overflow-hidden pointer-events-none hidden md:block z-0">
                    <img src="/img/azul.png" alt="TecNM" class="absolute right-6 top-1/2 -translate-y-1/2 h-20 w-auto opacity-30 grayscale object-contain" />
                    <div class="absolute -top-10 -bottom-10 right-[25%] w-10 bg-[#15396F] -skew-x-12"></div>
                    <div class="absolute -top-10 -bottom-10 right-[36%] w-2.5 bg-[#7cb0f7] -skew-x-12"></div>
                </div>
                <div class="relative z-10 px-6 py-4 flex items-center w-full md:w-3/4">
                    <div class="w-11 h-11 rounded-full bg-[#15396F] flex items-center justify-center text-white shadow-sm flex-shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z" />
                        </svg>
                    </div>
                    <div class="h-8 w-[2px] bg-[#15396F] mx-4 flex-shrink-0"></div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-[#15396F] tracking-wide">{{ title }}</h1>
                        <p class="text-xs md:text-sm text-gray-500 mt-0.5">Consulta y administra las subáreas del sistema</p>
                    </div>
                </div>
            </div>

            <div class="mb-4 space-y-2">
                <NotificationBar v-if="$page.props.flash?.success" color="success" outline="false">{{ $page.props.flash.success }}</NotificationBar>
                <NotificationBar v-if="$page.props.flash?.error" color="danger" outline="false">{{ $page.props.flash.error }}</NotificationBar>
            </div>

            <!-- Barra de Búsqueda y Botones -->
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6">
                <form @submit.prevent="filterSearch" class="flex w-full xl:w-auto items-center gap-2">
                    <div class="relative w-full sm:w-[350px]">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="search" v-model="search" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-full focus:ring-[#15396F] focus:border-[#15396F] block w-full pl-11 p-2.5 shadow-sm" placeholder="Buscar subárea..." />
                    </div>
                    <button type="submit" class="p-2.5 text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-full border border-gray-300 shadow-sm transition-colors" title="Filtrar">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z" /></svg>
                    </button>
                    <button @click="cleanFilters" type="button" class="p-2.5 text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-full border border-gray-300 shadow-sm transition-colors" title="Limpiar">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 4h-3.5l-1-1h-5l-1 1H5v2h14V4zM6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM8 9h8v10H8V9z" /></svg>
                    </button>
                </form>

                <div class="flex flex-row flex-wrap gap-3 w-full xl:w-auto mt-4 xl:mt-0 justify-start xl:justify-end">
                    <a :href="route(`${routeName}create`)" class="flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-[#15396F] hover:bg-[#0f2853] rounded-full transition-colors shadow-sm cursor-pointer z-10 relative">
                        <svg class="w-4 h-4 pointer-events-none" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" /></svg>
                        Agregar subárea
                    </a>
                    <button v-if="canExport" @click="exportExcel" class="flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-[#15396F] hover:bg-[#0f2853] rounded-full transition-colors shadow-sm z-10 relative">
                        <svg class="w-4 h-4 pointer-events-none" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" /></svg>
                        Exportar a Excel
                    </button>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-white uppercase bg-[#15396F]">
                            <tr>
                                <th scope="col" class="px-4 py-4 w-12 text-center"></th>
                                <th scope="col" class="px-4 py-4 font-semibold text-center border-l border-blue-800/50">Título</th>
                                <th scope="col" class="px-4 py-4 font-semibold text-center border-l border-blue-800/50">Descripción</th>
                                <th scope="col" class="px-4 py-4 font-semibold text-center border-l border-blue-800/50">Fecha Inicio</th>
                                <th scope="col" class="px-4 py-4 font-semibold text-center border-l border-blue-800/50">Fecha Fin</th>
                                <th scope="col" class="px-4 py-4 font-semibold text-center border-l border-blue-800/50">Estatus</th>
                                <th scope="col" class="px-4 py-4 font-semibold text-center border-l border-blue-800/50 w-24">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="subareas && subareas.data && subareas.data.length > 0">
                                <tr v-for="item in subareas.data" :key="item.id" class="bg-white border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-4 text-center text-[#15396F]">
                                        <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M21 4H7V2H5v20h2v-8h14l-2-5 2-5zm-3.86 5.74l1.14 2.76H7V6h11.4l-1.26 3.74z" /></svg>
                                    </td>
                                    <td class="px-4 py-4 text-gray-800 font-medium border-l border-gray-100">{{ item.name }}</td>
                                    <td class="px-4 py-4 text-gray-600 border-l border-gray-100">{{ limitText(item.description, 50) }}</td>
                                    <td class="px-4 py-4 text-gray-700 text-center whitespace-nowrap border-l border-gray-100">{{ item.start_date || '2026-11-06' }}</td>
                                    <td class="px-4 py-4 text-gray-700 text-center whitespace-nowrap border-l border-gray-100">{{ item.end_date || '2026-11-18' }}</td>
                                    <td class="px-4 py-4 text-center border-l border-gray-100">
                                        <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-bold bg-emerald-100 text-emerald-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ item.status || 'Activo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 border-l border-gray-100">
                                        <div class="flex justify-center gap-2">
                                            <Link :href="route(`${routeName}edit`, item.id)" class="bg-[#0d6efd] hover:bg-blue-700 text-white p-2 rounded-lg shadow-sm transition-colors" title="Editar">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" /></svg>
                                            </Link>
                                            <button class="bg-[#dc3545] hover:bg-red-700 text-white shadow-sm p-2 rounded-lg transition-colors" title="Eliminar">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No se encontraron subáreas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="subareas && subareas.links" class="relative pb-12">
                    <div class="flex items-center justify-center gap-4 w-full mt-6 mb-6 px-4 md:px-8 relative z-10">
                        <div class="h-px bg-gray-200 flex-1"></div>
                        <svg class="w-7 h-7 text-[#15396F] bg-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <div class="h-px bg-gray-200 flex-1"></div>
                    </div>

                    <div class="flex flex-col lg:flex-row justify-between items-center gap-6 px-4 md:px-8 mb-4 relative z-10">
                        <div class="flex items-center gap-3 bg-[#f4f7f9] border border-gray-200 px-4 py-2.5 rounded-xl shadow-sm w-full lg:w-auto flex-shrink-0">
                            <div class="w-7 h-7 rounded-full bg-[#15396F] flex items-center justify-center flex-shrink-0 shadow-sm">
                                <span class="text-white text-sm font-serif font-bold italic">i</span>
                            </div>
                            <div class="text-[13px] text-gray-500 leading-tight">
                                Mostrando registros del <span class="font-bold text-[#15396F]">{{ subareas.from || 0 }}</span> al <span class="font-bold text-[#15396F]">{{ subareas.to || 0 }}</span><br />
                                de un total de <span class="font-bold text-[#15396F]">{{ subareas.total || 0 }}</span> registros
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-center lg:justify-end gap-2 w-full lg:max-w-[500px]">
                            <template v-for="(link, k) in subareas.links" :key="k">
                                <Link v-if="link.url" :href="link.url" class="w-10 h-10 flex items-center justify-center rounded-md border text-sm font-semibold transition-all duration-200 shadow-sm" :class="link.active ? 'bg-[#15396F] text-white border-[#15396F]' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'">
                                    <span v-html="getPaginatorLabel(link.label)"></span>
                                </Link>
                                <span v-else class="w-10 h-10 flex items-center justify-center rounded-md border border-gray-300 bg-white text-gray-400 text-sm font-semibold cursor-not-allowed shadow-sm" v-html="getPaginatorLabel(link.label)"></span>
                            </template>
                        </div>
                    </div>

                    <svg class="absolute bottom-0 left-0 w-48 h-16 pointer-events-none z-0" viewBox="0 0 100 50" preserveAspectRatio="none">
                        <polygon points="0,50 100,50 0,10" fill="#d1a744" />
                        <polygon points="0,10 100,50 0,4" fill="#15396F" />
                    </svg>
                    <svg class="absolute bottom-0 right-0 w-48 h-16 pointer-events-none z-0" viewBox="0 0 100 50" preserveAspectRatio="none">
                        <polygon points="100,50 0,50 100,10" fill="#d1a744" />
                        <polygon points="100,10 0,50 100,4" fill="#15396F" />
                    </svg>
                </div>
            </div>

            <div class="vl-parent">
                <loading v-model:active="isLoading" :can-cancel="false" :is-full-page="true" />
            </div>
        </LayoutMain>
    </div>
</template>
<script setup>
import { router } from '@inertiajs/vue3';
import Swal from "sweetalert2";
import { ref, reactive, onMounted } from 'vue';
// Importación de iconos MDI necesarios
import { 
    mdiMagnify, 
    mdiCodeTags, 
    mdiFileDocumentOutline, 
    mdiAccountOutline, 
    mdiFingerprint, 
    mdiQrcode, 
    mdiInformationOutline, 
    mdiHelpCircleOutline, 
    mdiCheckCircle,
    mdiFileSearchOutline
} from "@mdi/js";

const props = defineProps({
    title: { type: String, required: false, default: 'Buscador' },
    code: { type: String, required: false, default: null },
    notFound: { type: Boolean, required: false, default: false },
});

const searchType = ref('code');
const formData = reactive({
  code: '',
  title: '',
  author: '',
  identifier: '',
  searchType: 'code'
});

const errors = reactive({ code: null, title: null, author: null, identifier: null });

const setSearchType = (type) => {
  searchType.value = type;
  formData.code = type === 'code' && props.code ? props.code : '';
  formData.title = '';
  formData.author = '';
  formData.identifier = '';
  formData.searchType = type;
};

const submitForm = () => {
  let searchValue = '';
  let searchField = '';
  
  switch (searchType.value) {
    case 'code': searchValue = formData.code; searchField = 'código'; break;
    case 'title': searchValue = formData.title; searchField = 'título'; break;
    case 'author': searchValue = formData.author; searchField = 'autor'; break;
    case 'identifier': searchValue = formData.identifier; searchField = 'identificador'; break;
  }

  if (!searchValue || searchValue.trim() === "") {
    Swal.fire({ icon: 'warning', title: 'Campo requerido', text: `Por favor ingresa el ${searchField} antes de consultar.`, confirmButtonText: 'OK' });
    return;
  }

  router.get(route('welcome.search.article'), formData);
};

onMounted(() => {
  if (props.code && props.code !== '' && props.notFound) {
    Swal.fire({ icon: 'error', title: 'Artículo no encontrado', text: `No se encontró ningún artículo con el folio: ${props.code}`, confirmButtonText: 'OK' });
  }
  if (props.notFound && !props.code) {
    Swal.fire({ icon: 'error', title: 'Artículo no encontrado', text: `No se encontró ningún artículo con los criterios ingresados.`, confirmButtonText: 'OK' });
  }
});
</script>

<template>
    <section id="estatus" class="relative py-16 bg-white overflow-hidden">
        
        <!-- ============================================== -->
        <!-- NUEVAS DECORACIONES CON IMAGEN                 -->
        <!-- ============================================== -->
        
        <!-- Decoración Esquina Superior Izquierda -->
        <div class="absolute top-0 left-0 w-64 h-64 md:w-80 md:h-80 lg:w-[450px] lg:h-[450px] pointer-events-none z-0">
            <!-- Reemplaza la ruta por tu imagen real -->
           
        </div>

        <!-- Decoración Esquina Inferior Derecha -->
        <div class="absolute bottom-0 right-0 w-64 h-64 md:w-80 md:h-80 lg:w-[400px] lg:h-[400px] pointer-events-none z-0 opacity-80">
            <!-- Si no tienes imagen para la derecha, esta línea voltea la de la izquierda -->
            <img src="/img/bg-decor-right.png" alt="Decoración fondo" class="w-full h-full object-contain object-right-bottom" />
            <!-- Alternativa si usas la misma imagen de la izquierda: 
            <img src="/img/decoracion-izquierda.png" class="w-full h-full object-contain object-right-bottom transform scale-x-[-1]" /> 
            -->
        </div>

        <!-- ============================================== -->

        <div class="relative max-w-screen-xl mx-auto px-4 md:px-8 flex flex-col md:flex-row items-center justify-between gap-12 z-10">
            
            <!-- Columna Izquierda: Formulario -->
            <div class="w-full md:w-3/5 lg:w-1/2 space-y-6">
                
                <!-- Encabezado -->
                <div>
                    <span class="inline-block bg-[#E8F0FE] text-[#1B396A] text-xs font-bold px-4 py-1.5 rounded-full mb-4 tracking-wide">
                        CONSULTA
                    </span>
                    <div class="flex items-center gap-3 mb-2">
                        <!-- Icono de Documento con Lupa -->
                        <svg class="w-10 h-10 text-[#1B396A]" viewBox="0 0 24 24">
                            <path fill="currentColor" :d="mdiFileSearchOutline" />
                        </svg>
                        <h1 class="text-3xl md:text-4xl text-[#1B396A] font-extrabold">
                            Buscador de artículos
                        </h1>
                    </div>
                    <p class="text-gray-600 text-sm md:text-base">
                        Consulta el estado de tu artículo de manera rápida y sencilla.
                    </p>
                </div>

                <!-- Selector de Tipo de Búsqueda -->
                <div class="space-y-3 pt-2">
                    <p class="text-sm font-bold text-gray-800">Tipo de búsqueda:</p>
                    <div class="flex flex-wrap gap-2">
                        <!-- Botón: Por Código -->
                        <button @click="setSearchType('code')" :class="['flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-full transition-colors border', searchType === 'code' ? 'bg-[#1B396A] text-white border-[#1B396A]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50']" type="button">
                            <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiCodeTags" /></svg>
                            Por Código
                        </button>
                        <!-- Botón: Por Título -->
                        <button @click="setSearchType('title')" :class="['flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-full transition-colors border', searchType === 'title' ? 'bg-[#1B396A] text-white border-[#1B396A]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50']" type="button">
                            <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiFileDocumentOutline" /></svg>
                            Por Título
                        </button>
                        <!-- Botón: Por Autor -->
                        <button @click="setSearchType('author')" :class="['flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-full transition-colors border', searchType === 'author' ? 'bg-[#1B396A] text-white border-[#1B396A]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50']" type="button">
                            <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiAccountOutline" /></svg>
                            Por Autor
                        </button>
                        <!-- Botón: Por Identificador -->
                        <button @click="setSearchType('identifier')" :class="['flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-full transition-colors border', searchType === 'identifier' ? 'bg-[#1B396A] text-white border-[#1B396A]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50']" type="button">
                            <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiFingerprint" /></svg>
                            Por Identificador
                        </button>
                    </div>
                </div>

                <!-- Formulario -->
                <form class="space-y-4 pt-4" @submit.prevent="submitForm">
                    
                    <!-- Inputs Dinámicos -->
                    <div v-if="searchType === 'code'">
                        <label class="block text-sm font-bold text-[#1B396A] mb-2 border-l-4 border-[#1B396A] pl-2">Identificador o Número de Artículo</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiQrcode" /></svg>
                            </span>
                            <input v-model="formData.code" type="text" placeholder="Ej: CITCA2025-02-0003-11-08-2025" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#1B396A] focus:border-[#1B396A] outline-none transition-all text-sm" />
                        </div>
                        <p class="flex items-center gap-1 text-xs text-[#1B396A] font-semibold mt-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiInformationOutline" /></svg>
                            Ingresa el número de artículo o folio
                            <svg class="w-4 h-4 cursor-help ml-1" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiHelpCircleOutline" /></svg>
                        </p>
                    </div>

                    <div v-if="searchType === 'title'">
                        <label class="block text-sm font-bold text-[#1B396A] mb-2 border-l-4 border-[#1B396A] pl-2">Título del Artículo</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiFileDocumentOutline" /></svg>
                            </span>
                            <input v-model="formData.title" type="text" placeholder="Ej: Inteligencia artificial..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#1B396A] focus:border-[#1B396A] outline-none transition-all text-sm" />
                        </div>
                    </div>

                    <div v-if="searchType === 'author'">
                        <label class="block text-sm font-bold text-[#1B396A] mb-2 border-l-4 border-[#1B396A] pl-2">Nombre del Autor</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiAccountOutline" /></svg>
                            </span>
                            <input v-model="formData.author" type="text" placeholder="Ej: García, María..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#1B396A] focus:border-[#1B396A] outline-none transition-all text-sm" />
                        </div>
                    </div>

                    <div v-if="searchType === 'identifier'">
                        <label class="block text-sm font-bold text-[#1B396A] mb-2 border-l-4 border-[#1B396A] pl-2">Identificador Único</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiFingerprint" /></svg>
                            </span>
                            <input v-model="formData.identifier" type="text" placeholder="Ej: CITCA2025-02-0003..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#1B396A] focus:border-[#1B396A] outline-none transition-all text-sm" />
                        </div>
                    </div>

                    <!-- Caja de Consejo -->
                    <div class="bg-[#F0F5FA] p-3 rounded-lg flex items-start gap-2 mt-4">
                        <svg class="w-5 h-5 text-[#1B396A] shrink-0" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiCheckCircle" /></svg>
                        <p class="text-xs text-[#1B396A]">
                            <strong>Consejo:</strong> 
                            <span v-if="searchType === 'code'">Ingresa el código completo del artículo.</span>
                            <span v-if="searchType === 'title'">Puedes usar palabras parciales, se buscarán coincidencias.</span>
                            <span v-if="searchType === 'author'">Puedes buscar por nombre o apellido.</span>
                            <span v-if="searchType === 'identifier'">Ingresa cualquier identificador único.</span>
                        </p>
                    </div>

                    <!-- Botón Consultar -->
                    <div class="pt-2">
                        <button type="submit" class="relative group flex items-center justify-center gap-2 bg-[#1B396A] text-white px-8 py-3 rounded-full font-bold text-sm hover:bg-[#132A4F] transition-all overflow-hidden w-auto min-w-[160px]">
                            <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiMagnify" /></svg>
                            Consultar
                            <!-- Decoración de líneas inclinadas del botón -->
                            <span class="absolute right-0 top-0 bottom-0 w-8 bg-white/20 transform skew-x-[-20deg] translate-x-4"></span>
                            <span class="absolute right-0 top-0 bottom-0 w-4 bg-white/10 transform skew-x-[-20deg] translate-x-8"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Columna Derecha: QR y Fondo -->
            <div class="w-full md:w-2/5 flex justify-center relative mt-10 md:mt-0 z-10">
                <!-- Marca de agua (Engranaje) -->
                <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none -z-10">
                    <svg class="w-96 h-96 text-[#1B396A]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                    </svg>
                </div>

                <!-- Caja del QR -->
                <div class="bg-white p-4 rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.08)] border border-gray-50">
                    <img src="/img/qr.gif" class="w-64 h-64 md:w-72 md:h-72 object-contain rounded-2xl" alt="Código QR" />
                </div>
            </div>
            
        </div>
    </section>
</template>
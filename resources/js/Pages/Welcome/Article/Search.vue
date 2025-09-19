<script setup>
import HeadLogo from '@/Components/HeadLogo.vue';
import LayoutWelcome from '@/Layouts/LayoutWelcome.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BaseButtons from '@/Components/BaseButtons.vue';
import BaseButton from '@/Components/BaseButton.vue';
import FormField from '@/Components/FormField.vue';
import FormControl from '@/Components/FormControl.vue';
import {
    mdiArrowRight,
    mdiQrcode
} from "@mdi/js";
import BaseDivider from '@/Components/BaseDivider.vue'; 
import Swal from "sweetalert2";
import { ref, reactive, onMounted } from 'vue';

// Props
const props = defineProps({
    title: { type: String, required: true },
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

const errors = reactive({
  code: null,
  title: null,
  author: null,
  identifier: null
});

// Function to change search type
const setSearchType = (type) => {
  searchType.value = type;
  // Clear other fields when switching search type
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
    case 'code':
      searchValue = formData.code;
      searchField = 'código';
      break;
    case 'title':
      searchValue = formData.title;
      searchField = 'título';
      break;
    case 'author':
      searchValue = formData.author;
      searchField = 'autor';
      break;
    case 'identifier':
      searchValue = formData.identifier;
      searchField = 'identificador';
      break;
  }

  if (!searchValue || searchValue.trim() === "") {
    Swal.fire({
      icon: 'warning',
      title: 'Campo requerido',
      text: `Por favor ingresa el ${searchField} antes de consultar.`,
      confirmButtonText: 'OK'
    });
    return;
  }

  // Use router.get instead of form.get
  router.get(route('welcome.search.article'), formData);
};

onMounted(() => {
  // Si se busca por código directo
  if (props.code && props.code !== '' && props.notFound) {
    Swal.fire({
      icon: 'error',
      title: 'Artículo no encontrado',
      text: `No se encontró ningún artículo con el folio: ${props.code}`,
      confirmButtonText: 'OK'
    });
  }

  // Si se buscó por otro campo y no hay resultados
  if (props.notFound && !props.code) {
    Swal.fire({
      icon: 'error',
      title: 'Artículo no encontrado',
      text: `No se encontró ningún artículo con los criterios ingresados.`,
      confirmButtonText: 'OK'
    });
  }
});

</script>

<template>
  <HeadLogo :title="title" />
  <LayoutWelcome>
    <section class="min-h-screen py-20 bg-white dark:bg-blue-950">
      <div class="max-w-screen-xl mx-auto text-gray-600 gap-x-12 items-center justify-between overflow-hidden md:flex md:px-8">
        <div class="flex-none space-y-5 px-4 sm:max-w-lg md:px-0 lg:max-w-xl">
          <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">NUEVO</p>
          <h1 class="text-4xl text-gray-800 dark:text-blue-400 font-extrabold md:text-5xl">
            Buscador de artículos
          </h1>

          <!-- Added search type selector buttons -->
          <div class="space-y-3">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de búsqueda:</p>
            <div class="flex flex-wrap gap-2">
              <button
                @click="setSearchType('code')"
                :class="[
                  'px-3 py-2 text-sm rounded-lg transition-colors',
                  searchType === 'code' 
                    ? 'bg-blue-600 text-white' 
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                ]"
                type="button"
              >
                Por Código
              </button>
              <button
                @click="setSearchType('title')"
                :class="[
                  'px-3 py-2 text-sm rounded-lg transition-colors',
                  searchType === 'title' 
                    ? 'bg-blue-600 text-white' 
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                ]"
                type="button"
              >
                Por Título
              </button>
              <button
                @click="setSearchType('author')"
                :class="[
                  'px-3 py-2 text-sm rounded-lg transition-colors',
                  searchType === 'author' 
                    ? 'bg-blue-600 text-white' 
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                ]"
                type="button"
              >
                Por Autor
              </button>
              <button
                @click="setSearchType('identifier')"
                :class="[
                  'px-3 py-2 text-sm rounded-lg transition-colors',
                  searchType === 'identifier' 
                    ? 'bg-blue-600 text-white' 
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                ]"
                type="button"
              >
                Por Identificador
              </button>
            </div>
          </div>

          <!-- Dynamic form fields based on search type -->
          <form class="space-y-5" @submit.prevent="submitForm">
            <!-- Search by Code -->
            <div v-if="searchType === 'code'">
              <FormField label="Identificador o Número de Artículo" :error="errors.code" help="Ingresa el número de artículo o folio">
                <FormControl v-model="formData.code" placeholder="Ej: CITCA2025-02-0003-11-08-2025" :icon="mdiQrcode" />
              </FormField>
            </div>

            <!-- Search by Title -->
            <div v-if="searchType === 'title'">
              <FormField label="Título del Artículo" :error="errors.title" help="Ingresa palabras clave del título">
                <FormControl v-model="formData.title" placeholder="Ej: Inteligencia artificial, machine learning..." :icon="mdiQrcode" />
              </FormField>
            </div>

            <!-- Search by Author -->
            <div v-if="searchType === 'author'">
              <FormField label="Nombre del Autor" :error="errors.author" help="Ingresa el nombre o apellido del autor">
                <FormControl v-model="formData.author" placeholder="Ej: García, María, López..." :icon="mdiQrcode" />
              </FormField>
            </div>

            <!-- Search by Identifier -->
            <div v-if="searchType === 'identifier'">
              <FormField label="Identificador Único" :error="errors.identifier" help="Ingresa cualquier identificador único asociado al artículo">
                <FormControl v-model="formData.identifier" placeholder="Ej: CITCA2025-02-0003..." :icon="mdiQrcode" />
              </FormField>
            </div>

            <!-- Added search tips -->
            <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
              <p class="text-xs text-blue-700 dark:text-blue-300">
                <strong>Consejo:</strong> 
                <span v-if="searchType === 'code'">Ingresa el código completo del artículo.</span>
                <span v-if="searchType === 'title'">Puedes usar palabras parciales, se buscarán coincidencias.</span>
                <span v-if="searchType === 'author'">Puedes buscar por nombre o apellido, se encontrarán coincidencias parciales.</span>
                <span v-if="searchType === 'identifier'">Ingresa cualquier identificador único asociado al artículo.</span>
              </p>
            </div>

            <BaseButtons>
              <BaseButton
                :class="[{ 'animate-bounce animate-twice': formData.code || formData.title || formData.author || formData.identifier }]"
                label="Consultar"
                type="submit"
                color="info"
                :icon="mdiArrowRight"
              />
            </BaseButtons>
          </form>
        </div>

        <!-- Imagen -->
        <div class="flex-none mt-14 md:mt-0 hidden md:block">
          <img src="/img/qr.gif" class="lg:w-96 md:w-60 dark:bg-white rounded-3xl" alt="" width="380" />
        </div>

        <!-- Vista móvil -->
        <div class="flex-none w-full block md:hidden">
          <BaseDivider />
          <div class="p-4 text-center flex items-center justify-center h-full text-white lg:bg-black/45">
            <div class="block px-2">
              <h2 class="font-bold text-xl lg:text-3xl mb-4 text-black lg:text-white dark:text-white">
                ¿Quieres registrar un nuevo artículo?
              </h2>
              <p class="text-xs text-gray-600 lg:text-white dark:text-white">
                Hazlo de forma rápida y sencilla
              </p>
              <Link href="/register"
                class="lg:hidden text-xs text-blue-500 underline-offset-4 hover:underline hover:text-blue-700 hover:dark:text-blue-400">
                Saber más
              </Link>

              <BaseButton class="w-auto mt-6 hidden lg:flex" type="submit" color="white"
                label="Volver al inicio" routeName="welcome" small />
            </div>
          </div>
        </div>
      </div>
    </section>
  </LayoutWelcome>
</template>

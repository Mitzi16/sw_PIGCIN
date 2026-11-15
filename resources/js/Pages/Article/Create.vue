<script setup>
import LayoutMain from "@/Layouts/LayoutMain.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import { Link, Head, useForm } from "@inertiajs/vue3";
import { computed, watch, ref, provide } from "vue";
import InputError from "@/Components/InputError.vue";
import Select from "@/Components/Select.vue";
import axios from "axios";
import Swal from "sweetalert2";

const props = defineProps({
  name: { type: String, default: "Create" },
  title: { type: String, required: true },
  routeName: { type: String, required: true },
  areas: { type: Object, default: () => ({}), required: true },
  calls: { type: Object, default: () => ({}), required: true },
  callId: { default: null },
  articleTypes: { type: Array, required: true },
});

const areasData = props.areas.filter((area) => area.parent_id === null) ?? [];
const subareasData = ref([]);
const results = ref(null);

const form = useForm({
  title: null,
  type: "Artículo (6 hojas)",
  abstract: null,
  key_works: null,
  knowledge_area_id: null,
  article_status_id: 1,
  call_id: props.callId, // Si viene por URL, se selecciona por defecto
  file: null,
  authors: [],
  article_type_id: null,
  selectedArea: null,
});

const saveForm = () => {
  form.post(route(`${props.routeName}store`));
};

const filterInstitutions = (search) => {
  axios.get(route(`institution.filter`, search))
    .then((response) => {
      results.value = response.data;
    })
    .catch(function (error) {
      if (error.response && error.response.status == 500) {
        Swal.fire({
          title: "ERRO!",
          text: "Error",
          icon: "info",
          confirmButtonColor: "#15396F",
          confirmButtonText: "Ok!",
        });
      }
    });
};

const handleFileInput = (event) => {
  form.file = event.target.files[0];
};

const getFileURL = computed(() => {
  if (form.file !== null) {
    return URL.createObjectURL(form.file);
  }
});

const prefixes = [
  { id: "Doctor(a)", name: "Doctor(a)" },
  { id: "Maestro(a)", name: "Maestro(a)" },
  { id: "Ingeniero(a)", name: "Ingeniero(a)" },
  { id: "Licenciado(a)", name: "Licenciado(a)" },
  { id: "Profesor(a)", name: "Profesor(a)" },
  { id: "Estudiante(a)", name: "Estudiante(a)" },
];

const addAuthor = () => {
  if (form.authors.length >= 6) {
    Swal.fire({
      title: "ERROR!",
      text: "No se pueden agregar más de 6 autores",
      icon: "error",
      confirmButtonColor: "#15396F",
      confirmButtonText: "Ok!",
    });
    return;
  }

  form.authors.push({
    id: Date.now(),
    is_corresponding: form.authors.length === 0,
    prefix: null,
    name: null,
    surname: null,
    institution_id: null,
    email: null,
  });
};

const removeAuthor = (index) => {
  if (form.authors.length <= 1) {
    Swal.fire({
      title: "Ups!",
      text: "Debe haber al menos 1 autor",
      icon: "warning",
      confirmButtonColor: "#15396F",
      confirmButtonText: "Ok",
    });
    return;
  }

  const wasCorresponding = form.authors[index].is_corresponding;
  form.authors.splice(index, 1);

  if (wasCorresponding && form.authors.length > 0) {
    form.authors[0].is_corresponding = true;
  }
};

const setCorrespondingAuthor = (authorId) => {
  form.authors.forEach((author) => {
    author.is_corresponding = author.id === authorId;
  });
};

const getErrorMessage = (index, field) => {
  const errorKey = `authors.${index}.${field}`;
  return form.errors[errorKey] ?? null;
};

const filterAreas = () => {
  form.knowledge_area_id = null;
  subareasData.value = props.areas.filter(
    (area) => area.parent_id === form.selectedArea
  );
};

const getItem = (id) => {
  if (id) {
    return results.value?.find((result) => result.id === id);
  }
  return null;
};

watch(() => form.selectedArea, filterAreas);

provide("calls", props.calls);
provide("form", form);
</script>

<template>
    <div class="bg-slate-50 min-h-screen relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-blue-50 to-transparent -z-10"></div>

        <Head :title="title" />
        <LayoutMain>
            <form @submit.prevent="saveForm" class="max-w-6xl mx-auto pt-6 pb-24 relative z-10">
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                    <!-- Header Principal -->
                    <div class="bg-[#15396F] px-6 py-3 flex items-center justify-between text-white">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                            </svg>
                            <h2 class="text-sm font-bold">{{ title }}</h2>
                        </div>
                        <Link :href="route(`${routeName}index`)" class="text-white hover:text-red-400 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </Link>
                    </div>

                    <!-- Contenido del Formulario -->
                    <div class="p-6 space-y-6">
                        
                        <!-- CONVOCATORIA (NUEVO CAMPO EN LISTA) -->
                        <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start">
                            <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2"><span class="text-red-500">*</span> Convocatoria:</label>
                            <div class="w-full md:w-3/4">
                                <select v-model="form.call_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50" required>
                                    <option value="" disabled>Selecciona una convocatoria</option>
                                    <option v-for="call in calls" :key="call.id" :value="call.id">
                                        {{ call.title || call.name || 'Convocatoria #' + call.id }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.call_id" class="mt-1" />
                            </div>
                        </div>

                        <!-- Título -->
                        <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start">
                            <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2"><span class="text-red-500">*</span> Título (Inglés):</label>
                            <div class="w-full md:w-3/4">
                                <input v-model="form.title" type="text" placeholder="Teclea el título del artículo en inglés" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50" required>
                                <InputError :message="form.errors.title" class="mt-1" />
                            </div>
                        </div>

                        <!-- Tipo de Artículo -->
                        <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start">
                            <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2"><span class="text-red-500">*</span> Tipo de Artículo:</label>
                            <div class="w-full md:w-3/4">
                                <select v-model="form.article_type_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50" required>
                                    <option value="" disabled>Selecciona un tipo</option>
                                    <option v-for="type in articleTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                </select>
                                <InputError :message="form.errors.article_type_id" class="mt-1" />
                            </div>
                        </div>

                        <!-- Resumen -->
                        <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start">
                            <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2"><span class="text-red-500">*</span> Resumen (Inglés):</label>
                            <div class="w-full md:w-3/4">
                                <textarea v-model="form.abstract" rows="5" placeholder="Teclea tu resumen en inglés" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50 resize-none" required></textarea>
                                <InputError :message="form.errors.abstract" class="mt-1" />
                            </div>
                        </div>

                        <!-- Programas y Áreas -->
                        <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start">
                            <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2">Áreas / Programas:</label>
                            <div class="w-full md:w-3/4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <select v-model="form.selectedArea" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50">
                                        <option value="" disabled>Programa de Estudio</option>
                                        <option v-for="area in areasData" :key="area.id" :value="area.id">{{ area.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <select v-model="form.knowledge_area_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50" required>
                                        <option value="" disabled>Área Prioritaria</option>
                                        <option v-for="subarea in subareasData" :key="subarea.id" :value="subarea.id">{{ subarea.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.knowledge_area_id" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Palabras Clave -->
                        <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start">
                            <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2"><span class="text-red-500">*</span> Palabras Clave (Inglés):</label>
                            <div class="w-full md:w-3/4">
                                <input v-model="form.key_works" type="text" placeholder="Teclea tus palabras clave" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50" required>
                                <InputError :message="form.errors.key_works" class="mt-1" />
                            </div>
                        </div>

                        <!-- Autores -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h3 class="text-base font-bold text-[#15396F]">Lista de autores</h3>
                                    <p class="text-xs text-blue-600 italic">Recuerda marcar el Autor de Correspondencia (AC).</p>
                                </div>
                                <button type="button" @click="addAuthor" :disabled="form.authors.length >= 6" class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-[#0d6efd] hover:bg-blue-700 rounded-lg transition-colors shadow-sm disabled:opacity-50">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                    Agregar autor
                                </button>
                            </div>
                            
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-white bg-[#15396F] uppercase">
                                        <tr>
                                            <th class="px-3 py-3 text-center">No.</th>
                                            <th class="px-3 py-3 text-center">AC</th>
                                            <th class="px-3 py-3">Prefijo</th>
                                            <th class="px-3 py-3">Nombre(s)</th>
                                            <th class="px-3 py-3">Apellido(s)</th>
                                            <th class="px-3 py-3">Institución</th>
                                            <th class="px-3 py-3">Email</th>
                                            <th class="px-3 py-3 text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        <tr v-for="(author, index) in form.authors" :key="index" class="border-b border-gray-100">
                                            <td class="px-3 py-2 text-center">{{ index + 1 }}</td>
                                            <td class="px-3 py-2 text-center">
                                                <input type="radio" name="corresponding-author" :checked="author.is_corresponding" @change="setCorrespondingAuthor(author.id)" class="h-5 w-5 text-[#15396F] focus:ring-[#15396F]">
                                            </td>
                                            <td class="px-3 py-2">
                                                <select v-model="author.prefix" class="w-full px-2 py-1.5 rounded border border-gray-300 text-xs bg-gray-50">
                                                    <option value="" disabled>Prefijo</option>
                                                    <option v-for="p in prefixes" :key="p.id" :value="p.id">{{ p.name }}</option>
                                                </select>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input v-model="author.name" type="text" placeholder="Nombre" class="w-full px-2 py-1.5 rounded border border-gray-300 text-xs bg-gray-50">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input v-model="author.surname" type="text" placeholder="Apellido" class="w-full px-2 py-1.5 rounded border border-gray-300 text-xs bg-gray-50">
                                            </td>
                                            <td class="px-3 py-2">
                                                <Select placeholder="Buscar institución" height="h-8" v-model="author.institution_id" required :options="results" @search="filterInstitutions" :itemSelected="getItem(author.institution_id)" dropdownClass="p-2 relative w-full z-50 bg-white border border-gray-300 mt-1 max-h-32 overflow-y-scroll rounded-md shadow-md"/>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input v-model="author.email" type="email" placeholder="Email" class="w-full px-2 py-1.5 rounded border border-gray-300 text-xs bg-gray-50">
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button type="button" @click="removeAuthor(index)" class="bg-red-50 text-red-500 hover:bg-red-100 p-1.5 rounded transition-colors">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Archivo PDF -->
                        <div class="pt-4 border-t border-gray-200">
                            <label class="block text-sm font-bold text-gray-800 mb-2"><span class="text-red-500">*</span> Cargar archivo PDF:</label>
                            <div class="bg-slate-50 border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center relative">
                                <svg class="w-10 h-10 text-[#15396F] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                <p class="text-sm font-bold text-gray-700">Sube tu archivo aquí</p>
                                <p class="text-xs text-gray-500 mb-4">Solo archivos PDF (Máx. 100MB)</p>
                                
                                <input type="file" @change="handleFileInput" accept=".pdf" class="block w-full max-w-sm text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#15396F] file:text-white hover:file:bg-[#0f2853] transition-colors cursor-pointer"/>
                                
                                <div v-if="form.file" class="mt-4 w-full text-center">
                                    <p class="text-sm font-bold text-[#15396F]">{{ form.file.name }}</p>
                                    <p class="text-xs text-gray-500">{{ (form.file.size / 1000).toFixed(2) }} KB</p>
                                    <div class="w-full flex justify-center mt-4">
                                        <iframe v-if="form.file.type === 'application/pdf'" class="w-full max-w-2xl h-96 border rounded-lg shadow-md" :src="getFileURL"></iframe>
                                    </div>
                                </div>
                            </div>
                            <InputError :message="form.errors.file" class="mt-1" />
                        </div>

                    </div>
                </div>

                <!-- BOTONES INFERIORES -->
                <div class="flex justify-between items-center mt-8 relative">
                    <Link :href="route(`${routeName}index`)" class="px-8 py-2.5 text-sm font-bold text-[#15396F] bg-white border border-gray-300 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 shadow-sm z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing" class="px-8 py-2.5 text-sm font-bold text-white bg-[#15396F] hover:bg-[#0f2853] rounded-full transition-colors flex items-center gap-2 shadow-md disabled:opacity-50 z-10">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z" /></svg>
                        Guardar Contribución
                    </button>
                </div>
            </form>
        </LayoutMain>
    </div>
</template>
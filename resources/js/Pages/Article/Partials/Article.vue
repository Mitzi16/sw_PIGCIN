<script setup>
import {
  mdiPencil,
  mdiTrashCan,
  mdiContentSave,
  mdiClose,
  mdiInformation,
  mdiPlus,
  mdiFileDocumentOutline,
  mdiUpload,
  mdiAccountMultiple
} from "@mdi/js";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import { computed, watch, ref, onMounted, inject } from "vue";
import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import InputError from "@/Components/InputError.vue";
import LabelControl from "@/Components/LabelControl.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import Select from "@/Components/Select.vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import axios from "axios";
import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

const form = inject("form");
const articleTypes = usePage().props.articleTypes;
const props = inject("props");

form.article_type_id = form.article_type_id ?? props.article?.article_type_id ?? null;

const areasData = props.areas.filter((area) => area.parent_id === null) ?? [];
const subareasData = ref([]);
const results = ref(props.institutions);

const fileName = computed(() => form.file?.name ?? props.fileName ?? "Sin archivo");
const fileSize = computed(() => ((form.file?.size ?? props.article.file?.size) / 1000).toFixed(2) + " KB");

const handleFileInput = (event) => {
  form.file = event.target.files[0];
};

const getFileURL = computed(() => form.file ? URL.createObjectURL(form.file) : props.filePath);

const prefixes = [
  { id: "Doctor(a)", name: "Doctor(a)" },
  { id: "Maestro(a)", name: "Maestro(a)" },
  { id: "Ingeniero(a)", name: "Ingeniero(a)" },
  { id: "Licenciado(a)", name: "Licenciado(a)" },
  { id: "Profesor(a)", name: "Profesor(a)" },
  { id: "Estudiante(a)", name: "Estudiante(a)" },
];

const addAuthor = () => {
  form.authors.push({
    is_corresponding: false,
    prefix: null,
    name: null,
    surname: null,
    institution_id: null,
    email: null,
  });
};

const removeAuthor = (index, id) => {
  if (form.authors.length > 0) {
    if (id) {
      axios.delete(route("author.destroy", id)).then((response) => {
          if (response.status === 200) form.authors.splice(index, 1);
        }).catch((error) => {
          if (error.response.status === 500) {
            Swal.fire({
              title: "Error",
              text: "Se produjo un error al eliminar el autor.",
              icon: "error",
              confirmButtonColor: "#3085d6",
              confirmButtonText: "Ok",
            });
          }
        });
    } else {
      form.authors.splice(index, 1);
    }
  }
};

const getErrorMessage = (index, field) => {
  const errorKey = `authors.${index}.${field}`;
  return form.errors[errorKey] ?? null;
};

const filterAreas = () => {
  if (form.selectedArea !== props.article?.knowledge_area?.parent_id) {
    form.knowledge_area_id = null;
  }
  subareasData.value = props.areas.filter((area) => area.parent_id === form.selectedArea);
};

const filterInstitutions = (search) => {
  axios.get(route(`institution.filter`, search)).then((response) => {
      results.value = response.data;
    }).catch(function (error) {
      if (error.response && error.response.status == 500) {
        Swal.fire({
          title: "ERRO!",
          text: "Error al buscar instituciones",
          icon: "info",
          confirmButtonColor: "#3085d6",
          confirmButtonText: "Ok!",
        });
      }
    });
};

const getItem = (id) => {
  if (id) return results.value?.find((result) => result.id === id);
  return null;
};

watch(() => form.selectedArea, filterAreas);
onMounted(() => filterAreas());
</script>

<template>
  <div class="w-full">
    <!-- HEADER AZUL: DATOS DEL ARTÍCULO -->
    <div class="bg-[#15396F] px-6 py-4 flex items-center justify-between text-white rounded-t-lg mb-8">
      <div class="flex items-center gap-3">
        <BaseIcon :path="mdiFileDocumentOutline" size="24" class="text-white" />
        <h2 class="text-lg font-bold tracking-wide">Datos del Artículo</h2>
      </div>
    </div>

    <div class="space-y-6">
      <!-- Título -->
      <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
        <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 pt-2 md:pt-0">
          <span class="text-red-500">*</span> Título:
        </label>
        <div class="w-full md:w-3/4">
          <input v-model="form.title" type="text" placeholder="Título del artículo" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-[#f8fafc] outline-none transition-all placeholder-gray-400" required>
          <InputError :message="form.errors.title" class="mt-1" />
        </div>
      </div>

      <!-- Tipo de artículo -->
      <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
        <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 pt-2 md:pt-0">
          <span class="text-red-500">*</span> Tipo de artículo:
        </label>
        <div class="w-full md:w-3/4">
          <select v-model="form.article_type_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-[#f8fafc] outline-none transition-all" required>
            <option value="" disabled>Selecciona un tipo</option>
            <option v-for="type in articleTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
          </select>
          <InputError :message="form.errors.article_type_id" class="mt-1" />
        </div>
      </div>

      <!-- Resumen -->
      <div class="flex flex-col md:flex-row items-start gap-4">
        <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 pt-2">
          <span class="text-red-500">*</span> Resumen:
        </label>
        <div class="w-full md:w-3/4">
          <textarea v-model="form.abstract" rows="6" placeholder="Resumen del artículo..." class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-[#f8fafc] outline-none transition-all resize-y placeholder-gray-400" required></textarea>
          <InputError :message="form.errors.abstract" class="mt-1" />
        </div>
      </div>

      <!-- Programa y Área -->
      <div class="flex flex-col md:flex-row gap-4 md:gap-6">
        <div class="w-full md:w-1/2 flex flex-col gap-2">
          <label class="text-sm font-bold text-gray-800">Programa de estudio:</label>
          <select v-model="form.selectedArea" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-[#f8fafc] outline-none transition-all">
            <option value="" disabled>Selecciona un programa</option>
            <option v-for="area in areasData" :key="area.id" :value="area.id">{{ area.name }}</option>
          </select>
        </div>
        <div class="w-full md:w-1/2 flex flex-col gap-2">
          <label class="text-sm font-bold text-gray-800"><span class="text-red-500">*</span> Área Prioritaria:</label>
          <select v-model="form.knowledge_area_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-[#f8fafc] outline-none transition-all" required>
            <option value="" disabled>Selecciona un área</option>
            <option v-for="subarea in subareasData" :key="subarea.id" :value="subarea.id">{{ subarea.name }}</option>
          </select>
          <InputError :message="form.errors.knowledge_area_id" class="mt-1" />
        </div>
      </div>

      <!-- Convocatoria y Palabras Clave -->
      <div class="flex flex-col md:flex-row gap-4 md:gap-6">
        <div class="w-full md:w-1/2 flex flex-col gap-2">
          <label class="text-sm font-bold text-gray-800"><span class="text-red-500">*</span> Convocatoria:</label>
          <input v-model="form.call" type="text" disabled placeholder="Convocatoria" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-100 text-sm text-gray-500 cursor-not-allowed outline-none">
          <InputError :message="form.errors.call_id" class="mt-1" />
        </div>
        <div class="w-full md:w-1/2 flex flex-col gap-2">
          <label class="text-sm font-bold text-gray-800"><span class="text-red-500">*</span> Palabras clave:</label>
          <input v-model="form.key_works" type="text" placeholder="Palabras clave" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-[#f8fafc] outline-none transition-all" required>
          <InputError :message="form.errors.key_works" class="mt-1" />
        </div>
      </div>

      <!-- Tabla de Autores -->
      <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-[#15396F] flex items-center gap-2">
            <BaseIcon :path="mdiAccountMultiple" size="20" /> Lista de autores
          </h3>
          <button v-if="form._method" type="button" @click="addAuthor" class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-[#15396F] hover:bg-[#0f2a54] rounded-lg transition-colors shadow-sm">
            <BaseIcon :path="mdiPlus" size="16" /> Agregar autor
          </button>
        </div>
        
        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
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
                <th v-if="form._method" class="px-3 py-3 text-center">Acciones</th>
              </tr>
            </thead>
            <tbody v-if="form._method" class="bg-white divide-y divide-gray-100">
              <tr v-for="(author, index) in form.authors" :key="index" class="hover:bg-gray-50">
                <td class="px-3 py-2 text-center font-semibold">{{ index + 1 }}</td>
                <td class="px-3 py-2 text-center">
                  <input type="radio" name="corresponding-author" :checked="author.is_corresponding" class="h-4 w-4 text-[#15396F] focus:ring-[#15396F]">
                </td>
                <td class="px-3 py-2">
                  <select v-model="author.prefix" class="w-full px-2 py-1.5 rounded border border-gray-300 text-xs bg-gray-50 outline-none focus:ring-1 focus:ring-[#15396F]">
                    <option value="" disabled>Prefijo</option>
                    <option v-for="p in prefixes" :key="p.id" :value="p.id">{{ p.name }}</option>
                  </select>
                  <InputError :message="getErrorMessage(index, 'prefix')" />
                </td>
                <td class="px-3 py-2">
                  <input v-model="author.name" type="text" placeholder="Nombre" class="w-full px-2 py-1.5 rounded border border-gray-300 text-xs bg-gray-50 outline-none focus:ring-1 focus:ring-[#15396F]">
                  <InputError :message="getErrorMessage(index, 'name')" />
                </td>
                <td class="px-3 py-2">
                  <input v-model="author.surname" type="text" placeholder="Apellido" class="w-full px-2 py-1.5 rounded border border-gray-300 text-xs bg-gray-50 outline-none focus:ring-1 focus:ring-[#15396F]">
                  <InputError :message="getErrorMessage(index, 'surname')" />
                </td>
                <td class="px-3 py-2">
                  <Select :itemSelected="getItem(author.institution_id)" placeholder="Buscar" height="h-8" v-model="author.institution_id" required :options="results" @search="filterInstitutions" dropdownClass="p-2 relative w-full z-50 bg-white border border-gray-300 mt-1 max-h-32 overflow-y-auto rounded-md shadow-md"/>
                  <InputError :message="getErrorMessage(index, 'institution_id')" />
                </td>
                <td class="px-3 py-2">
                  <input v-model="author.email" type="email" placeholder="Email" class="w-full px-2 py-1.5 rounded border border-gray-300 text-xs bg-gray-50 outline-none focus:ring-1 focus:ring-[#15396F]">
                  <InputError :message="getErrorMessage(index, 'email')" />
                </td>
                <td class="px-3 py-2 text-center">
                  <button type="button" @click="removeAuthor(index, author?.id)" class="p-1.5 text-red-500 bg-red-50 hover:bg-red-100 rounded transition-colors">
                    <BaseIcon :path="mdiTrashCan" size="16" />
                  </button>
                </td>
              </tr>
            </tbody>
            <tbody v-else class="bg-white divide-y divide-gray-100">
              <tr v-for="(author, index) in form.authors" :key="index">
                <td class="px-3 py-3 text-center">{{ index + 1 }}</td>
                <td class="px-3 py-3 text-center">
                   <input type="radio" disabled :checked="author.is_corresponding" class="h-4 w-4 text-[#15396F]">
                </td>
                <td class="px-3 py-3 text-xs">{{ author.prefix }}</td>
                <td class="px-3 py-3 text-xs font-semibold text-gray-800">{{ author.name }}</td>
                <td class="px-3 py-3 text-xs font-semibold text-gray-800">{{ author.surname }}</td>
                <td class="px-3 py-3 text-xs">{{ author?.institution?.name ?? 'Sin registro' }}</td>
                <td class="px-3 py-3 text-xs">{{ author.email }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Cargar PDF -->
      <div class="mt-8 pt-6 border-t border-gray-200">
        <label class="block text-sm font-bold text-gray-800 mb-3"><span class="text-red-500">*</span> Cargar archivo PDF:</label>
        
        <div class="bg-[#f8fafc] border-2 border-dashed border-gray-300 rounded-xl p-8 flex flex-col items-center justify-center relative">
          <BaseIcon :path="mdiUpload" size="40" class="text-[#15396F] mb-3" />
          <p class="text-sm font-bold text-gray-700">Sube tu archivo aquí</p>
          <p class="text-xs text-gray-500 mb-4">Solo archivos PDF (Máx. 100MB)</p>
          
          <input v-if="form._method" @input="handleFileInput" type="file" accept=".pdf" class="block w-full max-w-sm text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#15396F] file:text-white hover:file:bg-[#0f2853] transition-colors cursor-pointer"/>
          
          <div class="mt-4 w-full text-center">
            <p class="text-sm font-bold text-[#15396F]">Nombre: {{ fileName }}</p>
            <p class="text-xs text-gray-500">Tamaño: {{ fileSize }}</p>
            
            <div v-if="form.file || props.filePath" class="w-full flex justify-center mt-6">
              <iframe class="w-full max-w-3xl h-96 border rounded-lg shadow-md" :src="getFileURL" />
            </div>
          </div>
          
          <InputError :message="form.errors.file" class="mt-2" />
        </div>
      </div>
    </div>
  </div>
</template>
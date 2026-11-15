<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
  mdiAccount,
  mdiAsterisk,
  mdiEmail,
  mdiMagnify,
  mdiEye,
  mdiEyeOff,
} from "@mdi/js";
import LayoutGuest from "@/Layouts/LayoutGuest.vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import FormValidationErrors from "@/Components/FormValidationErrors.vue";
import JetInputError from "@/Components/InputError.vue";
import { watch, ref } from "vue";
import axios from "axios";
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/css/index.css";
import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
import HeadLogo from "@/Components/HeadLogo.vue";

const props = defineProps({
  areas: { type: Object, required: true, default: {} },
  call_id: { type: String, default: null },
  selected_call: { type: Object, default: null }, // 👈 Recibimos los datos de la convocatoria
});

// Datos de áreas
const areasData = props.areas.filter((area) => area.parent_id === null) ?? [];
const subareasData = ref([]);

// Roles
const roles = [
  'Postulante', 'Revisor', 'Editor', 'Moderador',
  'Conferencista', 'Tallerista', 'Staff'
];

const form = useForm({
  curp: null,
  name: null,
  email: null,
  password: null,
  password_confirmation: null,
  knowledge_area_id: null,
  institution_id: null,
  selectedArea: null,
  role: 'Postulante',
  role_name: 'Postulante',
  call_id: props.call_id || null, // 👈 Guardamos el ID en el formulario
});

const isLoading = ref(false);
const showPassword = ref(false);
const showPasswordConfirm = ref(false);

// --- LÓGICA DE BÚSQUEDA DE INSTITUCIONES ---
const searchInstitution = ref("");
const showInstitutionDropdown = ref(false);
const results = ref([]);
let searchTimeout = null;

const searchInstitutions = (query) => {
  if (searchTimeout) clearTimeout(searchTimeout);
  if (!query || query.length < 2) {
    results.value = [];
    return;
  }
  searchTimeout = setTimeout(() => {
    axios.get(route('institution.filter'), { params: { search: query } })
      .then((response) => {
        results.value = Array.isArray(response.data) ? response.data : [];
      })
      .catch((error) => console.log("Error:", error));
  }, 400);
};

watch(searchInstitution, (newVal) => {
  const currentSelected = results.value.find(i => i.id === form.institution_id);
  if (currentSelected && currentSelected.name !== newVal) {
    form.institution_id = null;
  }
  if (newVal) {
    showInstitutionDropdown.value = true;
    searchInstitutions(newVal);
  } else {
    showInstitutionDropdown.value = false;
    results.value = [];
  }
});

const selectInstitution = (institution) => {
  form.institution_id = institution.id;
  searchInstitution.value = institution.name;
  showInstitutionDropdown.value = false;
};

const hideInstitutionDropdown = () => {
  setTimeout(() => { showInstitutionDropdown.value = false; }, 150);
};
// ---------------------------------------------

const submit = () => {
  if (!form.institution_id) {
    Swal.fire({ title: "Atención", text: "Debes seleccionar una institución de la lista", icon: "warning", confirmButtonColor: "#1B396A" });
    return;
  }
  form.post(route("register.postulantes"));
};

const getCurp = () => {
  if (!form.curp) return;
  isLoading.value = true;
  axios.get(route(`user.getCurp`, form.curp.toUpperCase()))
    .then((response) => {
      isLoading.value = false;
      const data = response.data.dataCurp;
      form.name = data.nombres.toUpperCase() + " " + data.apellidoPaterno.toUpperCase() + " " + data.apellidoMaterno.toUpperCase();
    })
    .catch(function (error) {
      if (error.response && error.response.status == 500) {
        Swal.fire({ title: "CURP Incorrecta!", text: "La CURP ingresada no es valida", icon: "info", confirmButtonColor: "#1B396A" });
      }
      form.reset("name");
      isLoading.value = false;
    });
};

const filterAreas = () => {
  form.knowledge_area_id = null;
  subareasData.value = props.areas.filter((area) => area.parent_id === form.selectedArea);
};

watch(() => form.selectedArea, filterAreas);
</script>

<template>
  <div class="vl-parent">
    <loading v-model:active="isLoading" :can-cancel="false" :is-full-page="true" />
  </div>
  <HeadLogo title="Registro" />

  <LayoutGuest>
    <div class="relative min-h-screen bg-white flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8 overflow-hidden">
      <div class="absolute top-1/2 left-0 -translate-y-1/2 -translate-x-1/4 opacity-10 pointer-events-none z-0">
        <img src="/img/azul.png" alt="Engrane de fondo" class="w-[600px] h-[600px] object-contain" />
      </div>

      <div class="relative z-10 w-full max-w-xl bg-white rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.08)] border border-gray-100 overflow-hidden">
        <div class="absolute top-0 left-0 w-28 h-16 bg-[#1B396A] z-0" style="clip-path: polygon(0 0, 100% 0, 0 100%);"></div>
        <div class="absolute top-3 left-3 z-10 text-white">
          <img src="/img/tecnm_white.png" alt="Logo" class="w-7 h-7 object-contain" />
        </div>

        <div class="pt-8 pb-4 px-6 border-b border-gray-100 relative z-10">
          <h1 class="text-xl font-extrabold text-[#1B396A] text-center">Crear Cuenta</h1>
          <p class="text-xs text-gray-500 mt-1 text-center">Ingresa tus datos para registrarte en el evento</p>
        </div>

        <!-- 👇 BANNER DE LA CONVOCATORIA SELECCIONADA 👇 -->
        <div v-if="selected_call" class="mx-6 mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-start gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <p class="text-xs font-bold text-blue-800">Registrándote a la convocatoria:</p>
            <p class="text-sm font-semibold text-blue-900">{{ selected_call.title }}</p>
          </div>
        </div>

        <form @submit.prevent="submit" class="p-6 space-y-4 relative z-10 bg-white">
          <FormValidationErrors />
          <!-- ... (RESTRO DEL FORMULARIO INTACTO) ... -->
          
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">CURP:</label>
            <div class="flex gap-2">
              <input v-model="form.curp" maxlength="18" placeholder="Tu curp"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-[#1B396A] focus:border-[#1B396A] block p-2 uppercase transition-colors" />
              <button type="button" @click="getCurp()" class="bg-[#1B396A] text-white px-3 py-2 rounded-lg font-medium text-xs hover:bg-[#132A4F] transition-colors shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                <BaseIcon :path="mdiMagnify" size="14" /> Buscar
              </button>
            </div>
            <JetInputError class="mt-1" :message="form.errors.curp" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Nombre Completo:</label>
              <input v-model="form.name" required placeholder="Tu nombre" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-[#1B396A] focus:border-[#1B396A] block p-2 transition-colors" />
              <JetInputError class="mt-1" :message="form.errors.name" />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Correo Electrónico:</label>
              <input v-model="form.email" type="email" required placeholder="correo@ejemplo.com" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-[#1B396A] focus:border-[#1B396A] block p-2 transition-colors" />
              <JetInputError class="mt-1" :message="form.errors.email" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Contraseña:</label>
              <div class="relative">
                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required placeholder="••••••••" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-[#1B396A] focus:border-[#1B396A] block p-2 pr-8 transition-colors" />
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-600">
                  <BaseIcon :path="showPassword ? mdiEyeOff : mdiEye" size="16" />
                </button>
              </div>
              <JetInputError class="mt-1" :message="form.errors.password" />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Confirmar Contraseña:</label>
              <div class="relative">
                <input v-model="form.password_confirmation" :type="showPasswordConfirm ? 'text' : 'password'" required placeholder="••••••••" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-[#1B396A] focus:border-[#1B396A] block p-2 pr-8 transition-colors" />
                <button type="button" @click="showPasswordConfirm = !showPasswordConfirm" class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-600">
                  <BaseIcon :path="showPasswordConfirm ? mdiEyeOff : mdiEye" size="16" />
                </button>
              </div>
              <JetInputError class="mt-1" :message="form.errors.password_confirmation" />
            </div>
          </div>

          <div class="relative">
            <label class="block text-xs font-bold text-gray-700 mb-1">Institución de Procedencia:</label>
            <div class="bg-gray-50 border border-gray-200 rounded-lg overflow-hidden">
              <input v-model="searchInstitution" type="text" placeholder="Escribe el nombre de tu institución..." class="w-full bg-transparent border-none text-gray-700 text-sm focus:ring-0 p-2 block" @focus="showInstitutionDropdown = true" @blur="hideInstitutionDropdown" autocomplete="off" required />
            </div>
            <input type="hidden" v-model="form.institution_id" required>
            <ul v-if="showInstitutionDropdown && (results.length > 0 || searchInstitution.length >= 2)" class="absolute z-50 w-full bg-white border border-gray-200 mt-1 rounded-lg shadow-lg max-h-48 overflow-y-auto">
              <li v-for="inst in results" :key="inst.id" @mousedown.prevent="selectInstitution(inst)" class="cursor-pointer p-2 hover:bg-gray-100 text-sm text-gray-700 transition-colors border-b border-gray-50 last:border-0">
                {{ inst.name }}
              </li>
              <li v-if="results.length === 0 && searchInstitution.length >= 2" class="p-2 text-sm text-gray-500 text-center">No se encontraron resultados</li>
            </ul>
            <JetInputError class="mt-1" :message="form.errors.institution_id" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Área Prioritaria:</label>
              <select v-model="form.selectedArea" class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-[#1B396A] focus:border-[#1B396A] block p-2 transition-colors">
                <option :value="null" disabled selected>Seleccionar área...</option>
                <option v-for="area in areasData" :key="area.id" :value="area.id">{{ area.name }}</option>
              </select>
              <JetInputError class="mt-1" :message="form.errors.selectedArea" />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Subárea:</label>
              <select v-model="form.knowledge_area_id" :disabled="!form.selectedArea" class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-[#1B396A] focus:border-[#1B396A] block p-2 transition-colors disabled:opacity-50">
                <option :value="null" disabled selected>Seleccionar subárea...</option>
                <option v-for="subarea in subareasData" :key="subarea.id" :value="subarea.id">{{ subarea.name }}</option>
              </select>
              <JetInputError class="mt-1" :message="form.errors.knowledge_area_id" />
            </div>
          </div>

          <div class="pt-1">
            <label class="block text-xs font-bold text-gray-700 mb-2">¿Cuál será tu participación?</label>
            <div class="flex flex-wrap gap-1.5">
              <button type="button" v-for="roleOption in roles" :key="roleOption" @click="form.role = roleOption" :class="['px-2.5 py-1 rounded-md text-[11px] font-bold transition-all border', form.role === roleOption ? 'bg-[#1B396A] text-white border-[#1B396A] shadow-sm' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100']">
                {{ roleOption }}
              </button>
            </div>
            <JetInputError class="mt-1" :message="form.errors.role" />
          </div>

          <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 relative z-10">
            <p class="text-[11px] text-gray-500 order-2 sm:order-1">
              ¿Ya tienes cuenta? <Link href="/login" class="text-[#1B396A] font-bold hover:underline">Inicia Sesión</Link>
            </p>
            <button type="submit" :disabled="form.processing" class="w-full sm:w-auto bg-[#1B396A] text-white px-5 py-2.5 rounded-lg font-bold text-xs hover:bg-[#132A4F] transition-all shadow-md disabled:opacity-50 order-1 sm:order-2">
              Crear cuenta
            </button>
          </div>
        </form>

        <div class="absolute bottom-0 right-0 w-full h-12 pointer-events-none z-0">
          <svg viewBox="0 0 1000 100" preserveAspectRatio="none" class="w-full h-full">
            <path d="M0,100 C300,100 400,0 1000,0 L1000,100 Z" fill="#1B396A" opacity="0.08"></path>
            <path d="M200,100 C500,100 600,20 1000,20 L1000,100 Z" fill="#1B396A" opacity="0.15"></path>
          </svg>
        </div>
      </div>
    </div>
  </LayoutGuest>
</template>
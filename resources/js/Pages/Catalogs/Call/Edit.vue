<script setup>
import LayoutMain from "@/Layouts/LayoutMain.vue";
import { useForm, Head, Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import axios from "axios";
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { mdiTrashCan } from "@mdi/js";

// ✅ Ziggy: necesario para usar route() dentro del <script setup>
// Ziggy v2
import { route } from 'ziggy-js';
// Si usas Ziggy v1, cambia la línea anterior por:
// import route from 'ziggy-js';

const props = defineProps({
    name: 'Edit',
    title: { type: String, required: true },
    routeName: { type: String, required: true },
    call: { type: Object, required: true },
});

const results = ref([]);
const institutionSearch = ref(props.call.institution?.name || '');
const showDropdown = ref(false);
// Variable para la vista previa de la imagen
const imagePreview = ref(props.call.image ? '/storage/' + props.call.image.path : null);

// ✅ forceFormData va como SEGUNDO argumento, no dentro del objeto de datos
const form = useForm(
    {
        _method: 'patch',
        id: props.call.id,
        title: props.call.title,
        description: props.call.description,
        start_date: props.call.start_date,
        end_date: props.call.end_date,
        status: props.call.status,
        url: props.call.url,
        file: null,
        file_id: props.call.file?.id,
        image: null,
        image_id: props.call.image?.id,
        institution_id: props.call.institution_id,
        publication_start: props.call.publication_start,
        publication_end: props.call.publication_end,
        registration_start: props.call.registration_start,
        registration_end: props.call.registration_end,
        application_start: props.call.application_start,
        application_end: props.call.application_end,
        evaluation_start: props.call.evaluation_start,
        evaluation_end: props.call.evaluation_end,
        results_start: props.call.results_start,
        results_end: props.call.results_end,
    },
    { forceFormData: true }
);

// --- LÓGICA DEL CALENDARIO ---
const currentStep = ref(1);
const steps = [
    { id: 1, name: 'Publicación', desc: 'Publicación de la convocatoria', start_field: 'publication_start', end_field: 'publication_end', color: 'bg-[#15396F]', light: 'bg-blue-100 text-[#15396F]' },
    { id: 2, name: 'Registro', desc: 'Periodo para inscribirse', start_field: 'registration_start', end_field: 'registration_end', color: 'bg-emerald-600', light: 'bg-emerald-100 text-emerald-700' },
    { id: 3, name: 'Postulación', desc: 'Periodo de postulación', start_field: 'application_start', end_field: 'application_end', color: 'bg-purple-600', light: 'bg-purple-100 text-purple-700' },
    { id: 4, name: 'Evaluación', desc: 'Periodo de revisión', start_field: 'evaluation_start', end_field: 'evaluation_end', color: 'bg-amber-500', light: 'bg-amber-100 text-amber-700' },
    { id: 5, name: 'Resultados', desc: 'Publicación de resultados', start_field: 'results_start', end_field: 'results_end', color: 'bg-rose-600', light: 'bg-rose-100 text-rose-700' },
];

const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
const daysOfWeek = ["DO", "LU", "MA", "MI", "JU", "VI", "SA"];
const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());

const getDaysInMonth = (month, year) => new Date(year, month + 1, 0).getDate();
const getFirstDayOfMonth = (month, year) => new Date(year, month, 1).getDay();

const blankDays = computed(() => {
    const days = [];
    const firstDay = getFirstDayOfMonth(currentMonth.value, currentYear.value);
    for (let i = 0; i < firstDay; i++) days.push('');
    return days;
});

const daysInMonth = computed(() => {
    const days = [];
    const totalDays = getDaysInMonth(currentMonth.value, currentYear.value);
    for (let i = 1; i <= totalDays; i++) days.push(i);
    return days;
});

const prevMonth = () => {
    if (currentMonth.value === 0) { currentMonth.value = 11; currentYear.value--; }
    else { currentMonth.value--; }
};

const nextMonth = () => {
    if (currentMonth.value === 11) { currentMonth.value = 0; currentYear.value++; }
    else { currentMonth.value++; }
};

const formatDate = (day) => {
    if (!day) return null;
    const m = String(currentMonth.value + 1).padStart(2, '0');
    const d = String(day).padStart(2, '0');
    return `${currentYear.value}-${m}-${d}`;
};

const selectDate = (day) => {
    if (!day) return;
    const dateStr = formatDate(day);
    const stepData = steps[currentStep.value - 1];
    const startVal = form[stepData.start_field];
    const endVal = form[stepData.end_field];

    if (!startVal || (startVal && endVal) || dateStr < startVal) {
        form[stepData.start_field] = dateStr;
        form[stepData.end_field] = null;
    } else {
        form[stepData.end_field] = dateStr;
    }
};

const getDayClass = (day) => {
    const dateStr = formatDate(day);
    if (!dateStr) return 'text-gray-700 bg-white hover:bg-gray-100 border border-transparent rounded-md';

    let baseClass = 'text-gray-700 bg-white hover:bg-gray-100 border border-transparent rounded-md';
    let isCurrentStep = false;

    for (let i = 0; i < steps.length; i++) {
        const step = steps[i];
        const start = form[step.start_field];
        const end = form[step.end_field];

        // Si el día está dentro del rango (inicio y fin seleccionados)
        if (start && end && dateStr >= start && dateStr <= end) {
            if (dateStr === start || dateStr === end) {
                baseClass = `${step.color} text-white font-bold rounded-md shadow-sm`;
            } else {
                baseClass = `${step.light} font-medium rounded-md`;
            }
            if (currentStep.value === step.id) isCurrentStep = true;
        }
        // Si solo se ha seleccionado la fecha de inicio (para que se pinte de inmediato)
        else if (start && !end && dateStr === start) {
            baseClass = `${step.color} text-white font-bold rounded-md shadow-sm`;
            if (currentStep.value === step.id) isCurrentStep = true;
        }
    }

    // Anillo indicador si es la etapa actual
    return isCurrentStep ? `${baseClass} ring-2 ring-offset-1 ring-gray-400 z-10 relative` : baseClass;
};

// --- BUSCADOR DE INSTITUCIONES ---
const filterInstitutions = async (search) => {
    try {
        const url = route('institution.filter', { search });
        console.log('🔍 Buscando en:', url);
        const res = await axios.get(url);
        const payload = res.data;
        results.value = Array.isArray(payload)
            ? payload
            : (Array.isArray(payload?.data) ? payload.data : []);
        console.log('✅ Resultados:', results.value);
    } catch (err) {
        console.error('❌ Error:', err);
        results.value = [];
    }
};

const onInstitutionInput = (e) => {
    institutionSearch.value = e.target.value;
    showDropdown.value = true;
    if (institutionSearch.value === '') {
        form.institution_id = null;
        results.value = [];
    }
    if (institutionSearch.value.length >= 2) filterInstitutions(institutionSearch.value);
};

const selectInstitution = (inst) => {
    form.institution_id = inst.id;
    institutionSearch.value = inst.name;
    showDropdown.value = false;
};

const closeDropdown = () => {
    setTimeout(() => { showDropdown.value = false; }, 200);
};

// --- FUNCIONES PARA ARCHIVOS ---
const formatSize = (bytes) => {
    if (!bytes) return '0 KB';
    const kb = bytes / 1024;
    return kb > 1024 ? (kb / 1024).toFixed(1) + ' MB' : kb.toFixed(0) + ' KB';
};

const removeFile = () => { form.file = null; form.file_id = null; };
const removeImage = () => {
    form.image = null;
    form.image_id = null;
    imagePreview.value = null; // Limpiamos la vista previa
};

const onChangeFile = (e) => {
    const file = e.target.files?.[0] ?? null;
    console.log('📁 PDF seleccionado:', file);
    form.file = file;
    e.target.value = '';
};

const onChangeImage = (e) => {
    const file = e.target.files?.[0] ?? null;
    console.log('🖼️ Imagen seleccionada:', file);
    form.image = file;

    if (file) {
        imagePreview.value = URL.createObjectURL(file); // Creamos la URL aquí
    }
    e.target.value = '';
};


// --- GUARDAR Y ELIMINAR ---
const saveForm = () => {
    if (!form.publication_start || !form.results_end) {
        alert("Por favor, asegúrate de seleccionar la fecha de inicio en 'Publicación' y la fecha de fin en 'Resultados'.");
        return;
    }
    form.start_date = form.publication_start;
    form.end_date = form.results_end;

    form.post(route(`${props.routeName}update`, props.call.id), {
        onError: (errors) => { console.log("Errores:", errors); }
    });
};

const deleteForm = () => {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "Esta acción no se puede revertir",
        icon: "warning",
        showCancelButton: true,
        cancelButtonText: "Cancelar",
        cancelButtonColor: "#6b7280",
        confirmButtonColor: "#E02424",
        confirmButtonText: "Sí, eliminar registro",
    }).then((res) => {
        if (res.isConfirmed) {
            form.delete(route(`${props.routeName}destroy`, props.call.id));
        }
    });
};
</script>

<template>
    <div class="bg-slate-50 min-h-screen relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-blue-50 to-transparent -z-10"></div>

        <Head :title="title" />
        <LayoutMain>
            <form @submit.prevent="saveForm" class="max-w-5xl mx-auto pt-6 pb-24 relative z-10">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-[#15396F] px-6 py-3 flex items-center justify-between text-white">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                            </svg>
                            <h2 class="text-sm font-bold">{{ title }}</h2>
                        </div>
                        <Link :href="route(`${routeName}index`)"
                            class="text-white hover:text-red-400 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </Link>
                    </div>

                    <div class="p-6 space-y-8">
                        <!-- DATOS -->
                        <div>
                            <h3
                                class="text-base font-bold text-[#15396F] mb-4 flex items-center gap-2 border-b border-gray-200 pb-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                                </svg>
                                Datos de la convocatoria
                            </h3>
                            <div class="space-y-4">
                                <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start md:items-center">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2"><span
                                            class="text-red-500">*</span> Título:</label>
                                    <input v-model="form.title" type="text" placeholder="Nombre de la convocatoria"
                                        class="w-full md:w-3/4 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50"
                                        required>
                                </div>
                                <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2"><span
                                            class="text-red-500">*</span> Descripción:</label>
                                    <textarea v-model="form.description" rows="3"
                                        placeholder="Descripción de la convocatoria..."
                                        class="w-full md:w-3/4 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50 resize-none"
                                        required></textarea>
                                </div>
                                <div
                                    class="flex flex-col md:flex-row gap-2 md:gap-6 items-start md:items-center relative">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2"><span
                                            class="text-red-500">*</span> Institución:</label>
                                    <div class="relative w-full md:w-3/4">
                                        <input type="text" v-model="institutionSearch" @input="onInstitutionInput"
                                            @focus="showDropdown = true" @blur="closeDropdown"
                                            placeholder="Buscar institución..."
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50"
                                            required>
                                        <svg class="w-5 h-5 text-gray-400 absolute right-3 top-2 pointer-events-none"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <div v-if="showDropdown && results && results.length > 0"
                                            class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                            <ul class="py-1">
                                                <li v-for="inst in results" :key="inst.id"
                                                    @mousedown.prevent="selectInstitution(inst)"
                                                    class="px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#15396F] cursor-pointer transition-colors">
                                                    {{ inst.name }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start md:items-center">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800"><span
                                            class="text-red-500">*</span> Estatus:</label>
                                    <div class="w-full md:w-3/4 flex items-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" v-model="form.status" :true-value="1"
                                                :false-value="0" class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#15396F]">
                                            </div>
                                            <span class="ml-3 text-sm font-medium text-gray-700">{{ form.status === 1 ?
                                                'Activa' : 'Inactiva' }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start md:items-center">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800">URL
                                        (opcional):</label>
                                    <div class="relative w-full md:w-3/4">
                                        <input v-model="form.url" type="url" placeholder="Ej. https://youtube.com/..."
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50">
                                        <svg class="w-5 h-5 text-gray-400 absolute right-3 top-2 pointer-events-none"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                            </path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- ARCHIVOS -->
                                <div class="pt-4">
                                    <div class="flex items-center gap-2 mb-4">
                                        <svg class="w-6 h-6 text-[#15396F]" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
                                        </svg>
                                        <h3 class="text-base font-bold text-[#15396F]">Archivos de la convocatoria</h3>
                                        <div class="flex-1 h-px bg-gray-200 ml-2"></div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- TARJETA PDF -->
                                        <div class="border border-blue-200 rounded-xl overflow-hidden shadow-sm">
                                            <div
                                                class="bg-[#15396F] px-4 py-2.5 flex items-center justify-between relative overflow-hidden">
                                                <div class="flex items-center gap-2 text-white z-10">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6z" />
                                                    </svg>
                                                    <span class="text-sm font-bold">Archivo (PDF) <span
                                                            class="text-red-400">*</span></span>
                                                </div>
                                                <div
                                                    class="absolute right-0 top-0 bottom-0 w-24 opacity-20 pointer-events-none">
                                                    <div
                                                        class="w-full h-full bg-[repeating-linear-gradient(45deg,transparent,transparent_5px,#fff_5px,#fff_10px)]">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-4 bg-white">
                                                <!-- ✅ Subida inicial (sin archivo actual ni seleccionado) -->
                                                <label v-if="!form.file && !form.file_id"
                                                    class="border-2 border-dashed border-blue-200 bg-blue-50/30 rounded-lg p-6 flex flex-col items-center justify-center cursor-pointer hover:bg-blue-50 transition-colors w-full">
                                                    <svg class="w-8 h-8 text-[#15396F] mb-2" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12">
                                                        </path>
                                                    </svg>
                                                    <p class="text-sm font-bold text-[#15396F]">Subir PDF</p>
                                                    <p class="text-[10px] text-gray-500 mt-1">Máx. 10 MB</p>
                                                    <input type="file" @change="onChangeFile" class="hidden"
                                                        accept=".pdf" />
                                                </label>

                                                <!-- ✅ Vista previa + botón Cambiar archivo -->
                                                <div v-else class="flex items-center gap-4">
                                                    <div
                                                        class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0 relative">
                                                        <svg class="w-8 h-8 text-red-500" fill="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path
                                                                d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 7.5c0 .83-.67 1.5-1.5 1.5H9v2H7.5V7H10c.83 0 1.5.67 1.5 1.5v1zm5 2c0 .83-.67 1.5-1.5 1.5h-2.5V7H15c.83 0 1.5.67 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zM4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm10 5.5h1v-3h-1v3z" />
                                                        </svg>
                                                        <span
                                                            class="absolute -bottom-1 bg-red-600 text-white text-[8px] font-bold px-1.5 py-0.5 rounded">PDF</span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-bold text-gray-800 truncate"
                                                            :title="form.file ? form.file.name : props.call.file?.name">
                                                            {{ form.file ? form.file.name : props.call.file?.name }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 mt-0.5">
                                                            {{ formatSize(form.file ? form.file.size :
                                                                props.call.file?.size) }}
                                                        </p>
                                                        <div class="mt-1.5 flex items-center gap-3 flex-wrap">
                                                            <label
                                                                class="cursor-pointer inline-flex items-center gap-1 text-[11px] font-bold text-[#15396F] hover:underline">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12">
                                                                    </path>
                                                                </svg>
                                                                Cambiar archivo
                                                                <input type="file" @change="onChangeFile" class="hidden"
                                                                    accept=".pdf" />
                                                            </label>
                                                            <div
                                                                class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full text-[10px] font-bold">
                                                                <svg class="w-3 h-3" fill="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path
                                                                        d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                                                </svg>
                                                                {{ form.file ? 'Nuevo PDF' : 'PDF actual' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" @click="removeFile"
                                                        class="w-8 h-8 rounded-full bg-red-50 hover:bg-red-100 flex items-center justify-center text-red-500 transition-colors flex-shrink-0">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                            <path
                                                                d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- TARJETA IMAGEN -->
                                        <div class="border border-blue-200 rounded-xl overflow-hidden shadow-sm">
                                            <div
                                                class="bg-[#15396F] px-4 py-2.5 flex items-center justify-between relative overflow-hidden">
                                                <div class="flex items-center gap-2 text-white z-10">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" />
                                                    </svg>
                                                    <span class="text-sm font-bold">Imagen (opcional)</span>
                                                </div>
                                                <div
                                                    class="absolute right-0 top-0 bottom-0 w-24 opacity-20 pointer-events-none">
                                                    <div
                                                        class="w-full h-full bg-[repeating-linear-gradient(45deg,transparent,transparent_5px,#fff_5px,#fff_10px)]">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-4 bg-white">
                                                <!-- ✅ Subida inicial (sin imagen actual ni seleccionada) -->
                                                <label v-if="!form.image && !form.image_id"
                                                    class="border-2 border-dashed border-blue-200 bg-blue-50/30 rounded-lg p-6 flex flex-col items-center justify-center cursor-pointer hover:bg-blue-50 transition-colors w-full">
                                                    <svg class="w-8 h-8 text-[#15396F] mb-2" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12">
                                                        </path>
                                                    </svg>
                                                    <p class="text-sm font-bold text-[#15396F]">Subir Imagen</p>
                                                    <p class="text-[10px] text-gray-500 mt-1">JPG, PNG (máx. 5 MB)</p>
                                                    <input type="file" @change="onChangeImage" class="hidden"
                                                        accept="image/*" />
                                                </label>

                                                <!-- ✅ Vista previa + botón Cambiar imagen -->
                                                <div v-else class="flex items-center gap-4">
                                                    <div
                                                        class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 overflow-hidden border border-gray-200">
                                                        <img v-if="imagePreview" :src="imagePreview" alt="Preview"
                                                            class="w-full h-full object-cover" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-bold text-gray-800 truncate"
                                                            :title="form.image ? form.image.name : props.call.image?.name">
                                                            {{ form.image ? form.image.name : props.call.image?.name }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 mt-0.5">
                                                            {{ formatSize(form.image ? form.image.size :
                                                                props.call.image?.size) }}
                                                        </p>
                                                        <div class="mt-1.5 flex items-center gap-3 flex-wrap">
                                                            <label
                                                                class="cursor-pointer inline-flex items-center gap-1 text-[11px] font-bold text-[#15396F] hover:underline">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12">
                                                                    </path>
                                                                </svg>
                                                                Cambiar imagen
                                                                <input type="file" @change="onChangeImage"
                                                                    class="hidden" accept="image/*" />
                                                            </label>
                                                            <div
                                                                class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full text-[10px] font-bold">
                                                                <svg class="w-3 h-3" fill="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path
                                                                        d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                                                </svg>
                                                                {{ form.image ? 'Nueva imagen' : 'Imagen actual' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" @click="removeImage"
                                                        class="w-8 h-8 rounded-full bg-red-50 hover:bg-red-100 flex items-center justify-center text-red-500 transition-colors flex-shrink-0">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                            <path
                                                                d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <!-- 🔥 AQUÍ ESTÁ EL MENSAJE DE ERROR 🔥 -->
                                                <p v-if="form.errors.image" class="text-xs text-red-500 mt-3 font-bold">
                                                    Error: {{ form.errors.image }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-4 bg-blue-50 text-blue-800 px-4 py-2.5 rounded-lg flex items-center gap-2 text-xs font-medium">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                                        </svg>
                                        Puedes subir archivos en formato PDF o imágenes (JPG, PNG).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ETAPAS Y CALENDARIO -->
                        <div class="max-w-3xl mx-auto border border-gray-200 rounded-xl overflow-hidden shadow-sm">

                            <!-- 🔥 LEYENDA DE COLORES (AHORA ARRIBA) 🔥 -->
                            <div class="bg-gray-50 border-b border-gray-200 p-3 flex flex-wrap justify-center gap-4">
                                <div v-for="step in steps" :key="'legend-' + step.id" @click="currentStep = step.id"
                                    class="flex items-center gap-1.5 opacity-80 hover:opacity-100 transition-opacity cursor-pointer bg-white px-3 py-1 rounded-full border border-gray-200 shadow-sm"
                                    :class="{ 'ring-2 ring-[#15396F] opacity-100': currentStep === step.id }">
                                    <span class="w-3.5 h-3.5 rounded-full border border-black/10"
                                        :class="step.color"></span>
                                    <span class="text-xs font-semibold text-gray-600">{{ step.name }}</span>
                                </div>
                            </div>

                            <!-- Días de la semana -->
                            <div class="grid grid-cols-7 text-center bg-blue-100/50">
                                <div v-for="day in daysOfWeek" :key="day"
                                    class="py-2 text-xs font-bold text-[#15396F] uppercase border-b border-blue-100">{{
                                    day }}</div>
                            </div>

                            <!-- Días del mes -->
                            <div class="grid grid-cols-7 text-center bg-white pb-2 pt-2">
                                <div v-for="(blank, index) in blankDays" :key="'blank-' + index"
                                    class="p-2 border-b border-r border-gray-100"></div>
                                <div v-for="day in daysInMonth" :key="day" @click="selectDate(day)"
                                    class="m-1 text-sm flex items-center justify-center cursor-pointer transition-colors h-9"
                                    :class="getDayClass(day)">{{ day }}</div>
                            </div>

                            <!-- Instrucción inferior -->
                            <div
                                class="bg-blue-50 py-2 text-center text-xs text-[#15396F] font-medium flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
                                </svg>
                                Selecciona las fechas en el calendario según la etapa activa
                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex justify-between items-center mt-8 relative">
                    <div class="flex gap-2">
                        <Link :href="route(`${routeName}index`)"
                            class="px-6 py-2.5 text-sm font-bold text-[#15396F] bg-white border border-gray-300 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 shadow-sm z-10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Cancelar
                        </Link>
                        <button type="button" @click="deleteForm"
                            class="px-6 py-2.5 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-full transition-colors flex items-center gap-2 shadow-sm z-10">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path :d="mdiTrashCan" />
                            </svg>
                            Eliminar
                        </button>
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="px-8 py-2.5 text-sm font-bold text-white bg-[#15396F] hover:bg-[#0f2853] rounded-full transition-colors flex items-center gap-2 shadow-md disabled:opacity-50 z-10">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z" />
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </LayoutMain>
    </div>
</template>
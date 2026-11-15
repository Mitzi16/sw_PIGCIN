<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { 
    mdiFileDocumentOutline, 
    mdiClose, 
    mdiMagnify, 
    mdiLinkVariant, 
    mdiInformationOutline,
    mdiFilePdfBox,
    mdiImageOutline,
    mdiTrashCan,
    mdiCheckCircle,
    mdiUpload
} from "@mdi/js";
import BaseIcon from "@/Components/BaseIcon.vue";
import InputError from "@/Components/InputError.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";

const props = defineProps({
    title: { type: String, required: true },
    routeName: { type: String, required: true },
    call: { type: Object, default: null },
    institutions: { type: Array, default: () => [] },
});

const isEdit = computed(() => !!props.call);

// Referencias para los inputs de archivos ocultos
const fileInput = ref(null);
const imageInput = ref(null);

const form = useForm({
    _method: isEdit.value ? 'patch' : 'post',
    title: props.call?.title ?? '',
    description: props.call?.description ?? '',
    institution_id: props.call?.institution_id ?? '',
    status: props.call?.status ?? true,
    url: props.call?.url ?? '',
    file: null,
    image: null,
    delete_file: false, // Para avisar al backend si se quiere borrar el archivo
    delete_image: false,
});

const submit = () => {
    if (isEdit.value) {
        form.post(route(`${props.routeName}update`, props.call.id));
    } else {
        form.post(route(`${props.routeName}store`));
    }
};

// Funciones para los archivos
const triggerFileInput = () => fileInput.value.click();
const triggerImageInput = () => imageInput.value.click();

const handleFile = (e) => {
    form.file = e.target.files[0];
    form.delete_file = false; // Si sube uno nuevo, cancelamos el borrado
};

const handleImage = (e) => {
    form.image = e.target.files[0];
    form.delete_image = false;
};

const removeFile = () => {
    form.file = null;
    form.delete_file = true;
    if (fileInput.value) fileInput.value.value = '';
};

const removeImage = () => {
    form.image = null;
    form.delete_image = true;
    if (imageInput.value) imageInput.value.value = '';
};
</script>

<template>
    <LayoutMain>
        <div class="bg-[#f3f4f6] min-h-screen py-10 flex justify-center">
            <div class="w-full max-w-4xl">
                
                <form @submit.prevent="submit" class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    
                    <!-- HEADER PRINCIPAL AZUL -->
                    <div class="bg-[#15396F] px-6 py-4 flex items-center justify-between text-white">
                        <div class="flex items-center gap-3">
                            <BaseIcon :path="mdiFileDocumentOutline" size="24" class="text-white" />
                            <h1 class="text-lg font-bold tracking-wide">{{ isEdit ? 'Editar convocatoria' : 'Datos y Calendario de la Convocatoria' }}</h1>
                        </div>
                        <Link :href="route(`${routeName}index`)" class="text-white hover:text-red-400 transition-colors">
                            <BaseIcon :path="mdiClose" size="24" />
                        </Link>
                    </div>

                    <!-- CUERPO -->
                    <div class="p-8 md:p-10">
                        
                        <!-- SECCIÓN 1: DATOS -->
                        <div class="mb-12">
                            <div class="flex items-center gap-3 mb-8">
                                <BaseIcon :path="mdiFileDocumentOutline" size="24" class="text-[#15396F]" />
                                <h2 class="text-xl font-bold text-[#15396F]">Datos de la convocatoria</h2>
                                <div class="flex-1 h-[1px] bg-gray-200 ml-4"></div>
                            </div>

                            <div class="space-y-6">
                                <!-- Título -->
                                <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 pt-2 md:pt-0">
                                        <span class="text-red-500">*</span> Título:
                                    </label>
                                    <div class="w-full md:w-3/4">
                                        <input v-model="form.title" type="text" placeholder="Nombre de la convocatoria" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-[#f8fafc] outline-none transition-all placeholder-gray-400" required>
                                        <InputError :message="form.errors.title" class="mt-1" />
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div class="flex flex-col md:flex-row items-start gap-4">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 pt-2">
                                        <span class="text-red-500">*</span> Descripción:
                                    </label>
                                    <div class="w-full md:w-3/4">
                                        <textarea v-model="form.description" rows="4" placeholder="Descripción de la convocatoria..." class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-[#f8fafc] outline-none transition-all resize-none placeholder-gray-400" required></textarea>
                                        <InputError :message="form.errors.description" class="mt-1" />
                                    </div>
                                </div>

                                <!-- Institución -->
                                <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 pt-2 md:pt-0">
                                        <span class="text-red-500">*</span> Institución:
                                    </label>
                                    <div class="w-full md:w-3/4 relative">
                                        <select v-model="form.institution_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-[#f8fafc] outline-none transition-all appearance-none" required>
                                            <option value="" disabled>Buscar institución...</option>
                                            <option v-for="inst in institutions" :key="inst.id" :value="inst.id">{{ inst.name }}</option>
                                        </select>
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                            <BaseIcon :path="mdiMagnify" size="20" />
                                        </div>
                                        <InputError :message="form.errors.institution_id" class="mt-1" />
                                    </div>
                                </div>

                                <!-- Estatus -->
                                <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800">
                                        <span class="text-red-500">*</span> Estatus:
                                    </label>
                                    <div class="w-full md:w-3/4 flex items-center gap-3">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" v-model="form.status" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#15396F]"></div>
                                        </label>
                                        <span class="text-sm font-bold text-gray-800">{{ form.status ? 'Activa' : 'Inactiva' }}</span>
                                    </div>
                                </div>

                                <!-- URL -->
                                <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 pt-2 md:pt-0">
                                        URL (opcional):
                                    </label>
                                    <div class="w-full md:w-3/4 relative">
                                        <input v-model="form.url" type="url" placeholder="Ej. https://youtube.com/..." class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-[#f8fafc] outline-none transition-all pr-12 placeholder-gray-400">
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">
                                            <BaseIcon :path="mdiLinkVariant" size="20" />
                                        </div>
                                        <InputError :message="form.errors.url" class="mt-1" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 2: ARCHIVOS -->
                        <div>
                            <div class="flex items-center gap-3 mb-8">
                                <BaseIcon :path="mdiInformationOutline" size="24" class="text-[#15396F]" />
                                <h2 class="text-xl font-bold text-[#15396F]">Archivos de la convocatoria</h2>
                                <div class="flex-1 h-[1px] bg-gray-200 ml-4"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                <!-- Archivo PDF -->
                                <div class="flex flex-col rounded-lg overflow-hidden border border-gray-200 shadow-sm">
                                    <div class="bg-[#15396F] px-4 py-3 flex items-center gap-2 text-white relative">
                                        <BaseIcon :path="mdiFilePdfBox" size="20" class="z-10" />
                                        <span class="font-bold text-sm z-10">Archivo (PDF) <span class="text-red-400">*</span></span>
                                        <div class="absolute right-0 top-0 bottom-0 w-1/2 z-0" style="background-image: repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(255, 255, 255, 0.15) 10px, rgba(255, 255, 255, 0.15) 20px);"></div>
                                    </div>
                                    <div class="p-4 bg-white flex-1 flex flex-col justify-center">
                                        
                                        <!-- Input oculto -->
                                        <input type="file" @change="handleFile" accept=".pdf" ref="fileInput" class="hidden">
                                        
                                        <!-- Si hay archivo actual o se seleccionó uno nuevo -->
                                        <div v-if="(props.call?.file && !form.delete_file) || form.file" class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                <!-- Ícono PDF -->
                                                <div class="w-10 h-10 bg-red-100 rounded flex items-center justify-center flex-shrink-0 text-red-500">
                                                    <BaseIcon :path="mdiFilePdfBox" size="24" />
                                                </div>
                                                <div class="truncate">
                                                    <p class="text-sm font-bold text-gray-800 truncate">
                                                        {{ form.file ? form.file.name : props.call?.file?.name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ form.file ? (form.file.size / 1024).toFixed(0) + ' KB' : (props.call?.file?.size / 1024).toFixed(0) + ' KB' }}
                                                    </p>
                                                    <div class="flex items-center gap-3 mt-1">
                                                        <button type="button" @click="triggerFileInput" class="text-xs text-[#15396F] hover:underline flex items-center gap-1 font-semibold">
                                                            <BaseIcon :path="mdiUpload" size="14" /> Cambiar archivo
                                                        </button>
                                                        <span class="text-xs text-green-600 flex items-center gap-1 font-semibold">
                                                            <BaseIcon :path="mdiCheckCircle" size="14" /> PDF actual
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Botón eliminar -->
                                            <button type="button" @click="removeFile" class="w-8 h-8 rounded-full bg-red-100 text-red-500 hover:bg-red-200 flex items-center justify-center flex-shrink-0 transition-colors">
                                                <BaseIcon :path="mdiTrashCan" size="18" />
                                            </button>
                                        </div>

                                        <!-- Si no hay archivo -->
                                        <div v-else class="flex flex-col items-center justify-center py-4 cursor-pointer hover:bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 transition-colors" @click="triggerFileInput">
                                            <BaseIcon :path="mdiUpload" size="32" class="text-gray-400 mb-2" />
                                            <span class="text-sm font-semibold text-[#15396F]">Subir archivo PDF</span>
                                            <span class="text-xs text-gray-400 mt-1">Haz clic aquí para seleccionar</span>
                                        </div>

                                        <InputError :message="form.errors.file" class="mt-2" />
                                    </div>
                                </div>

                                <!-- Archivo Imagen -->
                                <div class="flex flex-col rounded-lg overflow-hidden border border-gray-200 shadow-sm">
                                    <div class="bg-[#15396F] px-4 py-3 flex items-center gap-2 text-white relative">
                                        <BaseIcon :path="mdiImageOutline" size="20" class="z-10" />
                                        <span class="font-bold text-sm z-10">Imagen (opcional)</span>
                                        <div class="absolute right-0 top-0 bottom-0 w-1/2 z-0" style="background-image: repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(255, 255, 255, 0.15) 10px, rgba(255, 255, 255, 0.15) 20px);"></div>
                                    </div>
                                    <div class="p-4 bg-white flex-1 flex flex-col justify-center">
                                        
                                        <!-- Input oculto -->
                                        <input type="file" @change="handleImage" accept="image/*" ref="imageInput" class="hidden">
                                        
                                        <!-- Si hay imagen actual o se seleccionó una nueva -->
                                        <div v-if="(props.call?.image && !form.delete_image) || form.image" class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                <!-- Preview Imagen -->
                                                <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center flex-shrink-0 overflow-hidden">
                                                    <img v-if="form.image" :src="URL.createObjectURL(form.image)" class="w-full h-full object-cover" />
                                                    <img v-else-if="props.call?.image" :src="props.call.image.path" class="w-full h-full object-cover" />
                                                </div>
                                                <div class="truncate">
                                                    <p class="text-sm font-bold text-gray-800 truncate">
                                                        {{ form.image ? form.image.name : props.call?.image?.name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ form.image ? (form.image.size / 1024).toFixed(0) + ' KB' : (props.call?.image?.size / 1024).toFixed(0) + ' KB' }}
                                                    </p>
                                                    <div class="flex items-center gap-3 mt-1">
                                                        <button type="button" @click="triggerImageInput" class="text-xs text-[#15396F] hover:underline flex items-center gap-1 font-semibold">
                                                            <BaseIcon :path="mdiUpload" size="14" /> Cambiar imagen
                                                        </button>
                                                        <span class="text-xs text-green-600 flex items-center gap-1 font-semibold">
                                                            <BaseIcon :path="mdiCheckCircle" size="14" /> Imagen actual
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Botón eliminar -->
                                            <button type="button" @click="removeImage" class="w-8 h-8 rounded-full bg-red-100 text-red-500 hover:bg-red-200 flex items-center justify-center flex-shrink-0 transition-colors">
                                                <BaseIcon :path="mdiTrashCan" size="18" />
                                            </button>
                                        </div>

                                        <!-- Si no hay imagen -->
                                        <div v-else class="flex flex-col items-center justify-center py-4 cursor-pointer hover:bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 transition-colors" @click="triggerImageInput">
                                            <BaseIcon :path="mdiImageOutline" size="32" class="text-gray-400 mb-2" />
                                            <span class="text-sm font-semibold text-[#15396F]">Subir imagen</span>
                                            <span class="text-xs text-gray-400 mt-1">Haz clic aquí para seleccionar</span>
                                        </div>

                                        <InputError :message="form.errors.image" class="mt-2" />
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- FOOTER (Botones) -->
                    <div class="bg-[#f8fafc] px-8 py-5 flex justify-end gap-4 border-t border-gray-200">
                        <Link :href="route(`${routeName}index`)" class="px-6 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-bold text-white bg-[#15396F] hover:bg-[#0f2853] rounded-lg transition-colors shadow-sm disabled:opacity-50">
                            {{ isEdit ? 'Actualizar Convocatoria' : 'Guardar Convocatoria' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </LayoutMain>
</template>
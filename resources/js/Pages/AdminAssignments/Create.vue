<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { mdiAccountPlus, mdiClose, mdiContentSave, mdiCheckCircle } from "@mdi/js";
import BaseIcon from "@/Components/BaseIcon.vue";
import InputError from "@/Components/InputError.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import axios from "axios";

const props = defineProps({
    admins: { type: Array, required: true },
});

const form = useForm({
    user_id: '',
    call_ids: [],
});

const availableCalls = ref([]);
const selectedAdmin = ref(null);
const isLoadingCalls = ref(false);

// Observamos cuando se selecciona un Administrador
watch(() => form.user_id, async (newUserId) => {
    form.call_ids = []; // Limpiamos selecciones anteriores
    availableCalls.value = [];
    
    // Buscamos el administrador seleccionado para mostrar su institución
    selectedAdmin.value = props.admins.find(admin => admin.id === newUserId);

    if (newUserId && selectedAdmin.value?.institution_id) {
        isLoadingCalls.value = true;
        try {
            // Llamamos a nuestra API para traer las convocatorias de SU institución
            const response = await axios.get(route('api.admin.assignments.calls', newUserId));
            availableCalls.value = response.data;
        } catch (error) {
            console.error("Error al cargar convocatorias:", error);
        } finally {
            isLoadingCalls.value = false;
        }
    }
});

const toggleCall = (callId) => {
    const index = form.call_ids.indexOf(callId);
    if (index === -1) form.call_ids.push(callId);
    else form.call_ids.splice(index, 1);
};

const submit = () => {
    form.post(route('admin.assignments.store'));
};
</script>

<template>
    <LayoutMain>
        <div class="bg-[#f3f4f6] min-h-screen py-10 flex justify-center">
            <div class="w-full max-w-5xl">
                
                <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    
                    <!-- Header -->
                    <div class="px-8 pt-8 pb-4 flex items-center justify-between border-b border-gray-100">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-[#15396F]">
                                <BaseIcon :path="mdiAccountPlus" size="28" />
                            </div>
                            <h1 class="text-2xl font-bold text-gray-800">Asignar Convocatorias a Administrador</h1>
                        </div>
                        <Link :href="route('admin.assignments.index')" class="text-gray-400 hover:text-red-500 transition-colors">
                            <BaseIcon :path="mdiClose" size="24" />
                        </Link>
                    </div>

                    <!-- Body -->
                    <div class="px-8 py-8 grid grid-cols-1 md:grid-cols-2 gap-10">
                        
                        <!-- Columna Izquierda: Seleccionar Usuario -->
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 border-b border-gray-200 pb-2 mb-6">1. Seleccionar Administrador</h2>
                            
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-semibold text-gray-700">Administrador Registrado</label>
                                <select v-model="form.user_id" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] outline-none text-gray-700 bg-gray-50" required>
                                    <option value="" disabled>Selecciona un administrador</option>
                                    <option v-for="admin in admins" :key="admin.id" :value="admin.id">
                                        {{ admin.name }} ({{ admin.email }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.user_id" />
                            </div>
                        </div>

                        <!-- Columna Derecha: Asignaciones -->
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 border-b border-gray-200 pb-2 mb-6">2. Asignaciones</h2>
                            
                            <div class="flex flex-col gap-6">
                                <!-- Institución (Solo lectura) -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-semibold text-gray-700">Institución del Administrador</label>
                                    <input 
                                        type="text" 
                                        :value="selectedAdmin?.institution?.name ?? 'Ninguna institución asignada'" 
                                        disabled 
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed"
                                    >
                                </div>

                                <!-- Convocatorias -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-bold text-gray-900">Convocatorias disponibles</label>
                                    
                                    <div v-if="isLoadingCalls" class="text-sm text-blue-500 italic">Cargando convocatorias...</div>

                                    <p v-else-if="!form.user_id" class="text-sm text-gray-500 italic mt-1">
                                        Selecciona un administrador para ver sus convocatorias.
                                    </p>

                                    <p v-else-if="availableCalls.length === 0" class="text-sm text-red-500 italic mt-1">
                                        No hay convocatorias registradas para la institución de este administrador.
                                    </p>

                                    <!-- Lista de convocatorias -->
                                    <div v-else class="max-h-56 overflow-y-auto border border-gray-200 rounded-lg p-2 bg-gray-50">
                                        <label v-for="call in availableCalls" :key="call.id" class="flex items-center gap-3 p-3 hover:bg-white rounded-lg transition-colors cursor-pointer border-b border-gray-100 last:border-0">
                                            <input 
                                                type="checkbox" 
                                                :value="call.id" 
                                                :checked="form.call_ids.includes(call.id)"
                                                @change="toggleCall(call.id)"
                                                class="w-5 h-5 text-[#15396F] rounded focus:ring-[#15396F]"
                                            >
                                            <span class="text-sm text-gray-700 font-medium">{{ call.title }}</span>
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.call_ids" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-8 py-5 bg-gray-50 flex justify-end gap-4 border-t border-gray-200">
                        <Link :href="route('admin.assignments.index')" class="px-6 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing || form.call_ids.length === 0" class="flex items-center gap-2 px-8 py-2.5 text-sm font-bold text-white bg-[#10b981] hover:bg-[#059669] rounded-lg transition-colors shadow-sm disabled:opacity-50">
                            <BaseIcon :path="mdiContentSave" size="18" />
                            Guardar Asignación
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </LayoutMain>
</template>
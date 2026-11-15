<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import { mdiAccountEdit, mdiClose, mdiContentSave } from "@mdi/js";
import BaseIcon from "@/Components/BaseIcon.vue";
import InputError from "@/Components/InputError.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";

const props = defineProps({
    admin: { type: Object, required: true },
    availableCalls: { type: Array, required: true }, // Enviadas desde el controlador
    assignedCalls: { type: Array, required: true }, // IDs de las convocatorias ya asignadas
});

const form = useForm({
    user_id: props.admin.id,
    call_ids: [...props.assignedCalls], // Clonamos el arreglo para que sea reactivo
});

const toggleCall = (callId) => {
    const index = form.call_ids.indexOf(callId);
    if (index === -1) form.call_ids.push(callId);
    else form.call_ids.splice(index, 1);
};

const submit = () => {
    form.put(route('admin.assignments.update', props.admin.id));
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
                            <div class="w-12 h-12 bg-orange-50 rounded-full flex items-center justify-center text-orange-500">
                                <BaseIcon :path="mdiAccountEdit" size="28" />
                            </div>
                            <h1 class="text-2xl font-bold text-gray-800">Editar Asignaciones de Administrador</h1>
                        </div>
                        <Link :href="route('admin.assignments.index')" class="text-gray-400 hover:text-red-500 transition-colors">
                            <BaseIcon :path="mdiClose" size="24" />
                        </Link>
                    </div>

                    <!-- Body -->
                    <div class="px-8 py-8 grid grid-cols-1 md:grid-cols-2 gap-10">
                        
                        <!-- Columna Izquierda: Administrador (Solo lectura) -->
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 border-b border-gray-200 pb-2 mb-6">1. Administrador</h2>
                            
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-semibold text-gray-700">Administrador</label>
                                <!-- Campo bloqueado, solo visual -->
                                <div class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-100 text-gray-600 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-200 flex items-center justify-center text-blue-700 font-bold text-xs">
                                        {{ admin.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm">{{ admin.name }}</p>
                                        <p class="text-xs">{{ admin.email }}</p>
                                    </div>
                                </div>
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
                                        :value="admin.institution?.name ?? 'Ninguna institución asignada'" 
                                        disabled 
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed"
                                    >
                                </div>

                                <!-- Convocatorias -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-bold text-gray-900">Convocatorias de la Institución</label>
                                    
                                    <p v-if="availableCalls.length === 0" class="text-sm text-red-500 italic mt-1">
                                        No hay convocatorias registradas para la institución de este administrador.
                                    </p>

                                    <!-- Lista de convocatorias -->
                                    <div v-else class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-2 bg-gray-50">
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
                        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 px-8 py-2.5 text-sm font-bold text-white bg-[#15396F] hover:bg-[#0f2853] rounded-lg transition-colors shadow-sm disabled:opacity-50">
                            <BaseIcon :path="mdiContentSave" size="18" />
                            Actualizar Asignaciones
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </LayoutMain>
</template>
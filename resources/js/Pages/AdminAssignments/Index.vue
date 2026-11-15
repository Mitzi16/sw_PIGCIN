<script setup>
import { Link, Head } from "@inertiajs/vue3";
import { mdiAccountCog, mdiPencil, mdiAccountPlus, mdiFileDocumentMultipleOutline } from "@mdi/js";
import BaseIcon from "@/Components/BaseIcon.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import CardBox from "@/Components/CardBox.vue";

const props = defineProps({
    admins: { type: Array, required: true },
});
</script>

<template>
    <Head title="Administradores y Convocatorias" />
    <LayoutMain>
        <div class="bg-[#f3f4f6] min-h-screen py-8">
            <div class="max-w-6xl mx-auto">
                
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-[#15396F]">
                            <BaseIcon :path="mdiAccountCog" size="28" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Administradores</h1>
                            <p class="text-sm text-gray-500">Gestiona las convocatorias asignadas a cada administrador</p>
                        </div>
                    </div>
                    
                    <Link :href="route('admin.assignments.create')" class="flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-[#15396F] hover:bg-[#0f2853] rounded-lg transition-colors shadow-sm">
                        <BaseIcon :path="mdiAccountPlus" size="20" />
                        Asignar Convocatoria
                    </Link>
                </div>

                <!-- Tabla -->
                <CardBox class="overflow-hidden border border-gray-200 shadow-sm rounded-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-white bg-[#15396F] uppercase">
                                <tr>
                                    <th class="px-6 py-4">Administrador</th>
                                    <th class="px-6 py-4">Institución</th>
                                    <th class="px-6 py-4">Convocatorias Asignadas</th>
                                    <th class="px-6 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="admin in admins" :key="admin.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                                {{ admin.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800">{{ admin.name }}</p>
                                                <p class="text-xs text-gray-500">{{ admin.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-medium text-gray-700">
                                            {{ admin.institution?.name ?? 'Sin institución' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="admin.calls && admin.calls.length > 0" class="flex flex-wrap gap-1 max-w-xs">
                                            <span v-for="call in admin.calls" :key="call.id" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                <BaseIcon :path="mdiFileDocumentMultipleOutline" size="14" />
                                                {{ call.title }}
                                            </span>
                                        </div>
                                        <span v-else class="text-xs text-gray-400 italic">Ninguna asignada</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <Link 
                                            :href="route('admin.assignments.edit', admin.id)" 
                                            class="inline-flex items-center justify-center w-9 h-9 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                                            title="Editar asignaciones"
                                        >
                                            <BaseIcon :path="mdiPencil" size="18" />
                                        </Link>
                                    </td>
                                </tr>
                                
                                <!-- Si no hay administradores -->
                                <tr v-if="admins.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">
                                        No hay administradores registrados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardBox>

            </div>
        </div>
    </LayoutMain>
</template>
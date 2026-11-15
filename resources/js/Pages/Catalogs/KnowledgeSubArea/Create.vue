<script setup>
import LayoutMain from "@/Layouts/LayoutMain.vue";
import { Link, Head, useForm } from "@inertiajs/vue3";

const props = defineProps({
    name: { type: 'Create' },
    title: { type: String, required: true },
    routeName: { type: String, required: true },
});

const form = useForm({ name: null, description: null });

const saveForm = () => {
    form.post(route(`${props.routeName}store`));
};
</script>

<template>
    <div class="bg-slate-50 min-h-screen relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-blue-50 to-transparent -z-10"></div>

        <Head :title="title" />
        <LayoutMain>
            <form @submit.prevent="saveForm" class="max-w-5xl mx-auto pt-6 pb-24 relative z-10">
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                    <!-- Header Principal -->
                    <div class="bg-[#15396F] px-6 py-3 flex items-center justify-between text-white">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                            </svg>
                            <h2 class="text-sm font-bold">Datos de la Convocatoria</h2>
                        </div>
                        <Link :href="route(`${routeName}index`)" class="text-white hover:text-red-400 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </Link>
                    </div>

                    <div class="p-6 space-y-8">
                        <!-- SECCIÓN -->
                        <div>
                            <h3 class="text-base font-bold text-[#15396F] mb-4 flex items-center gap-2 border-b border-gray-200 pb-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                                </svg>
                                Datos del Programa
                            </h3>

                            <div class="space-y-4">
                                <!-- Nombre -->
                                <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start md:items-center">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2"><span class="text-red-500">*</span> Nombre del Programa:</label>
                                    <div class="w-full md:w-3/4">
                                        <input v-model="form.name" type="text" placeholder="Nombre del programa" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50" required>
                                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2"><span class="text-red-500">*</span> Descripción:</label>
                                    <div class="w-full md:w-3/4">
                                        <textarea v-model="form.description" rows="3" placeholder="Descripción del programa..." class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50 resize-none" required></textarea>
                                        <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                                    </div>
                                </div>
                            </div>
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
                        Guardar
                    </button>
                </div>
            </form>
        </LayoutMain>
    </div>
</template>
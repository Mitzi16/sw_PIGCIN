<script setup>
import BaseIcon from '@/Components/BaseIcon.vue';
import { Link } from '@inertiajs/vue3'; // 👈 No olvides importar Link
import { 
    mdiCalendarOutline, 
    mdiFileDocumentOutline, 
    mdiAccountPlus // 👈 Nuevo icono para registro
} from '@mdi/js';

const props = defineProps({
    call: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['openModal', 'openPdf']);
</script>

<template>
    <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-gray-100 overflow-hidden flex flex-col w-full transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group">

        <!-- Imagen de la convocatoria -->
        <div class="relative overflow-hidden h-48 w-full bg-gray-100 cursor-pointer" @click="emit('openModal', call)">
            <img :src="call.image != null ? '/storage/' + call.image.path : '/img/login-image.jpg'"
                alt="imagen convocatoria"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">

            <!-- Etiqueta superpuesta -->
            <div class="absolute top-4 left-4 bg-[#1B396A] text-white text-[10px] font-bold px-3 py-1.5 rounded-md shadow-md uppercase tracking-wide border border-white/20">
                {{ call.type || 'CONVOCATORIA' }}
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-5 flex flex-col flex-grow relative z-10 bg-white">

            <!-- Fecha -->
            <div class="flex items-center gap-2 text-xs text-gray-500 font-medium mb-3">
                <BaseIcon :path="mdiCalendarOutline" size="16" class="text-[#1B396A]" />
                {{ call.start_date }}
            </div>

            <!-- Título -->
            <h4 class="text-lg font-bold text-[#1B396A] mb-2 leading-tight line-clamp-2">
                {{ call.title }}
            </h4>

            <!-- Descripción -->
            <div class="line-clamp-3 mb-6 flex-grow">
                <p class="text-sm text-gray-500 leading-relaxed">
                    {{ call.description }}
                </p>
            </div>

            <!-- Footer de Botones Mejorado -->
            <div class="flex flex-col gap-3 mt-auto pt-4 border-t border-gray-100">
                
                <!-- Botón Ver convocatoria (Secundario) -->
                <button @click="emit('openModal', call)"
                    class="w-full border-2 border-[#1B396A] text-[#1B396A] hover:bg-[#f0f4f8] transition-colors rounded-lg py-2 px-4 flex items-center justify-center gap-2 text-sm font-bold">
                    <BaseIcon :path="mdiFileDocumentOutline" size="18" />
                    Ver convocatoria
                </button>

                <!-- Botón Registrarse (Principal) -->
                <Link :href="route('register', { call_id: call.id })"
                    class="w-full bg-green-600 hover:bg-green-700 text-white transition-all shadow-md hover:shadow-lg rounded-lg py-2.5 px-4 flex items-center justify-center gap-2 text-sm font-bold">
                    <BaseIcon :path="mdiAccountPlus" size="18" />
                    Registrarme
                </Link>
                
            </div>
        </div>

        <!-- Decoración geométrica -->
        <div class="absolute bottom-0 left-0 w-24 h-8 pointer-events-none z-0">
            <div class="absolute bottom-0 left-0 w-full h-full bg-[#1B396A]"
                style="clip-path: polygon(0 100%, 0 0, 100% 100%); opacity: 0.1;"></div>
            <div class="absolute bottom-0 left-0 w-16 h-6 bg-[#1B396A]"
                style="clip-path: polygon(0 100%, 0 0, 100% 100%); opacity: 0.2;"></div>
            <div class="absolute bottom-0 left-0 w-8 h-4 bg-[#1B396A]"
                style="clip-path: polygon(0 100%, 0 0, 100% 100%);"></div>
        </div>
    </div>
</template>
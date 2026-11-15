<script setup>
import HeadLogo from '@/Components/HeadLogo.vue';
import LayoutWelcome from '@/Layouts/LayoutWelcome.vue';
import { ref } from 'vue';
import { eventData } from "@/Components/CitcaInfo/eventData.js";
import TabsNavigation from "@/Components/CitcaInfo/TabsNavigation.vue"; 
import DailySchedule from "@/Components/CitcaInfo/DailySchedule.vue"; 
import WorkshopsList from "@/Components/CitcaInfo/WorkshopsList.vue";
import ConferencesList from "@/Components/CitcaInfo/ConferencesList.vue"; 

// Importamos un icono para el banner
import { mdiCalendarStar } from "@mdi/js";
import BaseIcon from "@/Components/BaseIcon.vue";

const props = defineProps({
    title: {
        type: String,
        required: true
    }
});

// Estado actual
const currentTab = ref('programa'); 

// Función para manejar el cambio que viene del hijo
const handleTabChange = (newTab) => {
  currentTab.value = newTab;
};
</script>

<template>
  <HeadLogo :title="title" />

  <LayoutWelcome>
    <!-- Sección principal con fondo blanco y decoraciones -->
    <main class="relative flex-grow bg-white overflow-hidden py-12 min-h-screen">
        
        <!-- Decoraciones geométricas de fondo (igual que en las otras secciones) -->
        <div class="absolute top-0 left-0 w-48 h-24 bg-[#1B396A] hidden md:block opacity-10" style="clip-path: polygon(0 0, 100% 0, 70% 100%, 0% 100%);"></div>
        <div class="absolute bottom-0 right-0 w-80 h-40 bg-[#1B396A] hidden md:block opacity-10" style="clip-path: polygon(40% 0, 100% 0, 100% 100%, 0% 100%);"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
            
            <!-- 1. BANNER SUPERIOR AZUL (Rediseñado) -->
            <div class="relative bg-[#1B396A] rounded-2xl overflow-hidden shadow-lg mb-8 flex flex-col md:flex-row justify-between items-center p-6 md:p-10">
                <!-- Patrón de fondo del banner -->
                <div class="absolute inset-0 opacity-10 pointer-events-none">
                    <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 100">
                        <circle cx="90" cy="50" r="40" fill="white" />
                        <circle cx="80" cy="20" r="20" fill="white" />
                    </svg>
                </div>

                <div class="relative z-10 flex items-center gap-4">
                    <div class="bg-white/10 p-3 rounded-xl backdrop-blur-sm border border-white/10 hidden sm:block">
                        <BaseIcon :path="mdiCalendarStar" size="32" class="text-white" />
                    </div>
                    <div>
                        <h1 class="text-white text-2xl md:text-4xl font-extrabold tracking-tight mb-2">
                            Consulta más sobre CITCA-2025
                        </h1>
                        <p class="text-blue-100 text-sm md:text-base">
                            Descubre todo lo que tenemos preparado para ti. Explora el programa, conferencias y talleres.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. NAVEGACIÓN DE PESTAÑAS (Estilo flotante) -->
            <div class="relative z-20 -mt-12 mb-8 flex justify-center">
                <!-- Envolvemos el componente TabsNavigation en un contenedor con sombra y bordes redondeados -->
                <div class="bg-white rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-gray-100 p-2 inline-flex max-w-full overflow-x-auto">
                    <!-- Nota: Debes asegurarte de que los botones dentro de TabsNavigation tengan estilos de píldora (rounded-full) -->
                    <TabsNavigation 
                        :active-tab="currentTab" 
                        @change-tab="handleTabChange" 
                    />
                </div>
            </div>

            <!-- 3. CONTENIDO DINÁMICO -->
            <div class="min-h-[400px]">
                <Transition name="fade" mode="out-in">
                    <div :key="currentTab" class="w-full">
                        
                        <DailySchedule 
                            v-if="currentTab === 'programa'" 
                            :schedule="eventData.programa" 
                        />

                        <ConferencesList 
                            v-if="currentTab === 'conferencias'" 
                            :conferences="eventData.conferencias"
                        />

                        <WorkshopsList 
                            v-if="currentTab === 'talleres'" 
                            :workshops="eventData.talleres" 
                        />

                    </div>
                </Transition>
            </div>

        </div>
    </main>
  </LayoutWelcome>
</template>

<style scoped>
/* Transición suave entre pestañas */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(15px); 
}
</style>
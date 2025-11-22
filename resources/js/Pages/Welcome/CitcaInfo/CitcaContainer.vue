<script setup>
import HeadLogo from '@/Components/HeadLogo.vue';
import LayoutWelcome from '@/Layouts/LayoutWelcome.vue';
import { ref } from 'vue';
import { eventData } from "@/Components/CitcaInfo/eventData.js";
import TabsNavigation from "@/Components/CitcaInfo/TabsNavigation.vue"; 
import DailySchedule from "@/Components/CitcaInfo/DailySchedule.vue"; 
import WorkshopsList from "@/Components/CitcaInfo/WorkshopsList.vue";
import ConferencesList from "@/Components/CitcaInfo/ConferencesList.vue"; 

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
    <main class="flex-grow container mx-auto px-4 py-8 max-w-7xl">
        
        <section class="flex flex-col gap-6">
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Consulta más sobre CITCA-2025</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Descubre todo lo que tenemos preparado para ti</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900/50 px-6 pt-4">
                    <TabsNavigation 
                        :active-tab="currentTab" 
                        @change-tab="handleTabChange" 
                    />
                </div>
            </div>

            <div class="min-h-[400px]">
                <Transition name="fade" mode="out-in">
                    <div :key="currentTab">
                        
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

        </section>
    </main>
  </LayoutWelcome>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(10px); 
}
</style>
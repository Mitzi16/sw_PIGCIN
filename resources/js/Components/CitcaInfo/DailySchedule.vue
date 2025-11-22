<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue'; 

// Recibimos el array de programas desde eventData
defineProps({
  schedule: {
    type: Array,
    required: true
  }
});

// --- LÓGICA DEL MODAL ---
const showPdfModal = ref(false);
const currentPdfUrl = ref('');
const currentDayTitle = ref('');

const openPdf = (pdfUrl, title) => {
  currentPdfUrl.value = pdfUrl;
  currentDayTitle.value = title;
  showPdfModal.value = true;
};

const closePdf = () => {
  showPdfModal.value = false;
  // Limpiamos la URL un poco después para evitar parpadeos
  setTimeout(() => {
    currentPdfUrl.value = '';
  }, 200);
};
</script>

<template>
  <div class="py-8 w-full">
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 max-w-7xl mx-auto px-2">
      
      <div 
        v-for="day in schedule" 
        :key="day.id" 
        class="
            group relative flex flex-col h-full
            bg-white dark:bg-gray-800 
            rounded-2xl overflow-hidden 
            shadow-md hover:shadow-2xl dark:shadow-none dark:hover:shadow-gray-900/60
            border border-gray-100 dark:border-gray-700
            cursor-pointer 
            transform transition-all duration-300 ease-out
            hover:-translate-y-2
        "
        @click="openPdf(day.pdf, day.dia + ' - ' + day.fecha)"
      >
        <div class="p-6 flex justify-between items-start text-white transition-colors relative overflow-hidden" :class="day.color">
          <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
          
          <div class="relative z-10">
              <h3 class="text-3xl font-black tracking-tight">{{ day.dia }}</h3>
              <p class="text-blue-50/90 text-sm font-medium mt-1 uppercase tracking-wider">Programa del día</p>
          </div>
          
          <span class="relative z-10 bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1 rounded-lg text-sm font-bold shadow-sm">
            {{ day.fecha }}
          </span>
        </div>
        
        <div class="p-6 flex flex-col flex-grow relative">
          <h4 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-3 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
            {{ day.titulo }}
          </h4>
          
          <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-6 flex-grow line-clamp-4">
            {{ day.descripcion }}
          </p>
          
          <hr class="border-gray-100 dark:border-gray-700 mb-4 group-hover:border-blue-100 dark:group-hover:border-gray-600 transition-colors" />

          <div class="flex items-center justify-between text-blue-600 dark:text-blue-400 font-bold text-sm group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors mt-auto">
            <span>Ver Programa PDF</span>
            
            <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </div>
          </div>
        </div>
      </div>

    </div>

    <Modal :show="showPdfModal" @close="closePdf" maxWidth="4xl">
      <div class="bg-white dark:bg-gray-800 h-[85vh] flex flex-col rounded-lg overflow-hidden shadow-xl">
        
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-between items-center">
          <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
            {{ currentDayTitle }}
          </h3>
          <button @click="closePdf" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-full p-1 hover:bg-gray-200 dark:hover:bg-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>

        <div class="flex-1 bg-gray-200 dark:bg-gray-900/50 w-full h-full overflow-hidden relative">
            <iframe 
              v-if="showPdfModal" 
              :src="currentPdfUrl" 
              class="w-full h-full border-0"
            >
              <div class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400 p-10 text-center">
                <p class="mb-4">Tu navegador no soporta la visualización directa de PDFs.</p>
                <a :href="currentPdfUrl" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">
                  Descárgalo aquí para verlo
                </a>
              </div>
            </iframe>
        </div>

      </div>
    </Modal>

  </div>
</template>
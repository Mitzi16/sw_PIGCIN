<script setup>
// Definimos las propiedades que recibe del padre
defineProps({
  activeTab: {
    type: String,
    required: true
  }
});

// Definimos los eventos que este componente envía al padre
const emit = defineEmits(['changeTab']);

// Función auxiliar para emitir el cambio
const selectTab = (tabName) => {
  emit('changeTab', tabName);
};
</script>

<template>
  <!-- Contenedor principal -->
  <div class="flex flex-wrap border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 px-6 pt-4 transition-colors duration-300">
    
    <!-- Botón: Programa por Días -->
    <button 
      @click="selectTab('programa')" 
      class="mr-2 px-6 py-3 rounded-t-lg font-medium text-sm transition-all duration-200 flex items-center gap-2 outline-none focus:outline-none"
      :class="activeTab === 'programa' 
        ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border-t-2 border-indigo-600 dark:border-indigo-400 shadow-sm' 
        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/50 border-t-2 border-transparent'"
    >
      <!-- Icono Calendar -->
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="16" y1="2" x2="16" y2="6"></line>
        <line x1="8" y1="2" x2="8" y2="6"></line>
        <line x1="3" y1="10" x2="21" y2="10"></line>
      </svg>
      Programa por Días
    </button>
    
    <!-- Botón: Conferencias -->
    <button 
      @click="selectTab('conferencias')" 
      class="mr-2 px-6 py-3 rounded-t-lg font-medium text-sm transition-all duration-200 flex items-center gap-2 outline-none focus:outline-none"
      :class="activeTab === 'conferencias' 
        ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border-t-2 border-indigo-600 dark:border-indigo-400 shadow-sm' 
        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/50 border-t-2 border-transparent'"
    >
      <!-- Icono Mic -->
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
        <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
        <line x1="12" y1="19" x2="12" y2="23"></line>
        <line x1="8" y1="23" x2="16" y2="23"></line>
      </svg>
      Conferencias Magistrales
    </button>
    
    <!-- Botón: Talleres -->
    <button 
      @click="selectTab('talleres')" 
      class="mr-2 px-6 py-3 rounded-t-lg font-medium text-sm transition-all duration-200 flex items-center gap-2 outline-none focus:outline-none"
      :class="activeTab === 'talleres' 
        ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border-t-2 border-indigo-600 dark:border-indigo-400 shadow-sm' 
        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/50 border-t-2 border-transparent'"
    >
      <!-- Icono Wrench (Talleres) -->
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
      </svg>
      Talleres
    </button>

  </div>
</template>
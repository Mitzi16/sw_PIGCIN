<script setup>
import HeadLogo from '@/Components/HeadLogo.vue';
import LayoutWelcome from '@/Layouts/LayoutWelcome.vue'; 
import { onMounted, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

// Props desde el backend
const page = usePage()

const article = computed(() => page.props.article)
const authors = computed(() => page.props.authors || [])
const statuses = computed(() => page.props.articleStatuses || [])
const alert = page.props.alert

// Muestra alerta y redirige si es necesario
onMounted(() => {
  if (alert) {
    Swal.fire({
      title: alert.title,
      text: alert.text,
      icon: alert.icon,
      confirmButtonText: 'Entendido'
    }).then(() => {
      router.visit(alert.redirectTo)
    })
  }
})

// Función para convertir fecha
function dateToLocal(dateStr) {
  return new Date(dateStr).toLocaleDateString('es-MX', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}
const currentStatusName = computed(() => {
  const status = statuses.value.find(s => s.id === article.value.article_status_id)
  return status ? status.name : 'Desconocido'
})

const nextStatusName = computed(() => {
  const currentIndex = statuses.value.findIndex(s => s.id === article.value.article_status_id)
  if (currentIndex < statuses.value.length - 1) {
    return statuses.value[currentIndex + 1].name
  }
  return 'Proceso completado'
})

const progressPercentage = computed(() => {
  const currentIndex = statuses.value.findIndex(s => s.id === article.value.article_status_id)
  return Math.round((currentIndex + 1) / statuses.value.length * 100)
})

const title = computed(() => {
  return article.value.title || 'Título desconocido'
})

const correspondingAuthor = computed(() => {
  if (!authors.value.length) return 'No asignado';
  
  // Busca el autor con is_corresponding = 1
const author = authors.value.find(a => a.is_corresponding === 1);
  return author ? author.name : 'No asignado';
});
</script>

<template>
  <HeadLogo :title="article ? `Artículo: ${article.article_folio}` : 'Buscador de Artículos'" />
  <LayoutWelcome>
    <div v-if="article" class="flex flex-col h-[calc(100vh-150px)] justify-center md:justify-start">
      <!-- Header mejorado -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
          <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Proceso del Artículo</h2>
          <p class="text-gray-600 dark:text-gray-300 mt-1">Folio: {{ article.article_folio }}</p>
        </div>
        <div class="mt-3 md:mt-0">
          <div class="inline-flex items-center px-4 py-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
            <span class="w-3 h-3 rounded-full bg-blue-500 dark:bg-blue-400 mr-2 animate-pulse"></span>
            <span class="font-medium text-blue-800 dark:text-blue-200">Estado actual: {{ currentStatusName }}</span>
          </div>
        </div>
      </div>

     

      <!-- Versión móvil (vertical) -->
      <div class="md:hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5">
          <div v-for="(item, index) in statuses" :key="item.id" class="flex items-start">
            <div class="flex flex-col items-center mr-4">
              <!-- Número de paso -->
              <div class="w-8 h-8 flex items-center justify-center rounded-full mb-1"
                :class="{
                  'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': article.article_status_id >= item.id,
                  'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': article.article_status_id < item.id
                }">
                <span class="font-medium">{{ index + 1 }}</span>
              </div>
              <!-- Línea vertical -->
              <div v-if="index < statuses.length - 1" 
                class="w-0.5 h-10 my-1"
                :class="{
                  'bg-green-200 dark:bg-green-800': article.article_status_id > item.id,
                  'bg-gray-200 dark:bg-gray-700': article.article_status_id <= item.id
                }"></div>
            </div>
            
            <div class="flex-1 pb-6" :class="{ 'border-b border-gray-100 dark:border-gray-700': index < statuses.length - 1 }">
              <div class="flex justify-between items-start">
                <h3 class="font-semibold text-gray-800 dark:text-white">{{ item.name }}</h3>
                <span v-if="article.article_status_id === item.id" class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full">
                  Actual
                </span>
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ item.description }}</p>
              <div v-if="article.article_status_id === item.id" class="mt-2 flex items-center text-xs text-gray-500 dark:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Última actualización: {{ dateToLocal(article.updated_at) }}
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Versión escritorio (horizontal) -->
      <div class="hidden md:block">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
          <div class="relative">
            <!-- Línea de progreso horizontal -->
            <div class="absolute top-8 left-0 right-0 h-1 bg-gray-200 dark:bg-gray-700 z-0"></div>
            
            <div class="relative flex justify-between z-10">
              <div v-for="(item, index) in statuses" :key="item.id" class="flex flex-col items-center" style="min-width: 140px;">
                <!-- Punto de progreso -->
                <div class="w-6 h-6 rounded-full mb-2 flex items-center justify-center"
                  :class="{
                    'bg-gradient-to-br from-blue-500 to-green-500 text-white': article.article_status_id >= item.id,
                    'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400': article.article_status_id < item.id
                  }">
                  <span v-if="article.article_status_id <= item.id">{{ index + 1 }}</span>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
                
                <!-- Tarjeta de estado -->
                <div class="text-center bg-gray-50 dark:bg-gray-700 p-3 rounded-lg w-full"
                  :class="{
                    'ring-2 ring-blue-500 dark:ring-blue-400': article.article_status_id === item.id,
                    'border border-gray-200 dark:border-gray-600': article.article_status_id !== item.id
                  }">
                  <h3 class="font-semibold text-gray-800 dark:text-white mb-1">{{ item.name }}</h3>
                  <p class="text-xs text-gray-600 dark:text-gray-300 mb-1">{{ item.description }}</p>
                  <div v-if="article.article_status_id === item.id" class="text-xs text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ dateToLocal(article.updated_at) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </LayoutWelcome>
</template>
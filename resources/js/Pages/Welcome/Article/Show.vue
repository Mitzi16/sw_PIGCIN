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

const title = computed(() => article.value.title || 'Título desconocido')

const correspondingAuthor = computed(() => {
  if (!authors.value.length) return 'No asignado';
  const author = authors.value.find(a => a.is_corresponding === 1);
  return author ? author.name : 'No asignado';
});
</script>

<template>
  <HeadLogo :title="article ? `Artículo: ${article.article_folio}` : 'Buscador de Artículos'" />
  <LayoutWelcome>
    <div v-if="article" class="flex justify-center">
      <div class="w-full max-w-6xl flex flex-col h-[calc(100vh-150px)] justify-center md:justify-start px-4 mt-4">

        <!-- Simplified header with better spacing -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ article.article_folio }}</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-lg">Seguimiento del proceso</p>
          </div>
          <div class="mt-4 md:mt-0">
            <span
              class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full font-medium">
              {{ currentStatusName }}
            </span>
          </div>
        </div>

        <!-- Simplified mobile version without cards -->
        <div class="md:hidden">
          <div class="space-y-6">
            <div v-for="(item, index) in statuses" :key="item.id" class="flex items-center">
              <div class="flex flex-col items-center mr-6">
                <div class="w-10 h-10 flex items-center justify-center rounded-full font-semibold text-sm"
                  :class="article.article_status_id >= item.id ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500'">
                  <span v-if="article.article_status_id > item.id">✓</span>
                  <span v-else>{{ index + 1 }}</span>
                </div>
                <div v-if="index < statuses.length - 1" class="w-0.5 h-8 mt-2"
                  :class="article.article_status_id > item.id ? 'bg-green-300' : 'bg-gray-200 dark:bg-gray-700'"></div>
              </div>

              <div class="flex-1">
                <h3 class="font-semibold text-gray-900 dark:text-white text-lg"
                  :class="article.article_status_id === item.id ? 'text-blue-600 dark:text-blue-400' : ''">
                  {{ item.name }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ item.description }}</p>
                <div v-if="article.article_status_id === item.id" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                  Actualizado: {{ dateToLocal(article.updated_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Clean horizontal design without heavy cards -->
        <div class="hidden md:block">
          <div class="relative py-8">
            <!-- Progress line -->
            <div class="absolute top-12 left-0 right-0 h-0.5 bg-gray-200 dark:bg-gray-700"></div>
            <div class="absolute top-12 left-0 h-0.5 bg-green-500 transition-all duration-500"
              :style="`width: ${(statuses.findIndex(s => s.id === article.article_status_id) / (statuses.length - 1)) * 100}%`">
            </div>

            <div class="relative flex justify-between">
              <div v-for="(item, index) in statuses" :key="item.id" class="flex flex-col items-center text-center"
                style="max-width: 180px;">
                <!-- Progress dot -->
                <div class="w-8 h-8 rounded-full mb-4 flex items-center justify-center font-semibold relative z-10"
                  :class="article.article_status_id >= item.id ? 'bg-green-500 text-white shadow-lg' : 'bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 text-gray-500'">
                  <span v-if="article.article_status_id > item.id" class="text-sm">✓</span>
                  <span v-else class="text-sm">{{ index + 1 }}</span>
                </div>

                <!-- Status info -->
                <div>
                  <h3 class="font-semibold text-gray-900 dark:text-white mb-2"
                    :class="article.article_status_id === item.id ? 'text-blue-600 dark:text-blue-400' : ''">
                    {{ item.name }}
                  </h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ item.description }}</p>
                  <div v-if="article.article_status_id === item.id"
                    class="mt-3 text-xs text-blue-600 dark:text-blue-400 font-medium">
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

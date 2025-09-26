<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, onBeforeUnmount, computed, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { mdiAccountMultiple, mdiBookEducation, mdiChartTimelineVariant, mdiBullhorn } from "@mdi/js";
import SectionMain from "@/Components/SectionMain.vue";
import CardBoxWidget from "@/Components/CardBoxWidget.vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import HeadLogo from "@/Components/HeadLogo.vue";
import CardBox from "@/Components/CardBox.vue";
import TemplatesGuide from "@/Components/TemplatesGuide.vue";
import { Chart, registerables } from 'chart.js';
import { debounce } from 'lodash';

const props = defineProps({
  data: { type: Object, default: null },
  role: { type: String, required: true },
  programs: { type: Array, required: true },
  areas: { type: Array, required: true },
  articleTypes: { type: Array, required: true },
  filters: { type: Object, default: () => ({}) },
  totalFilteredArticles: { type: Number, default: 0 },
  // NUEVO: Recibimos el conteo de artículos por tipo
  articleCountsByType: { type: Array, default: () => [] }
});

// Filtros reactivos
const selectedType = ref(props.filters.type ?? 'all');
const dateStart = ref(props.filters.date_start ?? '');
const dateEnd = ref(props.filters.date_end ?? '');
const programsType = ref(props.filters.program_id ?? 'all');
const areasType = ref(props.filters.area_id ?? 'all');
const isLoading = ref(false);

// Filtrado de áreas por programa
const filteredAreas = computed(() => {
  if (programsType.value === 'all') return props.areas;
  return props.areas.filter(area => area.program_id == programsType.value);
});

// Chart.js
Chart.register(...registerables);
let myChart = null;

// Saber si hay filtros aplicados
const hasFilters = computed(() => {
  return (
    programsType.value !== 'all' ||
    areasType.value !== 'all' ||
    selectedType.value !== 'all' ||
    dateStart.value !== '' ||
    dateEnd.value !== ''
  );
});

// Configuración dinámica del gráfico
const chartConfig = computed(() => {
  if (!hasFilters.value) {
    return {
      type: 'pie',
      data: {
        labels: props.articleTypes.map(type => type.name),
        datasets: [{
          // MODIFICADO: Mapeamos los tipos de artículo para encontrar su conteo real
          data: props.articleTypes.map(type => {
            const countData = props.articleCountsByType.find(
              item => item.article_type_id === type.id
            );
            // Si un tipo no tiene artículos, devolvemos 0
            return countData ? countData.count : 0;
          }),
          backgroundColor: [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)'
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: { 
          responsive: true, 
          maintainAspectRatio: false,
          plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed !== null) {
                            const total = context.dataset.data.reduce((acc, value) => acc + value, 0);
                            const value = context.parsed;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
                            label += `${value} (${percentage}%)`;
                        }
                        return label;
                    }
                }
            }
        }
      }
    };
  } else {
    return {
      type: 'bar',
      data: {
        labels: ["Artículos filtrados"],
        datasets: [{
          label: 'Cantidad',
          data: [props.totalFilteredArticles],
          backgroundColor: ['rgba(54, 162, 235, 0.7)'],
          borderColor: ['rgba(54, 162, 235, 1)'],
          borderWidth: 1
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    };
  }
});

const initChart = () => {
  const ctx = document.getElementById('articlesChart');
  if (ctx) {
    if (myChart) myChart.destroy();
    myChart = new Chart(ctx, chartConfig.value);
  }
};

// Aplicar filtros
const applyFilters = () => {
  isLoading.value = true;
  router.get(route('dashboard'), {
    program_id: programsType.value,
    area_id: areasType.value,
    type: selectedType.value,
    date_start: dateStart.value,
    date_end: dateEnd.value
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    // MODIFICADO: Asegúrate de que articleCountsByType se actualice si la lógica cambia
    only: ['totalFilteredArticles', 'filters', 'articleCountsByType'],
    onFinish: () => {
      isLoading.value = false;
      // initChart() es llamado por el watcher de chartConfig
    }
  });
};

const debouncedApplyFilters = debounce(applyFilters, 500);

// MODIFICADO: Observamos chartConfig para redibujar el gráfico si cambia su estructura
watch(chartConfig, () => {
    initChart();
});

watch([selectedType, dateStart, dateEnd, programsType, areasType], () => {
  debouncedApplyFilters();
});

watch(programsType, () => {
  areasType.value = 'all';
});

onMounted(() => {
  initChart();
});

onBeforeUnmount(() => {
  if (myChart) myChart.destroy();
  debouncedApplyFilters.cancel();
});

const getTrendPostulants = () => {
  const trendNumber = props.data?.newPostulantsThisWeek || 0;
  return trendNumber > 0 ? `+${trendNumber} esta semana` : "Sin nuevos registros";
};

const getTrendArticles = () => {
  const trendNumber = props.data?.newArticlesThisWeek || 0;
  return trendNumber > 0 ? `+${trendNumber} publicados` : "Sin nuevos registros";
};

const validateDates = () => {
  if (dateStart.value && dateEnd.value && dateStart.value > dateEnd.value) {
    const temp = dateStart.value;
    dateStart.value = dateEnd.value;
    dateEnd.value = temp;
  }
};

watch([dateStart, dateEnd], validateDates);
</script>

<template>
  <Head title="Dashboard" />
  <HeadLogo title="Inicio" />
  <LayoutAuthenticated>
    <SectionMain>
      <template v-if="role === 'Admin' || role === 'Editor'">
        <SectionTitleLineWithButton
          :icon="mdiChartTimelineVariant"
          title="Descripción general"
          main 
        />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
          <CardBoxWidget
            :trend="getTrendPostulants()"
            :trend-type="data.newPostulantsThisWeek > 0 ? 'up' : 'info'"
            color="text-emerald-500"
            :icon="mdiAccountMultiple"
            :number="data.postulants"
            label="Postulantes"
          />

          <CardBoxWidget
            :trend="getTrendArticles()"
            :trend-type="data.newArticlesThisWeek > 0 ? 'up' : 'alert'"
            color="text-blue-500"
            :icon="mdiBookEducation"
            :number="data.articles"
            label="Artículos"
          />

          <CardBoxWidget
            :trend="data.callsActives + ' Disponibles'"
            trend-type="info"
            color="text-red-500"
            :icon="mdiBullhorn"
            :number="data.calls"
            label="Convocatorias"
          />
        </div>

        <!-- Filtros -->
        <CardBox class="mb-6 p-4">
          <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-end">
            <!-- Select Programas -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Programas de Estudio
              </label>
              <select 
                v-model="programsType"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 
                       focus:ring focus:ring-indigo-200 focus:ring-opacity-50 
                       dark:bg-slate-800 dark:border-gray-600 dark:text-white"
                @change="areasType = 'all'" 
              >
                <option value="all">Todos los programas</option>
                <option 
                  v-for="program in props.programs" 
                  :key="program.id" 
                  :value="program.id"
                >
                  {{ program.name }}
                </option>
              </select>
            </div>

            <!-- Select Áreas -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Áreas Prioritarias
              </label>
              <select 
                v-model="areasType"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 
                       focus:ring focus:ring-indigo-200 focus:ring-opacity-50 
                       dark:bg-slate-800 dark:border-gray-600 dark:text-white"
                :disabled="isLoading || programsType === 'all'"
              >
                <option value="all">
                  {{ programsType === 'all' ? 'Seleccione un programa' : 'Todas las áreas' }}
                </option>
                <option 
                  v-for="area in filteredAreas" 
                  :key="area.id" 
                  :value="area.id"
                >
                  {{ area.name }}
                </option>
              </select>
              <p v-if="programsType !== 'all' && !filteredAreas.length" class="text-xs text-gray-500 mt-1">
                No hay áreas para este programa
              </p>
            </div>

            <!-- Select Tipo Artículo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Tipo de Artículo
              </label>
              <select 
                v-model="selectedType"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 
                       focus:ring focus:ring-indigo-200 focus:ring-opacity-50 
                       dark:bg-slate-800 dark:border-gray-600 dark:text-white"
              >
                <option value="all">Todos los tipos</option>
                <option 
                  v-for="type in props.articleTypes" 
                  :key="type.id" 
                  :value="type.id"
                >
                  {{ type.name }}
                </option>
              </select>
            </div>

            <!-- Fechas  -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Inicio</label>
              <input
                type="date"
                v-model="dateStart"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 
                       focus:ring focus:ring-indigo-200 focus:ring-opacity-50 
                       dark:bg-slate-800 dark:border-gray-600 dark:text-white"
                @change="validateDates"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Fin</label>
              <input
                type="date"
                v-model="dateEnd"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 
                       focus:ring focus:ring-indigo-200 focus:ring-opacity-50 
                       dark:bg-slate-800 dark:border-gray-600 dark:text-white"
                @change="validateDates"
              >
            </div>
          </div>
        </CardBox>

        <!-- Gráfico dinámico -->
        <CardBox class="relative">
          <div v-if="isLoading" class="absolute inset-0 bg-white dark:bg-slate-900/70 flex items-center justify-center z-10 rounded-lg">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
          </div>
          <div class="chart-container" style="position: relative; height:400px; width:100%">
            <canvas id="articlesChart"></canvas>
          </div>
          <div class="mt-4 text-sm text-gray-500 dark:text-gray-400 text-center">
            <span v-if="!hasFilters">Mostrando la distribución total de artículos por tipo.</span>
            <span v-else>Mostrando la cantidad de artículos que coinciden con los filtros aplicados.</span>
          </div>
        </CardBox>
      </template>

      <template v-else-if="role === 'Postulante'">
        <CardBox>
          <TemplatesGuide />
        </CardBox>
      </template>

      <template v-else >
        <CardBox>
          <div class="text-center py-12">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Bienvenido al sistema</h3>
            <p class="text-gray-600 dark:text-gray-400">Selecciona una opción del menú para comenzar</p>
          </div>
        </CardBox>
      </template>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>
.chart-container {
  min-height: 400px;
}

select:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>
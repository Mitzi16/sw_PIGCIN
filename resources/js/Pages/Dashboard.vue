<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, onBeforeUnmount, computed, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
// Iconos de Material Design
import {
  mdiAccountMultiple,
  mdiBookEducation,
  mdiChartTimelineVariant,
  mdiBullhorn,
  mdiCalendar,
  mdiFileDocument,
  mdiAccountGroup,
  mdiChevronRight
} from "@mdi/js";
import SectionMain from "@/Components/SectionMain.vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import HeadLogo from "@/Components/HeadLogo.vue";
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
          data: props.articleTypes.map(type => {
            const countData = props.articleCountsByType.find(
              item => item.article_type_id === type.id
            );
            return countData ? countData.count : 0;
          }),
          backgroundColor: [
            '#15396F', 
            '#10b981', 
            '#f59e0b', 
            '#06b6d4', 
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)'
          ],
          borderColor: [
            '#15396F', '#10b981', '#f59e0b', '#06b6d4', '#8b5cf6', '#f97316'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (context) {
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
          backgroundColor: ['#15396F'], 
          borderColor: ['#0f2853'], 
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
      }
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
    only: ['totalFilteredArticles', 'filters', 'articleCountsByType'],
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

const debouncedApplyFilters = debounce(applyFilters, 500);

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
      
      <!-- ========================================== -->
      <!-- CONTENIDO DEL DASHBOARD (Visible para todos) -->
      <!-- ========================================== -->

      <!-- Banner Principal -->
      <div class="relative bg-[#15396F] rounded-xl overflow-hidden mb-6 min-h-[160px] shadow-sm flex items-center">
        <div class="absolute inset-0 bg-cover bg-right bg-no-repeat opacity-60 dark:opacity-40"
          :style="{ backgroundImage: `url('/img/fondo2.png')` }"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#15396F] via-[#15396F]/90 to-transparent"></div>
        <div class="relative z-10 p-6 md:p-8">
          <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-wide">Panel Principal</h1>
          <p class="text-blue-100 text-lg">Bienvenido al Sistema PIGCIN</p>
        </div>
      </div>

      <!-- Tarjetas de Resumen (Widgets) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <!-- Tarjeta: Usuarios -->
        <div
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition-shadow">
          <div class="flex items-center gap-4">
            <div
              class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path :d="mdiAccountGroup" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-slate-400 font-medium">Usuarios</p>
              <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ data?.postulants || 0 }}</p>
            </div>
          </div>
          <svg class="w-5 h-5 text-gray-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 24 24">
            <path :d="mdiChevronRight" />
          </svg>
        </div>

        <!-- Tarjeta: Artículos -->
        <div
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition-shadow">
          <div class="flex items-center gap-4">
            <div
              class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center text-green-600 dark:text-green-400">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path :d="mdiFileDocument" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-slate-400 font-medium">Artículos</p>
              <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ data?.articles || 0 }}</p>
            </div>
          </div>
          <svg class="w-5 h-5 text-gray-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 24 24">
            <path :d="mdiChevronRight" />
          </svg>
        </div>

        <!-- Tarjeta: Convocatorias -->
        <div
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition-shadow">
          <div class="flex items-center gap-4">
            <div
              class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/50 flex items-center justify-center text-orange-500 dark:text-orange-400">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path :d="mdiCalendar" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-slate-400 font-medium">Convocatorias</p>
              <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ data?.calls || 0 }}</p>
            </div>
          </div>
          <svg class="w-5 h-5 text-gray-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 24 24">
            <path :d="mdiChevronRight" />
          </svg>
        </div>

        <!-- Tarjeta: Avisos -->
        <div
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition-shadow">
          <div class="flex items-center gap-4">
            <div
              class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path :d="mdiBullhorn" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-slate-400 font-medium">Avisos</p>
              <p class="text-2xl font-bold text-gray-800 dark:text-white">3</p>
            </div>
          </div>
          <svg class="w-5 h-5 text-gray-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 24 24">
            <path :d="mdiChevronRight" />
          </svg>
        </div>
      </div>

      <!-- Sección: Accesos Rápidos -->
      <div class="mb-6">
        <div
          class="bg-[#15396F] dark:bg-slate-800 dark:border dark:border-slate-700 dark:border-b-0 text-white px-4 py-2 rounded-t-lg flex items-center gap-2 w-48 font-semibold text-sm">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path :d="mdiChartTimelineVariant" />
          </svg>
          Accesos Rápidos
        </div>

        <div
          class="bg-white dark:bg-slate-800 p-4 rounded-b-lg rounded-tr-lg shadow-sm border border-gray-100 dark:border-slate-700">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Select Programas -->
            <div class="bg-gray-50 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-lg p-3">
              <label
                class="flex items-center gap-2 text-xs font-bold text-[#15396F] dark:text-slate-300 mb-2 uppercase tracking-wide">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path :d="mdiFileDocument" />
                </svg>
                Áreas Prioritarias
              </label>
              <select v-model="programsType"
                class="w-full bg-transparent border-none p-0 text-sm text-gray-600 dark:text-white focus:ring-0 cursor-pointer"
                @change="areasType = 'all'">
                <option value="all">Seleccione un programa</option>
                <option v-for="program in props.programs" :key="program.id" :value="program.id">
                  {{ program.name }}
                </option>
              </select>
            </div>

            <!-- Select Tipos -->
            <div class="bg-gray-50 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-lg p-3">
              <label
                class="flex items-center gap-2 text-xs font-bold text-[#15396F] dark:text-slate-300 mb-2 uppercase tracking-wide">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path :d="mdiBookEducation" />
                </svg>
                Tipo de Artículo
              </label>
              <select v-model="selectedType"
                class="w-full bg-transparent border-none p-0 text-sm text-gray-600 dark:text-white focus:ring-0 cursor-pointer">
                <option value="all">Todos los tipos</option>
                <option v-for="type in props.articleTypes" :key="type.id" :value="type.id">
                  {{ type.name }}
                </option>
              </select>
            </div>

            <!-- Fecha Inicio -->
            <div class="bg-gray-50 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-lg p-3">
              <label
                class="flex items-center gap-2 text-xs font-bold text-[#15396F] dark:text-slate-300 mb-2 uppercase tracking-wide">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path :d="mdiCalendar" />
                </svg>
                Fecha Inicio
              </label>
              <input type="date" v-model="dateStart"
                class="w-full bg-transparent border-none p-0 text-sm text-gray-600 dark:text-white focus:ring-0 cursor-pointer dark:[color-scheme:dark]"
                @change="validateDates">
            </div>

            <!-- Fecha Fin -->
            <div class="bg-gray-50 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-lg p-3">
              <label
                class="flex items-center gap-2 text-xs font-bold text-[#15396F] dark:text-slate-300 mb-2 uppercase tracking-wide">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path :d="mdiCalendar" />
                </svg>
                Fecha Fin
              </label>
              <input type="date" v-model="dateEnd"
                class="w-full bg-transparent border-none p-0 text-sm text-gray-600 dark:text-white focus:ring-0 cursor-pointer dark:[color-scheme:dark]"
                @change="validateDates">
            </div>

          </div>
        </div>
      </div>

      <!-- Sección: Resumen de Actividad -->
      <div>
        <div
          class="bg-[#15396F] dark:bg-slate-800 dark:border dark:border-slate-700 dark:border-b-0 text-white px-4 py-2 rounded-t-lg flex justify-between items-center w-full font-semibold text-sm">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path :d="mdiChartTimelineVariant" />
            </svg>
            Resumen de Actividad
          </div>
          <button
            class="bg-white text-[#15396F] dark:bg-slate-700 dark:text-white px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-slate-600 transition">
            Ver más
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
              <path :d="mdiChevronRight" />
            </svg>
          </button>
        </div>

        <div
          class="bg-white dark:bg-slate-800 p-6 rounded-b-lg shadow-sm border border-gray-100 dark:border-slate-700 relative">

          <div v-if="isLoading"
            class="absolute inset-0 bg-white/80 dark:bg-slate-800/80 flex items-center justify-center z-10 rounded-b-lg">
            <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-[#15396F] dark:border-white">
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-center gap-6 mb-6">
            <div class="flex items-center gap-2 text-xs font-medium text-gray-600 dark:text-slate-300">
              <span class="w-8 h-3 bg-[#15396F] dark:bg-blue-600 rounded-sm"></span> Artículo Congreso
            </div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-600 dark:text-slate-300">
              <span class="w-8 h-3 bg-[#10b981] rounded-sm"></span> Artículo Revista
            </div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-600 dark:text-slate-300">
              <span class="w-8 h-3 bg-[#f59e0b] rounded-sm"></span> Póster Científico
            </div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-600 dark:text-slate-300">
              <span class="w-8 h-3 bg-[#06b6d4] rounded-sm"></span> Póster Difusión
            </div>
          </div>

          <div class="chart-container" style="position: relative; height:300px; width:100%">
            <canvas id="articlesChart"></canvas>
          </div>

          <div class="mt-4 text-xs text-gray-400 dark:text-slate-500 text-center">
            <span v-if="!hasFilters">Mostrando la distribución total de artículos por tipo.</span>
            <span v-else>Mostrando la cantidad de artículos que coinciden con los filtros aplicados.</span>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- GUÍA EXCLUSIVA PARA POSTULANTES            -->
      <!-- ========================================== -->
      <div v-if="role === 'Postulante'" class="mt-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-6">
        <TemplatesGuide />
      </div>

    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>
.chart-container {
  min-height: 300px;
}

select:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Para quitar la flecha por defecto de los selects y inputs en algunos navegadores */
select {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23333%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 0.65em auto;
  padding-right: 1.5rem;
}
</style>
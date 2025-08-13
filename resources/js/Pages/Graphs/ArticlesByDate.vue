<script setup>
import HeadLogo from "@/Components/HeadLogo.vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import Dropdown from "@/Components/Dropdown.vue";
import { mdiChartLine } from "@mdi/js";
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Chart, registerables } from 'chart.js';
import html2canvas from "html2canvas";
import jsPDF from "jspdf";

Chart.register(...registerables);

const props = defineProps({
  articlesByDate: {
    type: Array,
    required: true,
    default: () => []
  },
  dateRange: {
    type: Object,
    default: () => ({ start: null, end: null })
  }
});

// Formatear fechas para el gráfico
const chartData = computed(() => {
  return {
    labels: props.articlesByDate.map(item => item.date),
    datasets: [{
      label: 'Artículos postulados',
      data: props.articlesByDate.map(item => item.total),
      backgroundColor: 'rgba(54, 162, 235, 0.5)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 1,
      tension: 0.1,
      fill: true
    }]
  };
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
    },
    title: {
      display: true,
      text: 'Artículos por Fecha de Postulación',
      font: {
        size: 16
      }
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          return `${context.dataset.label}: ${context.raw}`;
        }
      }
    }
  },
  scales: {
    x: {
      title: {
        display: true,
        text: 'Fecha de Postulación'
      },
      grid: {
        display: false
      }
    },
    y: {
      title: {
        display: true,
        text: 'Cantidad de Artículos'
      },
      beginAtZero: true,
      ticks: {
        stepSize: 1,
        precision: 0
      }
    }
  }
});

let chartInstance = null;

onMounted(() => {
  const ctx = document.getElementById('dateChart');
  if (ctx) {
    chartInstance = new Chart(ctx, {
      type: 'line',
      data: chartData.value,
      options: chartOptions.value
    });
  }
});

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});

// Función para descargar el reporte
async function downloadReport(format) {
  try {
    const element = document.getElementById('report-content');
    const canvas = await html2canvas(element, { 
      scale: 2,
      logging: false,
      useCORS: true
    });

    if (format === 'pdf') {
      const pdf = new jsPDF('p', 'mm', 'a4');
      const imgProps = pdf.getImageProperties(canvas.toDataURL('image/png'));
      const pdfWidth = pdf.internal.pageSize.getWidth();
      const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
      pdf.addImage(canvas, 'PNG', 0, 0, pdfWidth, pdfHeight);
      pdf.save('articulos_por_fecha.pdf');
    } else {
      const link = document.createElement('a');
      link.href = canvas.toDataURL(`image/${format}`);
      link.download = `articulos_por_fecha.${format}`;
      link.click();
    }
  } catch (error) {
    console.error('Error al generar el reporte:', error);
  }
}
</script>

<template>
  <HeadLogo title="Artículos por Fecha de Postulación" />
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiChartLine"
        title="Artículos por Fecha de Postulación"
        main
      />

      <div id="report-content, charts-pdf-content" 
           class="bg-white dark:bg-gray-900 rounded-lg shadow p-3">
        
        <!-- Resumen estadístico -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 mb-6">
          <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg">
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Total Artículos</h3>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
              {{ articlesByDate.reduce((sum, item) => sum + item.total, 0) }}
            </p>
          </div>

          <div class="bg-green-50 dark:bg-green-900/30 p-3 rounded-lg">
            <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Período Analizado</h3>
            <p class="text-lg font-semibold text-green-600 dark:text-green-400">
              {{ dateRange.start }} - {{ dateRange.end }}
            </p>
          </div>

          <div class="bg-purple-50 dark:bg-purple-900/30 p-3 rounded-lg">
            <h3 class="text-sm font-medium text-purple-800 dark:text-purple-300">Días con Datos</h3>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
              {{ articlesByDate.length }}
            </p>
          </div>

          <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg">
            <Dropdown>
              <template #trigger>
                <button
                  class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 
                         border border-gray-300 dark:border-gray-600 
                         text-sm text-gray-800 dark:text-gray-100 px-3 py-2 rounded w-full"
                >
                  Opciones
                </button>
              </template>

              <template #content>
                <ul class="rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-md">
                  <li v-for="format in ['png','jpg','pdf']" :key="format">
                    <button
                      @click="downloadChartsAs(format)"
                      class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                      <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Descargar {{ format.toUpperCase() }}
                      </span>
                    </button>
                  </li>
                </ul>
              </template>
            </Dropdown>
          </div>
        </div>

        <!-- Gráfico -->
        <div class="chart-container mb-8" style="height: 400px;">
          <canvas id="dateChart"></canvas>
        </div>

        <!-- Tabla de datos -->
        <div class="overflow-x-auto">
          <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-gray-100">Detalle por Fecha</h3>
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Artículos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Porcentaje</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(item, index) in articlesByDate" :key="index">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {{ item.date }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  {{ item.total }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  {{
                    ((item.total / articlesByDate.reduce((sum, i) => sum + i.total, 0)) * 100).toFixed(2)
                  }}%
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>
.chart-container {
  position: relative;
}
</style>
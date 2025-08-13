<script setup>
import HeadLogo from "@/Components/HeadLogo.vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import Dropdown from "@/Components/Dropdown.vue";
import { mdiChartTimelineVariant } from "@mdi/js";
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Chart, registerables } from 'chart.js';
import html2canvas from "html2canvas";
import jsPDF from "jspdf";

const props = defineProps({
  users: { 
    type: Array, 
    required: true,
    default: () => []
  },
  totals: {
    type: Object,
    required: true
  }
});

// Función para generar color pastel consistente basado en el ID
function getColorFromId(id, alpha = 0.5) {
  const hue = (id * 60) % 360;
  return `hsla(${hue}, 70%, 70%, ${alpha})`;
}

// Registra todos los componentes de Chart.js
Chart.register(...registerables);

// Preparar datos para el gráfico
const chartDataConfig = ref({
  labels: props.users.map(user => user.name),
  datasets: [{
    label: 'Artículos por Postulante',
    data: props.users.map(user => user.articles_count),
    backgroundColor: props.users.map(user => getColorFromId(user.id, 0.5)),
    borderColor: props.users.map(user => getColorFromId(user.id, 1)),
    borderWidth: 1
  }]
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { 
      display: false
    },
    title: { 
      display: true, 
      text: 'Distribución de Artículos por Postulantes',
      font: {
        size: 16
      }
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          return `Artículos: ${context.raw}`;
        }
      }
    }
  },
  scales: {
    y: { 
      beginAtZero: true, 
      ticks: { 
        stepSize: 1,
        precision: 0
      },
      title: {
        display: true,
        text: 'Cantidad de Artículos'
      }
    },
    x: {
      title: {
        display: true,
        text: 'Postulantes'
      }
    }
  }
});

let myChart = null;

onMounted(() => {
  const ctx = document.getElementById('articlesChart');
  if (ctx) {
    myChart = new Chart(ctx, {
      type: 'bar',
      data: chartDataConfig.value,
      options: chartOptions.value
    });
  }
});

onBeforeUnmount(() => { 
  if (myChart) {
    myChart.destroy();
    myChart = null;
  }
});

// Función para descargar gráfica y tabla
async function downloadChartsAs(format = "pdf") {
  try {
    const element = document.getElementById("charts-pdf-content");
    if (!element) return;

    const noCaptureElements = document.querySelectorAll(".no-capture");
    noCaptureElements.forEach((el) => (el.style.visibility = "hidden"));

    const canvas = await html2canvas(element, { 
      backgroundColor: null, 
      scale: 2,
      logging: false,
      useCORS: true
    });

    noCaptureElements.forEach((el) => (el.style.visibility = "visible"));

    const imgData = canvas.toDataURL(`image/${format === 'pdf' ? 'png' : format}`);

    if (format === "pdf") {
      const pdf = new jsPDF("p", "mm", "a4");
      const imgProps = pdf.getImageProperties(imgData);
      const pdfWidth = pdf.internal.pageSize.getWidth();
      const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
      pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
      pdf.save("articulos_por_postulante.pdf");
    } else {
      const link = document.createElement("a");
      link.href = imgData;
      link.download = `articulos_por_postulante.${format}`;
      link.click();
    }
  } catch (error) {
    console.error("Error al generar el documento:", error);
  }
}
</script>

<template>
  <HeadLogo title="Artículos por postulante" />
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiChartTimelineVariant"
        title="Artículos Por Postulante"
        main
      />
      
      <div id="charts-pdf-content" class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow relative">
        <!-- Dropdown de opciones -->
        <div class="absolute top-4 right-4 z-10 no-capture">
          <Dropdown>
            <template #trigger>
              <button
                class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 text-sm text-gray-800 dark:text-white px-3 py-2 rounded"
              >
                Opciones
              </button>
            </template>

            <template #content>
              <ul class="rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-md dark:shadow-lg">
                <li>
                    <button
                      @click="downloadChartsAs('png')"
                      class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                      <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Descargar PNG
                      </span>
                    </button>
                  </li>
                  <li>
                    <button
                      @click="downloadChartsAs('jpg')"
                      class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                      <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Descargar JPG
                      </span>
                    </button>
                  </li>
                  <li>
                    <button
                      @click="downloadChartsAs('pdf')"
                      class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                      <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Descargar PDF
                      </span>
                    </button>
                  </li>
              </ul>
            </template>
          </Dropdown>
        </div>

        <!-- Resumen estadístico -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Total Postulantes</h3>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-100">{{ totals.users }}</p>
          </div>
          <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Total Artículos</h3>
            <p class="text-2xl font-bold text-green-600 dark:text-green-100">{{ totals.articles }}</p>
          </div>
          <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-purple-800 dark:text-purple-200">Promedio por Postulante</h3>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-100">{{ totals.avg_articles_per_user }}</p>
          </div>
        </div>

        <!-- Gráfica -->
        <div class="chart-container dark:text-gray-300" style="position: relative; height:400px; width:100%">
          <canvas id="articlesChart"></canvas>
        </div>
        
        <!-- Tabla -->
        <div class="mt-8 overflow-x-auto shadow-md rounded-lg">
          <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200">Datos detallados</h2>
          <table class="min-w-full border border-gray-200 dark:border-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th scope="col" class="px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Postulante
                </th>
                <th scope="col" class="px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Artículos Publicados
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id" 
                  :class="{'bg-gray-50 dark:bg-gray-700': user.id % 2 === 0, 'bg-white dark:bg-gray-800': user.id % 2 !== 0}">
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                  {{ user.name }}
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                  {{ user.articles_count }}
                </td>
              </tr>
              <tr class="bg-gray-100 dark:bg-gray-700 font-semibold">
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                  Total
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                  {{ totals.articles }}
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
  min-height: 400px;
}
</style>
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
  chartData: { 
    type: Array, 
    required: true,
    default: () => []
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
  labels: props.chartData.map(area => area.name),
  datasets: [{
    label: 'Artículos por Área Prioritaria',
    data: props.chartData.map(area => area.total),
    backgroundColor: props.chartData.map(area => getColorFromId(area.id, 0.5)),
    borderColor: props.chartData.map(area => getColorFromId(area.id, 1)),
    borderWidth: 1
  }]
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { 
      position: 'top',
      labels: {
        font: {
          size: 14
        }
      }
    },
    title: { 
      display: true, 
      text: 'Distribución de Artículos por Áreas Prioritarias',
      font: {
        size: 16
      }
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          const area = props.chartData[context.dataIndex];
          return [
            `Área: ${area.name}`,
            `Programa: ${area.program}`,
            `Artículos: ${context.raw}`
          ];
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
        text: 'Áreas Prioritarias'
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
      pdf.save("articulos_por_area.pdf");
    } else {
      const link = document.createElement("a");
      link.href = imgData;
      link.download = `articulos_por_area.${format}`;
      link.click();
    }
  } catch (error) {
    console.error("Error al generar el documento:", error);
  }
}
</script>

<template>
  <HeadLogo title="Artículos por área prioritaria" />
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiChartTimelineVariant"
        title="Artículos Por Área Prioritaria"
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
                  Área Prioritaria
                </th>
                <th scope="col" class="px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Programa
                </th>
                <th scope="col" class="px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Artículos
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="area in chartData" :key="area.id" 
                  :class="{'bg-gray-50 dark:bg-gray-700': area.id % 2 === 0, 'bg-white dark:bg-gray-800': area.id % 2 !== 0}">
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                  {{ area.name }}
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                  {{ area.program }}
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                  {{ area.total }}
                </td>
              </tr>
              <tr class="bg-gray-100 dark:bg-gray-700 font-semibold">
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                  Total
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                  -
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                  {{ chartData.reduce((sum, area) => sum + area.total, 0) }}
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
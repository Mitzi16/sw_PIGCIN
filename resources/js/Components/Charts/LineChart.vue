<script setup>
import { ref, watch, onMounted, defineProps } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  type: {
    type: String,
    default: "line", // acepta 'line', 'bar', 'pie', etc.
  },
});

const root = ref(null);
let chart;

function createChart() {
  if (chart) {
    chart.destroy();
  }

  chart = new Chart(root.value, {
    type: props.type,
    data: props.data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: props.type === 'pie' ? {} : {
        y: {
          display: true,
        },
        x: {
          display: true,
        },
      },
      plugins: {
        legend: {
          display: true,
        },
      },
    },
  });
}

onMounted(createChart);

watch(() => props.data, createChart, { deep: true });
watch(() => props.type, createChart);
</script>

<template>
  <canvas ref="root" />
</template>
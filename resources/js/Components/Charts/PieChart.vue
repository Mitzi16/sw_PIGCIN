
<script setup>
import { Chart } from 'vue-chartjs'
import {
  Chart as ChartJS,
  PieController,
  ArcElement,
  Tooltip,
  Legend,
  Title
} from 'chart.js'

ChartJS.register(PieController, ArcElement, Tooltip, Legend, Title);

const props = defineProps({
  chartData: {
    type: Object,
    required: true,
  },
  chartOptions: {
    type: Object,
    default: () => ({}),
  },
});
</script>

<template>
  <Chart
    type="pie"
    :data="chartData"
    :options="chartOptions"
    class="w-full max-w-xl mx-auto"
  />
</template>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip } from "chart.js";

const props = defineProps({ data: { type: Object, required: true } });
const root = ref(null);
let chart;

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

onMounted(() => {
    chart = new Chart(root.value, {
        type: "bar",
        data: props.data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true } },
            scales: {
                x: { display: true },
                y: { display: true },
            },
        },
    });
});

watch(() => props.data, (data) => {
    if (chart) {
        chart.data = data;
        chart.update();
    }
});
</script>
<template>
    <canvas ref="root" />
</template>
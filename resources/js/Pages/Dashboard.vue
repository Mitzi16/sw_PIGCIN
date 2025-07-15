<script setup>
import { Head } from "@inertiajs/vue3";
import { computed, ref, onMounted } from "vue";
import { useMainStore } from "@/stores/main";
import {
  mdiAccountMultiple,
  mdiBookEducation,
  mdiChartTimelineVariant,
  mdiMonitorCellphone,
  mdiReload,
  mdiGithub,
  mdiChartPie,
  mdiViewModule,
  mdiBullhorn,
} from "@mdi/js";
import * as chartConfig from "@/Components/Charts/chart.config.js";
import LineChart from "@/Components/Charts/LineChart.vue";
import SectionMain from "@/Components/SectionMain.vue";
import CardBoxWidget from "@/Components/CardBoxWidget.vue";
import CardBox from "@/Components/CardBox.vue";
import TableSampleClients from "@/Components/TableSampleClients.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseButton from "@/Components/BaseButton.vue";
import CardBoxTransaction from "@/Components/CardBoxTransaction.vue";
import CardBoxClient from "@/Components/CardBoxClient.vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import SectionBannerStarOnGitHub from "@/Components/SectionBannerStarOnGitHub.vue";
import HeadLogo from "@/Components/HeadLogo.vue";

const props = defineProps({
  data: {
    type: Object,
    default: null,
  },
});

const mainStore = useMainStore();
const chartData = ref(null);
const clientBarItems = computed(() => mainStore.clients.slice(0, 4));
const transactionBarItems = computed(() => mainStore.history);

const fillChartData = () => {
  chartData.value = chartConfig.sampleChartData();
};

onMounted(() => {
  fillChartData();
});

const getTrendPostulants = () => {
  const trendNumber = props.data.newPostulantsThisWeek;
  if (trendNumber > 0) {
    return '+' + trendNumber + ' esta semana';
  }
  return 'Sin nuevos registros';
}

const getTrendArticles = () => {
  const trendNumber = props.data.newArticlesThisWeek;
  if (trendNumber > 0) {
    return '+' + trendNumber + ' publicados';
  }
  return 'Sin nuevos registros';
}

</script>

<template>
  <HeadLogo title="Inicio" />
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiChartTimelineVariant" title="Descripción general" main>
      </SectionTitleLineWithButton>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        <CardBoxWidget :trend="getTrendPostulants()" :trend-type="data.newPostulantsThisWeek > 0 ? 'up' : 'info'"
          color="text-emerald-500" :icon="mdiAccountMultiple" :number="data.postulants" label="Postulantes" />

        <CardBoxWidget :trend="getTrendArticles()" :trend-type="data.newArticlesThisWeek > 0 ? 'up' : 'alert'"
          color="text-blue-500" :icon="mdiBookEducation" :number="data.articles" label="Artículos" />

        <CardBoxWidget :trend="data.callsActives + ' Disponibles'" trend-type="info" color="text-red-500"
          :icon="mdiBullhorn" :number="data.calls" label="Convocatorias" />
      </div>

      <SectionTitleLineWithButton :icon="mdiChartPie" title="Trends overview">
        <BaseButton :icon="mdiReload" color="whiteDark" @click="fillChartData" />
      </SectionTitleLineWithButton>

      <CardBox class="mb-6">
        <div v-if="chartData">
          <line-chart :data="chartData" class="h-96" />
        </div>
      </CardBox>

    </SectionMain>
  </LayoutAuthenticated>
</template>

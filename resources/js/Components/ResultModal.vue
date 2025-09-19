<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  showModal: Boolean,
  article_id: Number,
  reviewerId: Number
});

const emit = defineEmits(['close']);

const evaluations = ref([]);
const articleScore = ref(0);
const isLoading = ref(false);
const errorMessage = ref(null);

watch(() => props.showModal, async (val) => {
  if (val) {
    await fetchEvaluations();
  }
});

const fetchEvaluations = async () => {
  isLoading.value = true;
  errorMessage.value = null;
  evaluations.value = [];
  articleScore.value = 0;
  
  try {
    // Usa la ruta de Inertia o axios según tu configuración
    const url = route('article.evaluation', { articleId: props.article_id });
    
    const params = {};
    if (props.reviewerId) {
      params.reviewer_id = props.reviewerId;
    }

    const response = await axios.get(url, { params });
    
    if (response.data) {
      evaluations.value = response.data.evaluations || [];
      articleScore.value = response.data.article_score || 0;
      
      if (evaluations.value.length === 0) {
        errorMessage.value = "No se encontraron evaluaciones para este artículo";
      }
    }
  } catch (error) {
    console.error("Error cargando evaluaciones:", error);
    handleApiError(error);
  } finally {
    isLoading.value = false;
  }
};

const handleApiError = (error) => {
  if (error.response) {
    switch (error.response.status) {
      case 404:
        errorMessage.value = "El recurso solicitado no fue encontrado";
        break;
      case 500:
        errorMessage.value = "Error interno del servidor";
        break;
      default:
        errorMessage.value = `Error: ${error.response.statusText}`;
    }
  } else if (error.request) {
    errorMessage.value = "No se recibió respuesta del servidor";
  } else {
    errorMessage.value = "Error al configurar la solicitud";
  }
};

const closeModal = () => {
  emit('close');
};
</script>

<template>
  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 dark:bg-opacity-70">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col">
      <!-- Header -->
      <div class="flex justify-between items-center border-b dark:border-gray-700 p-4 sticky top-0 bg-white dark:bg-gray-800 z-10">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Resultados de Evaluación</h2>
        <button 
          @click="closeModal" 
          class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-2xl font-light focus:outline-none"
        >
          &times;
        </button>
      </div>

      <!-- Contenido -->
      <div class="overflow-y-auto flex-1 p-4">
        <!-- Loading state -->
        <div v-if="isLoading" class="flex justify-center items-center h-64">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>

        <!-- Mensaje de error -->
        <div v-if="errorMessage" class="text-center py-8 text-red-500 dark:text-red-400">
          {{ errorMessage }}
        </div>

        <!-- Evaluaciones -->
        <div v-if="!isLoading && !errorMessage && evaluations.length > 0">
          <div v-for="(evaluation, index) in evaluations" :key="index" 
               class="border dark:border-gray-700 p-4 rounded-lg mb-4 bg-gray-50 dark:bg-gray-700">
            <div class="flex justify-between items-start">
              <div>
                <p class="font-medium text-gray-800 dark:text-gray-200">{{ evaluation.question }}</p>
                <p class="text-gray-600 dark:text-gray-300 mt-1">{{ evaluation.answer }}</p>
              </div>
              <span class="px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm font-medium">
                {{ evaluation.score }} pts
              </span>
            </div>
            <div v-if="evaluation.reviewer_name" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Evaluado por: {{ evaluation.reviewer_name }}
            </div>
          </div>

          <!-- Total -->
          <div class="border dark:border-gray-700 p-4 rounded-lg bg-gray-100 dark:bg-gray-600">
            <div class="flex justify-between items-center">
              <p class="font-bold text-lg text-gray-800 dark:text-gray-200">Puntuación Total</p>
              <span class="px-4 py-2 rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-lg font-bold">
                {{ articleScore }} pts
              </span>
            </div>
          </div>
        </div>

        <!-- No hay evaluaciones -->
        <div v-if="!isLoading && !errorMessage && evaluations.length === 0" class="text-center py-8">
          <p class="text-gray-500 dark:text-gray-400">No se encontraron evaluaciones para este artículo.</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t dark:border-gray-700 p-4 sticky bottom-0 bg-white dark:bg-gray-800 flex justify-end">
        <button 
          @click="closeModal" 
          class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-gray-400"
        >
          Cerrar
        </button>
      </div>
    </div>
  </div>
</template>
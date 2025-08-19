<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  showModal: Boolean,
  articleTypeId: Number,
  articleId: Number // para saber qué artículo se evalúa
});

const emit = defineEmits(['close']);

const rubric = ref(null);
const selectedAnswers = ref({}); // { rubric_item_id: rubric_answer_id }
const scores = ref({}); // { rubric_item_id: score }

watch(() => props.showModal, (val) => {
  if (val) {
    fetchRubric();
  }
});

const fetchRubric = async () => {
  try {
    const res = await axios.get(`/api/rubrics/by-article-type/${props.articleTypeId}`);
    rubric.value = res.data.rubric;
    selectedAnswers.value = {};
    scores.value = {};
  } catch (error) {
    console.error("Error cargando rúbrica:", error);
  }
};

const updateScore = (rubricItemId, answer) => {
  scores.value[rubricItemId] = answer.score;
};

const submitEvaluation = async () => {
  const answersPayload = Object.keys(selectedAnswers.value).map(itemId => ({
    rubric_item_id: parseInt(itemId),
    rubric_answer_id: selectedAnswers.value[itemId],
    score: scores.value[itemId] || 0
  }));

  try {
    await axios.post("/api/evaluations", {
      article_id: props.articleId,
      answers: answersPayload
    });
    closeModal();
  } catch (error) {
    console.error("Error guardando evaluación:", error);
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
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ rubric?.title }}</h2>
        <button 
          @click="closeModal" 
          class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-2xl font-light focus:outline-none"
        >
          &times;
        </button>
      </div>

      <!-- Preguntas -->
      <div v-if="rubric" class="overflow-y-auto flex-1 p-4">
        <div v-for="item in rubric.items" :key="item.id" class="border dark:border-gray-700 p-4 rounded-lg mb-4 bg-gray-50 dark:bg-gray-700">
          <p class="font-medium mb-3 text-gray-800 dark:text-gray-200">{{ item.question }}</p>

          <div class="space-y-3">
            <label 
              v-for="answer in item.answers" 
              :key="answer.id" 
              class="flex items-start gap-3 p-3 rounded-lg transition-colors duration-200"
              :class="{
                'bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800': selectedAnswers[item.id] === answer.id,
                'hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer': true
              }"
            >
              <input
                type="radio"
                :name="'rubric_item_' + item.id"
                :value="answer.id"
                v-model="selectedAnswers[item.id]"
                @change="updateScore(item.id, answer)"
                class="mt-1 flex-shrink-0"
              />
              <div class="flex-1">
                <span class="text-gray-800 dark:text-gray-200">{{ answer.text }}</span>
                <span class="block text-sm mt-1 font-medium" 
                  :class="{
                    'text-blue-600 dark:text-blue-400': selectedAnswers[item.id] === answer.id,
                    'text-gray-500 dark:text-gray-400': selectedAnswers[item.id] !== answer.id
                  }"
                >
                  ({{ answer.score }} pts)
                </span>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t dark:border-gray-700 p-4 sticky bottom-0 bg-white dark:bg-gray-800 flex justify-end gap-3">
        <button 
          @click="closeModal" 
          class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-gray-400"
        >
          Cancelar
        </button>
        <button 
          @click="submitEvaluation" 
          class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          :disabled="Object.keys(selectedAnswers).length !== rubric?.items?.length"
        >
          Guardar Evaluación
        </button>
      </div>
    </div>
  </div>
</template>
<template>
  <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div
      class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
      <div class="mt-3">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Evaluación del Artículo
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Cargando rúbrica...</p>
        </div>

        <!-- Evaluation Form -->
        <div v-else-if="rubric" class="space-y-6">
          <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <h4 class="font-semibold text-blue-900 dark:text-blue-100">{{ rubric.title }}</h4>
            <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
              Responde todas las preguntas para completar la evaluación
            </p>
          </div>



          <!-- Questions -->
          <form @submit.prevent="submitEvaluation" class="space-y-6">
            <div v-for="(item, index) in rubric.items" :key="item.id"
              class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
              <h5 class="font-medium text-gray-900 dark:text-white mb-3">
                {{ index + 1 }}. {{ item.question }}
              </h5>

              <div class="space-y-2">
                <label v-for="answer in item.answers" :key="answer.id"
                  class="flex items-start space-x-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                  :class="{ 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600': selectedAnswers[item.id] === answer.id }">
                  <input type="radio" :name="`item_${item.id}`" :value="answer.id" v-model="selectedAnswers[item.id]"
                    @change="updateScore" class="mt-1 text-blue-600 focus:ring-blue-500" />
                  <div class="flex-1">
                    <div class="flex justify-between items-start">
                      <span class="text-gray-900 dark:text-white">{{ answer.text }}</span>
                      <span class="text-sm font-medium text-blue-600 dark:text-blue-400 ml-2">
                        {{ answer.score }} pts
                      </span>
                    </div>
                  </div>
                </label>
              </div>
            </div>

            <!-- Real-time Score Display -->
            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
              <div class="flex justify-between items-center">
                <span class="text-green-800 dark:text-green-200 font-medium">Puntaje actual:</span>
                <span class="text-2xl font-bold text-green-600 dark:text-green-400">
                  {{ currentScore }} pts
                </span>
              </div>
              <div class="w-full bg-green-200 dark:bg-green-700 rounded-full h-2 mt-2">
                <div class="bg-green-600 dark:bg-green-400 h-2 rounded-full transition-all duration-300"
                  :style="{ width: `${scorePercentage}%` }"></div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <button type="button" @click="closeModal"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                Cancelar
              </button>
              <button type="submit" :disabled="!isFormComplete || submitting"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <span v-if="submitting">Guardando...</span>
                <span v-else>Guardar Evaluación</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-8">
          <div class="text-red-500 mb-4">
            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <p class="text-gray-600 dark:text-gray-400">{{ error }}</p>
          <button @click="loadRubric" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Reintentar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  showModal: {
    type: Boolean,
    required: true
  },
  articleTypeId: {
    type: Number,
    required: true
  },
  articleId: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['close', 'evaluationSaved'])

// State
const loading = ref(false)
const submitting = ref(false)
const error = ref(null)
const rubric = ref(null)
const selectedAnswers = reactive({})
const currentScore = ref(0)

// Computed
const isFormComplete = computed(() => {
  if (!rubric.value) return false
  return rubric.value.items.every(item => selectedAnswers[item.id])
})

const maxScore = computed(() => {
  if (!rubric.value) return 0
  return rubric.value.items.reduce((total, item) => {
    const maxItemScore = Math.max(...item.answers.map(answer => answer.score))
    return total + maxItemScore
  }, 0)
})

const scorePercentage = computed(() => {
  if (maxScore.value === 0) return 0
  return Math.round((currentScore.value / maxScore.value) * 100)
})

// Methods
const loadRubric = async () => {
  loading.value = true
  error.value = null

  try {
    const { data } = await axios.get(`/api/rubrics/by-article-type/${props.articleTypeId}`)
    rubric.value = data.rubric

    // Load existing evaluation if any
    await loadExistingEvaluation()
  } catch (err) {
    error.value = 'Error al cargar la rúbrica. Por favor, inténtalo de nuevo.'
    console.error('Error loading rubric:', err)
  } finally {
    loading.value = false
  }
}

const loadExistingEvaluation = async () => {
  try {
    const { data } = await axios.get(`/api/article-evaluation/${props.articleId}`)

    if (data.evaluations && data.evaluations.length > 0) {
      // Pre-fill existing answers
      data.evaluations.forEach(evaluation => {
        const item = rubric.value.items.find(i => i.question === evaluation.question)
        if (item) {
          const answer = item.answers.find(a => a.text === evaluation.answer)
          if (answer) {
            selectedAnswers[item.id] = answer.id
          }
        }
      })
      updateScore()
    }
  } catch (err) {
    // No existing evaluation, that's fine
    console.log('No existing evaluation found')
  }
}

const updateScore = () => {
  if (!rubric.value) return

  let total = 0
  rubric.value.items.forEach(item => {
    const selectedAnswerId = selectedAnswers[item.id]
    if (selectedAnswerId) {
      const answer = item.answers.find(a => a.id === selectedAnswerId)
      if (answer) {
        total += answer.score
      }
    }
  })

  currentScore.value = total
}

const submitEvaluation = async () => {
  if (!isFormComplete.value) return

  submitting.value = true

  try {
    const answers = rubric.value.items.map(item => {
      const selectedAnswerId = selectedAnswers[item.id]
      const answer = item.answers.find(a => a.id === selectedAnswerId)

      return {
        rubric_item_id: item.id,
        rubric_answer_id: selectedAnswerId,
        score: answer.score
      }
    })

    const { data } = await axios.post('/api/evaluations', {
      article_id: props.articleId,
      answers: answers
    })

    // Emit event with the new score
    emit('evaluationSaved', {
      totalScore: data.total_score,
      reviewerId: data.reviewer_id
    })

    closeModal()
  } catch (err) {
    error.value = 'Error al guardar la evaluación. Por favor, inténtalo de nuevo.'
    console.error('Error saving evaluation:', err)
  } finally {
    submitting.value = false
  }
}

const closeModal = () => {
  emit('close')
}

// Watchers
watch(() => props.showModal, (newValue) => {
  if (newValue) {
    loadRubric()
  } else {
    // Reset state when modal closes
    rubric.value = null
    Object.keys(selectedAnswers).forEach(key => delete selectedAnswers[key])
    currentScore.value = 0
    error.value = null
  }
})

// Load rubric when component mounts if modal is already open
onMounted(() => {
  if (props.showModal) {
    loadRubric()
  }
})
</script>

<script setup>
import LayoutMain from '@/Layouts/LayoutMain.vue'
import HeadLogo from '@/Components/HeadLogo.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import { mdiContentSave, mdiClose, mdiPlus, mdiTrashCan } from '@mdi/js'
import { Link, useForm } from '@inertiajs/vue3'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'

const props = defineProps({
  title: String,
  routeName: String,
})

const form = useForm({ 
  name: null,
  rubrics: [
    {
      title: '',
      items: []
    }
  ]
})

// Agregar item si no supera el límite de 10
const addItem = (rubricIndex) => {
  if (form.rubrics[rubricIndex].items.length < 10) {
    form.rubrics[rubricIndex].items.push({
      question: '',
      answers: []
    })
  }
}

// Agregar respuesta si no supera el límite de 4
const addAnswer = (rubricIndex, itemIndex) => {
  if (form.rubrics[rubricIndex].items[itemIndex].answers.length < 5) {
    form.rubrics[rubricIndex].items[itemIndex].answers.push({
      text: '',
      score: 0
    })
  }
}

const removeItem = (rubricIndex, itemIndex) => {
  form.rubrics[rubricIndex].items.splice(itemIndex, 1)
}

const removeAnswer = (rubricIndex, itemIndex, answerIndex) => {
  form.rubrics[rubricIndex].items[itemIndex].answers.splice(answerIndex, 1)
}

const saveForm = () => {
  form.post(route(`${props.routeName}store`))
}
</script>

<template>
  <HeadLogo :title="title" />
  <LayoutMain>
    <SectionTitleLineWithButton :icon="mdiPlus" :title="title" main>
      <Link :href="route(`${routeName}index`)">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x"
          viewBox="0 0 16 16">
          <path
            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
        </svg>
      </Link>
    </SectionTitleLineWithButton>

    <CardBox isForm @submit.prevent="saveForm">
      <FormField label="Nombre del Tipo de Artículo:" :required="true" help="Nombre del tipo" :error="form.errors.name">
        <FormControl v-model="form.name" placeholder="Ej: Artículo Científico, Ensayo, etc." />
      </FormField>

      <!-- RRubric (single) -->
      <div class="mt-8">
        <div v-for="(rubric, rubricIndex) in form.rubrics" :key="rubricIndex" class="mb-6">
          <CardBox class="bg-gray-50">
            <h4 class="text-md font-medium mb-4">Rúbrica</h4>

            <FormField 
              label="Título de la Rúbrica:"
              :required="true"
              :error="form.errors[`rubrics.${rubricIndex}.title`]"
            >
              <FormControl 
                v-model="rubric.title" 
                placeholder="Ej: Criterios de Evaluación General" 
              />
            </FormField>

            <!-- Questions -->
            <div class="mt-4">
              <div class="flex justify-between items-center mb-3">
                <h5 class="text-sm font-medium">Preguntas/Criterios</h5>
                <div>
                  <BaseButton
                    v-if="rubric.items.length < 10"
                    @click="addItem(rubricIndex)" 
                    :icon="mdiPlus" 
                    color="success" 
                    label="Agregar Pregunta" 
                    small 
                    outline
                  />
                  <p v-else class="text-xs text-gray-500 italic">Máximo 10 preguntas alcanzado</p>
                </div>
              </div>

              <div v-for="(item, itemIndex) in rubric.items" :key="itemIndex" class="mb-4 p-4  rounded">
                <div class="flex justify-between items-center mb-2">
                  <h6 class="text-sm font-medium">Pregunta {{ itemIndex + 1 }}</h6>
                  <BaseButton 
                    @click="removeItem(rubricIndex, itemIndex)" 
                    :icon="mdiTrashCan" 
                    color="danger" 
                    small 
                    outline
                  />
                </div>

                <FormField 
                  :label="`Pregunta ${itemIndex + 1}:`" 
                  :required="true"
                  :error="form.errors[`rubrics.${rubricIndex}.items.${itemIndex}.question`]"
                >
                  <FormControl 
                    v-model="item.question" 
                    placeholder="Ej: ¿El artículo presenta una metodología clara?" 
                  />
                </FormField>

                <!-- Response Options -->
                <div class="mt-3">
                  <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-medium">Opciones de Respuesta</span>
                    <div>
                      <BaseButton 
                        v-if="item.answers.length < 5"
                        @click="addAnswer(rubricIndex, itemIndex)" 
                        :icon="mdiPlus" 
                        color="info" 
                        label="Agregar Respuesta" 
                        small 
                        outline
                      />
                      <p v-else class="text-xs text-gray-500 italic">Máximo 4 respuestas alcanzado</p>
                    </div>
                  </div>

                  <div v-for="(answer, answerIndex) in item.answers" :key="answerIndex" class="flex gap-2 mb-2 items-end">
                    <div class="flex-1">
                      <FormField 
                        :label="`Respuesta ${answerIndex + 1}:`"
                        :error="form.errors[`rubrics.${rubricIndex}.items.${itemIndex}.answers.${answerIndex}.text`]"
                      >
                        <FormControl 
                          v-model="answer.text" 
                          placeholder="Ej: Excelente, Bueno, Regular, Deficiente" 
                        />
                      </FormField>
                    </div>
                    <div class="w-24">
                      <FormField 
                        label="Puntaje:"
                        :error="form.errors[`rubrics.${rubricIndex}.items.${itemIndex}.answers.${answerIndex}.score`]"
                      >
                        <FormControl 
                          v-model="answer.score" 
                          type="number" 
                          step="0.1"
                          placeholder="0.0" 
                        />
                      </FormField>
                    </div>
                    <BaseButton 
                      @click="removeAnswer(rubricIndex, itemIndex, answerIndex)" 
                      :icon="mdiTrashCan" 
                      color="danger" 
                      small 
                      outline
                    />
                  </div>
                </div>
              </div>
            </div>
          </CardBox>
        </div>
      </div>

      <template #footer>
        <BaseButtons>
          <BaseButton 
            :href="route(`${routeName}index`)" 
            :icon="mdiClose" 
            color="white" 
            label="Cancelar" 
          />
          <BaseButton 
            @click="saveForm" 
            :icon="mdiContentSave" 
            type="submit" 
            color="success" 
            label="Guardar" 
            :disabled="form.processing"
          />
        </BaseButtons>
      </template>
    </CardBox>
  </LayoutMain>
</template>

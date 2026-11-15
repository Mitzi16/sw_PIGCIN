<script setup>
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import BaseButton from "@/Components/BaseButton.vue";
import {
  mdiAccountMultiple,
  mdiFileDocumentOutline,
  mdiCertificateOutline,
} from "@mdi/js";
import { computed, inject } from "vue";
// Ya no necesitamos CardBoxComponentEmpty
// import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";

const props = inject("props");
const getRole = inject("getRole", () => false);

const emit = defineEmits(["changeSection"]);

const filteredStatuses = computed(() => {
  if (!props.articleStatuses) return [];
  return props.articleStatuses.filter((item) => item.id <= 5);
});

// 👇 NUEVA LÓGICA: Verificamos si hay algún comentario real
const hasComments = computed(() => {
    const comment = props.article?.comment;
    const commentForEditor = props.article?.comment_for_editor;
    
    // Retorna true si al menos uno de los dos tiene texto
    return (comment && comment.trim() !== '') || (commentForEditor && commentForEditor.trim() !== '');
});

const dateToLocal = (date) => {
  if (!date) return "";
  const [fecha, hora] = date.split(" ");
  if (!fecha || !hora) return "fecha inválida";
  const [day, month, year] = fecha.split("-");
  const [hour, minute] = hora.split(":");
  const d = new Date(year, month - 1, day, hour, minute);
  if (isNaN(d.getTime())) return "fecha inválida";
  return `${day}-${month}-${year} ${hour}:${minute}`;
};
</script>

<template>
  <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-6">
    Proceso editorial
  </h2>

  <!-- STEPPER HORIZONTAL -->
  <div class="relative flex items-start justify-between w-full mb-8">
    <div class="absolute top-5 left-0 right-0 h-[2px] bg-gray-200 dark:bg-gray-700 -z-10"></div>

    <div
      v-for="item in filteredStatuses"
      :key="item.id"
      class="flex flex-col items-center relative flex-1"
    >
      <div
        class="w-10 h-10 rounded-full flex items-center justify-center bg-white dark:bg-slate-800 transition-all duration-300 z-10"
        :class="{
          'border-2 border-green-200 text-green-500': item.id < props.article.article_status_id,
          'border-[3px] border-blue-600 text-blue-600 font-bold text-lg': item.id === props.article.article_status_id,
          'border-2 border-transparent text-gray-400 font-bold text-lg': item.id > props.article.article_status_id,
        }"
      >
        <svg
          v-if="item.id < props.article.article_status_id"
          class="w-5 h-5 text-green-500"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="3"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <span v-else>{{ item.id }}</span>
      </div>

      <div
        class="mt-3 text-sm font-semibold text-center w-32 leading-tight px-1"
        :class="{
          'text-gray-800 dark:text-gray-200': item.id <= props.article.article_status_id,
          'text-gray-400 dark:text-gray-500': item.id > props.article.article_status_id,
        }"
      >
        {{ item.name }}
      </div>

      <div
        v-if="item.id === props.article.article_status_id"
        class="text-xs text-blue-500 mt-1 font-medium"
      >
        {{ dateToLocal(props.article.updated_at) }}
      </div>
    </div>
  </div>

  <!-- BARRA DE ACCIONES -->
  <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
      
      <div class="flex items-center gap-3 text-gray-500">
        <span class="bg-[#e0f2fe] text-[#0369a1] px-4 py-1.5 rounded-full text-sm font-semibold">
          Acciones
        </span>
        <span class="text-sm text-gray-400">
          Asignar revisores y evaluación
        </span>
      </div>
      
      <div class="flex flex-wrap gap-2 justify-center md:justify-end">
        <BaseButton
          v-if="getRole(['Editor', 'Admin', 'Admin-Revista', 'Super Admin'])"
          color="info"
          :icon="mdiAccountMultiple"
          label="Asignar Revisores"
          @click="emit('changeSection', 'assigns')"
        />

        <BaseButton
          v-if="getRole(['Revisor', 'Editor', 'Admin', 'Admin-Revista', 'Super Admin'])"
          color="white"
          :icon="mdiFileDocumentOutline"
          label="Evaluación"
          class="border border-gray-300 text-gray-700"
          @click="emit('changeSection', 'evaluation')"
        />

        <BaseButton
          v-if="[3, 4].includes(props.article.article_status_id)"
          color="info"
          :icon="mdiCertificateOutline"
          label="Reconocimientos"
          class="bg-[#15396F] hover:bg-[#0f2a54] text-white border-none"
          @click="emit('changeSection', 'recognitions')"
        />
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 👇 COMENTARIOS DEL EDITOR (Solo si hay texto) 👇 -->
  <!-- ========================================== -->
  <div v-if="hasComments">
    <hr class="my-6 border-gray-200 dark:border-gray-700" />

    <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">
      Comentarios del editor
    </h2>

    <ul
      v-if="props.article.article_status.is_evaluation"
      role="list"
      class="max-w-sm divide-y divide-gray-200 dark:divide-gray-700"
    >
      <li class="py-3 sm:py-4">
        <div class="flex items-center space-x-3 rtl:space-x-reverse mb-2">
          <div class="flex-shrink-0">
            <img
              class="w-8 h-8 rounded-full"
              :src="props.article?.editor?.file?.path ?? '/img/user.jpg'"
              alt="Editor image"
            />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate dark:text-white">
              Editor en Jefe
            </p>
          </div>
        </div>
        <FormControl
          disabled
          type="textarea"
          height="h-24"
          v-model="props.article.comment"
          placeholder="Sin comentarios..."
        />
      </li>
    </ul>
  </div>
</template>
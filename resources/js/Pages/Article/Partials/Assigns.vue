<script setup>
import { mdiContentSave } from "@mdi/js";
import { computed, inject, ref } from "vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import { useForm } from "@inertiajs/vue3";

const props = inject("props");
const getRole = inject("getRole");

const form = useForm({
  article_id: props.article.id,
  editor_id: props.article.editor_id,
  article_status_id: props.article.article_status_id,
  reviewers: props.article.article_reviews.map(
    (reviewer) => reviewer.reviewer_id
  ),
});

const TotalAssigned = computed(() => form.reviewers.length);

const saveAssign = () => {
  form.post(route("articleReview.store"));
};

const checkedReviewer = (reviewer) => {
  return form.reviewers.includes(reviewer.id);
};

const toggleReviewer = (reviewer) => {
  const index = form.reviewers.indexOf(reviewer.id);
  if (index === -1) {
    form.reviewers.push(reviewer.id);
  } else {
    form.reviewers.splice(index, 1);
  }
};

/* ---- 🔹 Buscador ---- */
const search = ref("");

const filteredReviewers = computed(() => {
  if (!search.value) return props.reviewers;
  return props.reviewers.filter(
    (r) =>
      r.name.toLowerCase().includes(search.value.toLowerCase()) ||
      (r.area && r.area.toLowerCase().includes(search.value.toLowerCase())) ||
      (r.subarea &&
        r.subarea.toLowerCase().includes(search.value.toLowerCase()))
  );
});

/* ---- 🔹 Paginación ---- */
const currentPage = ref(1);
const perPage = 10; // cantidad de revisores por página

const totalPages = computed(() =>
  Math.ceil(filteredReviewers.value.length / perPage)
);

const paginatedReviewers = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredReviewers.value.slice(start, start + perPage);
});

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};
</script>

<template>
  <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">
    Asignar editor y revisores
  </h2>
  <div class="mt-5 grid grid-cols-1 gap-3">
    <!-- Selección de editor -->
    <div class="lg:mb-5">
      <div class="lg:mb-5">
        <FormField
          required
          label="Selecciona un editor:"
          :error="form.errors.editor_id"
        >
          <FormControl
            :disabled="!getRole(['Admin', 'Admin-Revista'])"
            v-model="form.editor_id"
            :options="props.editors"
          />
        </FormField>
      </div>

      <div class="lg:mb-5">
        <FormField
          required
          label="Teclea el nombre del revisor o su programa para buscarlo:"
          :error="form.errors.reviewers"
        >
          <FormControl
            v-model="search"
            placeholder="Buscar por nombre, área o subárea..."
          />
        </FormField>
      </div>
    </div>

    <!-- Selección de revisores -->
    <div class="lg:mb-5 col-span-2">
      <!-- Lista de revisores -->
      <div
        v-if="filteredReviewers.length > 0"
        class="border rounded border-gray-400"
      >
        <ul class="flex flex-col divide-y divide-gray-200">
          <li v-for="item in paginatedReviewers" :key="item.id">
            <label
              class="p-2 justify-between flex items-center px-3"
              :class="{
                'cursor-not-allowed': !getRole([
                  'Editor',
                  'Admin',
                  'Admin-Revista',
                ]),
                'cursor-pointer': getRole(['Editor', 'Admin', 'Admin-Revista']),
              }"
            >
              <span
                class="text-sm font-medium text-gray-900 dark:text-gray-300"
              >
                {{ item.name }}
                <span v-if="item.area" class="ml-2">- {{ item.area }}</span>
                <span v-if="item.subarea" class="ml-2"
                  >- {{ item.subarea }}</span
                >
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-xs">
                  Artículos asignados: {{ item.assigned_count }}
                </p>
              </span>

              <input
                :disabled="!getRole(['Editor', 'Admin', 'Admin-Revista'])"
                type="checkbox"
                class="sr-only peer"
                :value="item.id"
                :id="'reviewer_' + item.id"
                :checked="checkedReviewer(item)"
                @change="toggleReviewer(item)"
              />
              <div
                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"
              ></div>
            </label>
          </li>
        </ul>

        <!-- Controles de paginación -->
        <div
          class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-800"
        >
          <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-3 py-1 text-sm rounded border text-white bg-green-500 hover:bg-green-600 dark:bg-gray-700"
          >
            Anterior
          </button>
          <span class="text-sm"
            >Página {{ currentPage }} de {{ totalPages }}</span
          >
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 text-sm rounded border text-white bg-green-500 hover:bg-green-600 dark:bg-gray-700"
          >
            Siguiente
          </button>
        </div>
      </div>
      <CardBoxComponentEmpty v-else />
    </div>

    <!-- Botón guardar -->
    <div class="col-span-2">
      <BaseButtons class="mt-5">
        <BaseButton
          :disabled="!getRole(['Editor', 'Admin', 'Admin-Revista'])"
          @click="saveAssign()"
          :icon="mdiContentSave"
          color="success"
          label="Asignar"
          small
        />
      </BaseButtons>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, ref } from "vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";
import BaseButton from "@/Components/BaseButton.vue";
import { useForm } from "@inertiajs/vue3";

const props = inject("props");
const getRole = inject("getRole");
const emit = defineEmits(["close"]); 

const form = useForm({
  article_id: props.article.id,
  editor_id: props.article.editor_id,
  article_status_id: props.article.article_status_id,
  reviewers: props.article.article_reviews?.map((r) => r.reviewer_id) ?? [],
});

const saveAssign = () => {
  form.post(route("articleReview.store"), {
      onSuccess: () => emit("close"), 
  });
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

const search = ref("");

const filteredReviewers = computed(() => {
  if (!search.value) return props.reviewers;
  return props.reviewers.filter(
    (r) =>
      r.name.toLowerCase().includes(search.value.toLowerCase()) ||
      (r.area && r.area.toLowerCase().includes(search.value.toLowerCase())) ||
      (r.subarea && r.subarea.toLowerCase().includes(search.value.toLowerCase()))
  );
});

const currentPage = ref(1);
const perPage = 5;

const totalPages = computed(() => Math.ceil(filteredReviewers.value.length / perPage));

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
  <div class="flex flex-col h-full bg-white dark:bg-slate-800">
    <!-- Formulario Principal -->
    <div class="flex-1 overflow-y-auto pr-2">
      
      <!-- Selección de editor -->
      <div class="mb-6">
        <FormField required label="Editor responsable" :error="form.errors.editor_id">
          <!-- Aquí ya está incluido 'Super Admin' -->
          <FormControl
            :disabled="!getRole(['Admin', 'Admin-Revista', 'Super Admin'])"
            v-model="form.editor_id"
            :options="props.editors"
            placeholder="Seleccione un editor"
            class="w-full"
          />
        </FormField>
      </div>

      <!-- Buscador -->
      <div class="mb-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">Buscar revisores</h3>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
              <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z" />
            </svg>
          </div>
          <input 
            v-model="search"
            type="text"
            placeholder="Buscar por nombre, área o subárea..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#15396F] focus:border-[#15396F] dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all text-sm outline-none"
          />
        </div>
      </div>

      <!-- Lista de revisores -->
      <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-3">Revisores disponibles</h3>
      
      <div v-if="paginatedReviewers.length > 0" class="space-y-3">
        <div 
          v-for="item in paginatedReviewers" 
          :key="item.id"
          class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 bg-white dark:bg-slate-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm hover:border-blue-300 transition-colors gap-4"
        >
          <!-- Info del revisor (Izquierda) -->
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-300 flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" />
              </svg>
            </div>
            <div>
              <p class="text-base font-bold text-gray-800 dark:text-gray-100">{{ item.name }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{ item.area || 'Sin área' }} <span v-if="item.subarea">- {{ item.subarea }}</span>
              </p>
              <p class="text-xs text-gray-400 mt-1">Artículos asignados: {{ item.assigned_count }}</p>
            </div>
          </div>

          <!-- Switch y estado (Derecha) -->
          <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
            <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
              Disponible
            </span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input 
                type="checkbox" 
                class="sr-only peer"
                :disabled="!getRole(['Editor', 'Admin', 'Admin-Revista', 'Super Admin'])"
                :checked="checkedReviewer(item)"
                @change="toggleReviewer(item)"
              />
              <!-- Estilo del Switch Verde -->
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
            </label>
          </div>
        </div>
      </div>
      <CardBoxComponentEmpty v-else message="No se encontraron revisores" />

      <!-- Paginación -->
      <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
        <span class="text-sm text-gray-500">Página {{ currentPage }} de {{ totalPages || 1 }}</span>
        <div class="flex gap-2">
          <button @click="prevPage" :disabled="currentPage === 1" class="px-4 py-1.5 text-sm font-medium rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 disabled:opacity-50 transition-colors">Anterior</button>
          <button @click="nextPage" :disabled="currentPage === totalPages || totalPages === 0" class="px-4 py-1.5 text-sm font-medium rounded-full bg-emerald-100 hover:bg-emerald-200 text-emerald-700 disabled:opacity-50 transition-colors">Siguiente</button>
        </div>
      </div>
    </div>

    <!-- Botones de acción (Fijos al fondo) -->
    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 bg-white dark:bg-slate-800">
      <BaseButton 
        color="white" 
        label="Cancelar" 
        @click="emit('close')" 
        class="border border-gray-300 font-semibold text-gray-700"
      />
      <BaseButton 
        :disabled="!getRole(['Editor', 'Admin', 'Admin-Revista', 'Super Admin'])"
        @click="saveAssign()"
        color="success" 
        label="Guardar asignación" 
        class="bg-[#10b981] hover:bg-[#059669] text-white font-semibold border-none"
      />
    </div>
  </div>
</template>
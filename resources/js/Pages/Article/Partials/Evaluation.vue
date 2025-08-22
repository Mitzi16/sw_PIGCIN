<script setup>
import {
    mdiPencil,
    mdiTrashCan,
    mdiContentSave,
    mdiClose
} from "@mdi/js";
import { ref, reactive, onMounted, inject } from "vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";
import BaseDivider from "@/Components/BaseDivider.vue";
import { useForm, usePage } from '@inertiajs/vue3';
import DropdownItem from "@/Components/DropdownItem.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import EvaluacionModal from '@/Components/EvaluacionModal.vue';
import ResultModal from '@/Components/ResultModal.vue';
import axios from 'axios';

const props = inject('props');
const user = usePage().props.auth.user;
const getRole = inject('getRole');

const showModal = ref(false);
const showResultsModal = ref(false);
const selectedReviewerId = ref(null);
const currentArticleId = ref(props.article.id);

// Estado reactivo para el artículo
const article = reactive({ ...props.article });

// Formulario del revisor
const filterArticleReview = article.article_reviews.find(ar => ar.reviewer_id === user.id);
const formReviewer = useForm({
    id: filterArticleReview?.id ?? null,
    comment: filterArticleReview?.comment ?? null,
    comment_for_editor: filterArticleReview?.comment_for_editor ?? null,
    reviewer_id: filterArticleReview?.reviewer_id ?? null,
    article_status_id: filterArticleReview?.article_status_id ?? null,
    article_id: article.id,
    criteria: filterArticleReview?.criteria?.map(c => c.id) ?? []
});

// Formulario del editor
const formEditor = useForm({
    id: article.id,
    article_status_id: article.article_status.is_evaluation ? article.article_status_id : null,
    comment: article.comment,
    comment_for_editor: article.comment_for_editor,
    article_id: article.id,
});

// Puntaje total
const totalScore = ref(null);

// Abrir modal de resultados
const openReviewerResults = (reviewerId) => {
    selectedReviewerId.value = reviewerId;
    showResultsModal.value = true;
};

// Guardar evaluación del revisor
const saveReviewer = async () => {
    try {
        await formReviewer.put(route(`articleReview.update`, filterArticleReview?.id));

        const { data } = await axios.get(`/article-evaluation/${article.id}`, {
            params: { reviewer_id: user.id }
        });

        totalScore.value = data.article_score;

        // Asegurar que cada review tenga article_score
        article.article_reviews = data.article_reviews.map(r => ({
            ...r,
            article_score: r.article_score ?? data.article_score
        }));

    } catch (error) {
        console.error(error);
    }
};

// Guardar evaluación del editor
const saveEditor = async () => {
    try {
        await formEditor.patch(route(`${props.routeName}evaluate`, article.id));

        article.article_status_id = formEditor.article_status_id;
        article.comment = formEditor.comment;
        article.comment_for_editor = formEditor.comment_for_editor;
    } catch (error) {
        console.error(error);
    }
};

// Funciones de criterios
const checkedCriterion = (criterion) => formReviewer.criteria.includes(criterion.id);
const toggleCriterion = (criterion) => {
    const index = formReviewer.criteria.indexOf(criterion.id);
    if (index === -1) formReviewer.criteria.push(criterion.id);
    else formReviewer.criteria.splice(index, 1);
};

// Filtrar estados de evaluación
const filterArticleStatuses = () => props.articleStatuses.filter(status => status.is_evaluation);

// Cargar puntaje total al montar
onMounted(async () => {
    try {
        const { data } = await axios.get(`/api/article-evaluation/${article.id}`, {
            params: { reviewer_id: user.id }
        });

        totalScore.value = data.article_score;

        // Cada review debe tener article_score
        article.article_reviews = data.article_reviews.map(r => ({
            ...r,
            article_score: r.article_score ?? data.article_score
        }));

    } catch (error) {
        console.error("Error al obtener el puntaje total", error);
    }
});
</script>



<template>
    <!-- FORM PARA EL REVISOR -->
    <div v-if="getRole(['Revisor'])">
        <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">
            Evaluación de criterios y comentarios
        </h2>
        <p class="text-slate-500 text-sm mb-2">
            Los comentarios y criterios se veran reflejados al editor.
        </p>
        <div class="lg:mb-5 border-slate-500">
            <FormField required label="Evalua los criterios:" :error="formReviewer.errors.criteria">
                <div class="mt-4">
                    <button @click="showModal = true" class="bg-green-500 text-white px-4 py-2 rounded">
                        Evaluar artículo
                    </button>
                </div>

                <EvaluacionModal :showModal="showModal" :articleTypeId="props.article.article_type_id"
                    :articleId="props.article.id" @close="showModal = false" />
            </FormField>
            <FormField label="Puntuación total:">
                <p class="text-gray-700 dark:text-gray-300">
                    {{ totalScore !== null ? totalScore : 'Sin evaluar aún' }}
                </p>
            </FormField>

        </div>
        <BaseDivider />
        <FormField required label="Selecciona un resultado:" :error="formReviewer.errors.article_status_id">
            <FormControl v-model="formReviewer.article_status_id" :options="filterArticleStatuses()" />
        </FormField>
        <FormField required label="Comentarios Del Revisor:" :error="formReviewer.errors.comment">
            <FormControl type="textarea" height="h-24" v-model="formReviewer.comment"
                placeholder="Escribe tus comentarios..." />
        </FormField>
        <FormField required label="Comentarios Para El Editor:" :error="formReviewer.errors.comment_for_editor">
            <FormControl type="textarea" height="h-24" v-model="formReviewer.comment_for_editor"
                placeholder="Escribe tus comentarios..." />
        </FormField>
        <BaseButtons class="mt-5">
            <BaseButton :disabled="props.statusReviewer" @click="saveReviewer" :icon="mdiContentSave" color="success"
                label="Guardar" small />
        </BaseButtons>
    </div>

    <!-- FORM PARA EL EDITOR -->
    <div v-else-if="getRole('Editor') || getRole('Admin') || getRole('Admin-Revista')">
        <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">
            Evaluación final
        </h2>
        <p class="text-slate-500 text-sm mb-2">
            Los comentarios y resultado final se vera reflejado en el estatus del postulante.
        </p>

        <FormField required label="Selecciona un resultado:" :error="formEditor.errors.article_status_id">
            <FormControl v-model="formEditor.article_status_id" :options="filterArticleStatuses()" />
        </FormField>
        <FormField required label="Comentarios:" :error="formEditor.errors.comment">
            <FormControl type="textarea" height="h-24" v-model="formEditor.comment"
                placeholder="Escribe tus comentarios..." />
        </FormField>
        <BaseButtons class="mt-5">
            <BaseButton :disabled="props.article.article_status_id === 4" @click="saveEditor" :icon="mdiContentSave"
                color="success" label="Guardar" small />
        </BaseButtons>

        <BaseDivider />

        <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">
            Evaluación de los revisores
        </h2>
        <div v-if="props.article.article_reviews.length > 0" class="lg:mb-5">
            <ul role="list" class="max-w-sm">
<li v-for="item in article.article_reviews" :key="item.id">
                    <DropdownItem :value="false" class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                        @click="openReviewerResults(item.reviewer_id)">
                        <template #header>
                            <div class="flex items-center justify-between w-full space-x-2">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <img class="w-8 h-8 rounded-full"
                                            :src="item.reviewer?.file?.path ?? '/img/user.jpg'" alt="Neil image">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate dark:text-white">
                                            {{ item?.reviewer?.name }}
                                        </p>
                                        <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                            {{ item?.reviewer?.email }}
                                        </p>
                                    </div>
                                </div>
                                <span :class="item.article_status?.class" class="text-xs">
                                    {{ item.article_status?.name }}
                                </span>
                            </div>
                        </template>
                        <p class="text-center text-xs text-slate-700 dark:text-slate-300">Información de evaluación</p>
                       <p class="text-center text-sm font-semibold text-gray-800 dark:text-white">
{{ item.article_score ?? totalScore ?? 'Sin evaluar' }}
</p>





                        <p class="text-center text-xs text-slate-700 dark:text-slate-300 my-2">Comentarios</p>
                        <p class="text-center text-xs text-slate-700 dark:text-slate-300 my-2">Comentarios Del Revisor
                        </p>
                        <FormControl disabled type="textarea" height="h-24" v-model="item.comment"
                            placeholder="Sin comentarios por parte del revisor..." />
                        <p class="text-center text-xs text-slate-700 dark:text-slate-300 my-2">Comentarios Para El
                            Editor</p>
                        <FormControl disabled type="textarea" height="h-24" v-model="item.comment_for_editor"
                            placeholder="Sin comentarios para el editor..." />

                    </DropdownItem>
                </li>
            </ul>
        </div>
        <CardBoxComponentEmpty v-else />
    </div>

    <!-- Modal de Resultados (fuera del bucle) -->
    <ResultModal :showModal="showResultsModal" :article_id="currentArticleId" :reviewerId="selectedReviewerId"
        @close="showResultsModal = false" />
</template>
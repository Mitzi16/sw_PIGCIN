<script setup>
import {
    mdiPencil,
    mdiTrashCan,
    mdiContentSave,
    mdiClose
} from "@mdi/js";
import { ref, reactive, onMounted, inject, computed } from "vue";
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
const article = reactive({ ...props.article });


const filterArticleReview = computed(() => 
    article.article_reviews.find(ar => ar.reviewer_id === user.id)
);

const isUserReviewerOfThisArticle = computed(() => !!filterArticleReview.value);
const isUserEditorOfThisArticle = computed(() => article.editor_id === user.id);
const isUserPostulantOfThisArticle = computed(() => article.postulant_id === user.id);

const formReviewer = useForm({
    id: filterArticleReview.value?.id ?? null,
    comment: filterArticleReview.value?.comment ?? null,
    comment_for_editor: filterArticleReview.value?.comment_for_editor ?? null,
    reviewer_id: filterArticleReview.value?.reviewer_id ?? null,
    article_status_id: filterArticleReview.value?.article_status_id ?? null,
    article_id: article.id,
    criteria: filterArticleReview.value?.criteria?.map(c => c.id) ?? []
});

// Formulario del EDITOR: Se inicializa con los datos generales del artículo.
const formEditor = useForm({
    id: article.id,
    article_status_id: article.article_status.is_evaluation ? article.article_status_id : null,
    comment: article.comment,
    comment_for_editor: article.comment_for_editor,
    article_id: article.id,
});

const totalScore = ref(filterArticleReview.value?.article_score ?? null);

const isModalEvaluationCompleted = ref(false);


const updateScoreInRealTime = async () => {
    try {
        const params = getRole(user) === 'revisor'
            ? { reviewer_id: user.id }
            : {};
        const { data } = await axios.get(`/api/article-evaluation/${article.id}`, { params });
        totalScore.value = data.article_score ?? null;
        article.article_reviews = article.article_reviews.map(r => ({
            ...r,
            article_score: r.article_score ?? data.article_score
        }));
    } catch (error) {
        console.error('Error actualizando el puntaje:', error);
    }
};

const handleEvaluationSaved = async (evaluationData) => {
    totalScore.value = evaluationData.totalScore;
    const review = article.article_reviews.find(ar => ar.reviewer_id === user.id); 
    if (review) {
        review.article_score = evaluationData.totalScore;
    } 
    // Habilita el botón de guardar principal
    isModalEvaluationCompleted.value = true;
};

const openReviewerResults = (reviewerId) => {
    selectedReviewerId.value = reviewerId;
    showResultsModal.value = true;
};

const saveReviewer = async () => {
    // Si el modal no se ha completado (ya sea al cargar o al guardar), muestra error
    if (!isModalEvaluationCompleted.value) {
        formReviewer.setError('criteria', 'Debes completar la evaluación de criterios (abrir el modal y guardar) antes de guardar.');
        return;
    }
    
    try {
        await formReviewer.put(route(`articleReview.update`, filterArticleReview.value?.id));
        const review = article.article_reviews.find(ar => ar.reviewer_id === user.id);
        if (review) {
            review.article_status_id = formReviewer.article_status_id;
            review.article_status = props.articleStatuses.find(s => s.id === formReviewer.article_status_id);
            review.comment = formReviewer.comment;
            review.comment_for_editor = formReviewer.comment_for_editor;
            review.article_score = totalScore.value;
        }
    } catch (error) {
        console.error('Error guardando evaluación del revisor:', error);
    }
};

const saveEditor = async () => {
    try {
        await formEditor.patch(route(`${props.routeName}evaluate`, article.id));
        article.article_status_id = formEditor.article_status_id;
        article.comment = formEditor.comment;
        article.comment_for_editor = formEditor.comment_for_editor;
    } catch (error) {
        console.error('Error guardando evaluación del editor:', error);
    }
};

const loadReviewerScore = async () => {
    if (!isUserReviewerOfThisArticle.value) return; 
    
    try {
        const { data } = await axios.get(`/api/article-evaluation/${article.id}`, {
            params: { reviewer_id: user.id } 
        });

        if (data.evaluations && data.evaluations.length > 0) {
            // El usuario YA tiene evaluaciones guardadas (el puntaje puede ser 0 o más).
            isModalEvaluationCompleted.value = true;
            // Mostramos el puntaje (que podría ser 0)
            totalScore.value = data.article_score; 
        } else {
            // No hay evaluaciones. Es una evaluación nueva.
            isModalEvaluationCompleted.value = false;
            // Forzamos el puntaje a 'null' para que muestre "Sin evaluar aún"
            totalScore.value = null; 
        }

    } catch (error) {
        console.error('Error cargando el puntaje del revisor:', error);
        totalScore.value = null;
        isModalEvaluationCompleted.value = false; // Asegurarse de que esté bloqueado en caso de error
    }
};

const checkedCriterion = (criterion) => formReviewer.criteria.includes(criterion.id);
const toggleCriterion = (criterion) => {
    const index = formReviewer.criteria.indexOf(criterion.id);
    if (index === -1) formReviewer.criteria.push(criterion.id);
    else formReviewer.criteria.splice(index, 1);
};

const filterArticleStatuses = () => props.articleStatuses.filter(status => status.is_evaluation);

onMounted(async () => {
  // 1. Si el usuario actual es un REVISOR de este artículo
    if (isUserReviewerOfThisArticle.value) {
        // Cargar SU puntaje específico usando la lógica de 'evaluations'
        await loadReviewerScore();
    
    } else if (getRole(['Admin', 'Admin-Revista']) || isUserEditorOfThisArticle.value) {
        
        try {
            const scorePromises = article.article_reviews.map(review => {
                // Llamamos a la API *para cada* revisor de la lista
                return axios.get(`/api/article-evaluation/${article.id}`, {
                    params: { reviewer_id: review.reviewer_id } 
                });
            });

            // Esperamos que todas las llamadas a la API terminen
            const responses = await Promise.all(scorePromises);
            
            const cleanedReviews = article.article_reviews.map((review, index) => {
                
                const { data } = responses[index]; 
                
                const correctScore = (data.evaluations && data.evaluations.length > 0)
                    ? data.article_score // Puntaje real (e.g., 50, o 0)
                    : null;               // Aún no evalúa, forzar null
                    
                
                return {
                    ...review,
                    article_score: correctScore
                };
            });
            
           
            article.article_reviews = cleanedReviews;

        } catch (error) {
            console.error('Error al limpiar los puntajes de los revisores:', error);
           
        }
    }
  
});
</script>

<template>
<!-- Evaluación del artículo -->
    <div v-if="isUserReviewerOfThisArticle">
        <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">
            Evaluación de criterios y comentarios
        </h2>
        <p class="text-slate-500 text-sm mb-2">
            Los comentarios y criterios se verán reflejados al editor.
        </p>
        <div class="lg:mb-5 border-slate-500">
            <FormField required label="Evalua los criterios:" :error="formReviewer.errors.criteria">
                <div class="mt-4">
                    <button @click="showModal = true" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors">
                        Evaluar artículo
                    </button>
                    <p v-if="!isModalEvaluationCompleted" class="text-sm text-gray-500 dark:text-gray-400 mt-2 italic">
                        Debes evaluar los criterios para habilitar el botón "Guardar".
                    </p>
                </div>
                <EvaluacionModal
                    :showModal="showModal" 
                    :articleTypeId="props.article.article_type_id"
                    :articleId="props.article.id" 
                    @close="showModal = false"
                    :reviewerId="user.id" 
                    @evaluationSaved="handleEvaluationSaved"
                />
            </FormField>
            <FormField label="Puntuación total:">
                <div class="flex items-center space-x-2">
                    <p class="text-gray-700 dark:text-gray-300 text-lg font-semibold">
                        
                        {{ totalScore !== null ? totalScore : 'Sin evaluar aún' }}
                        <span v-if="totalScore !== null" class="text-sm font-normal text-gray-500">pts</span>
                    </p>
                    <div v-if="totalScore !== null" class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                </div>
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
            <BaseButton 
                :disabled="props.statusReviewer || !isModalEvaluationCompleted" 
                @click="saveReviewer" 
                :icon="mdiContentSave" 
                color="success"
                label="Guardar" 
                small 
            />
        </BaseButtons> 
    </div>
    
    <!-- Evaluación final del editor -->
    <div v-else-if="getRole(['Admin', 'Admin-Revista']) || isUserEditorOfThisArticle">
        <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">
            Evaluación final
        </h2>
        <p class="text-slate-500 text-sm mb-2">
            Los comentarios y resultado final se verán reflejados en el estatus del postulante.
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
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                {{ item.article_score !== null ? item.article_score : 'Sin evaluar aún' }} pts
                            </p>
                            <div v-if="item.article_score !== null" class="w-2 h-2 bg-green-500 rounded-full mx-auto mt-1"></div>
                        </div>
                        <p class="text-center text-xs text-slate-700 dark:text-slate-300 my-2">Comentarios</p>
                        <p class="text-center text-xs text-slate-700 dark:text-slate-300 my-2">Comentarios Del Revisor</p>
                        <FormControl disabled type="textarea" height="h-24" v-model="item.comment"
                            placeholder="Sin comentarios por parte del revisor..." />
                        <p class="text-center text-xs text-slate-700 dark:text-slate-300 my-2">Comentarios Para El Editor</p>
                        <FormControl disabled type="textarea" height="h-24" v-model="item.comment_for_editor"
                            placeholder="Sin comentarios para el editor..." />
                    </DropdownItem>
                </li>
            </ul>
        </div>
        <CardBoxComponentEmpty v-else />
    </div>
    
    <div v-else-if="isUserPostulantOfThisArticle">
        <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">
            Estado de tu Artículo
        </h2>
        <p class="text-slate-500 text-sm mb-2">
            Actualmente tu artículo se encuentra en revisión. Aquí podrás ver el estado y los comentarios finales una vez que el proceso haya concluido.
        </p>
    </div>

    <div v-else>
        <CardBoxComponentEmpty message="No tienes permisos de edición para este artículo." />
    </div>

    <ResultModal :showModal="showResultsModal" :article_id="currentArticleId" :reviewerId="selectedReviewerId"
        @close="showResultsModal = false" />
</template>
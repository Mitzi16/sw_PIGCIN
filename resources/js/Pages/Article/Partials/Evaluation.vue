<script setup>
import {
    mdiPencil,
    mdiTrashCan,
    mdiContentSave,
    mdiClose
} from "@mdi/js";
import { computed, watch, ref, onMounted, inject } from "vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";
import BaseDivider from "@/Components/BaseDivider.vue";
import { Link, useForm, usePage, Head } from '@inertiajs/vue3';
import DropdownItem from "@/Components/DropdownItem.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";

const props = inject('props');
const user = usePage().props.auth.user;
const getRole = inject('getRole');

const filterArticleReview = props.article.article_reviews.find(articleReview => articleReview.reviewer_id === user.id)

const formReviewer = useForm({
    id: filterArticleReview?.id ?? null,
    comment: filterArticleReview?.comment ?? null,
    reviewer_id: filterArticleReview?.reviewer_id ?? null,
    article_status_id: filterArticleReview?.article_status_id ?? null,
    article_id: props.article.id,
    criteria: filterArticleReview?.criteria?.map(criterion => criterion.id)
})

const formEditor = useForm({
    id: props.article.id,
    article_status_id: props.article.article_status.is_evaluation ? props.article.article_status_id : null,
    comment: props.article.comment,
})

const saveEditor = () => {
    formEditor.patch(route(`${props.routeName}evaluate`, props.article.id));
}

const saveReviewer = () => {
    formReviewer.put(route(`articleReview.update`, filterArticleReview?.id));
}

const checkedCriterion = (criterion) => {
    return formReviewer.criteria.includes(criterion.id);
};

const toggleCriterion = (criterion) => {
    const index = formReviewer.criteria.indexOf(criterion.id);
    if (index === -1) {
        formReviewer.criteria.push(criterion.id);
    } else {
        formReviewer.criteria.splice(index, 1);
    }
};

const filterArticleStatuses = () => {
    return props.articleStatuses.filter(status => status.is_evaluation == true)
}

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
        <div class="lg:mb-5">
            <FormField required label="Evalua los criterios:" :error="formReviewer.errors.criteria">
                <div class="border rounded border-gray-400 overflow-y-auto max-h-96">
                    <ul class="px-5">
                        <li v-for="item in props.criteria" :key="item.id">
                            <label
                                class="p-2 justify-between flex items-center cursor-pointer border-b border-slate-200">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">
                                    {{ item.name }}
                                </span>
                                <input type="checkbox" class="sr-only peer" :value="{ id: item.id }"
                                    :id="'criterion_' + item.id" :checked="checkedCriterion(item)"
                                    @change="toggleCriterion(item)">
                                <div
                                    class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </li>
                    </ul>
                </div>
            </FormField>
        </div>
        <BaseDivider />
        <FormField required label="Selecciona un resultado:" :error="formReviewer.errors.article_status_id">
            <FormControl v-model="formReviewer.article_status_id" :options="filterArticleStatuses()" />
        </FormField>
        <FormField required label="Comentarios:" :error="formReviewer.errors.comment">
            <FormControl type="textarea" height="h-24" v-model="formReviewer.comment"
                placeholder="Escribe tus comentarios..." />
        </FormField>
        <BaseButtons class="mt-5">
            <BaseButton :disabled="props.statusReviewer" @click="saveReviewer" :icon="mdiContentSave" color="success" label="Guardar" small />
        </BaseButtons>
    </div>
    <!-- FORM PARA EL EDITOR -->
    <div v-else-if="getRole(['Editor', 'Admin'])">
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
            <BaseButton :disabled="props.article.article_status_id === 4" @click="saveEditor" :icon="mdiContentSave" color="success" label="Guardar" small />
        </BaseButtons>

        <BaseDivider />

        <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">
            Evaluación de los revisores
        </h2>
        <div v-if="props.article.article_reviews.length > 0" class="lg:mb-5">
            <ul role="list" class="max-w-sm">
                <li v-for="item in props.article.article_reviews" :key="item.id" class="">
                    <DropdownItem :value="false" class="">
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

                        <p class="text-center text-xs text-slate-700 dark:text-slate-300">Criterios</p>
                        <div v-if="item.criteria.length > 0" class="overflow-y-auto max-h-96">
                            <ul class="">
                                <li v-for="evaluation in props.criteria" :key="evaluation.id">
                                    <label
                                    
                                        class="p-2 px-5 justify-between flex items-center border-b border-slate-200 dark:border-slate-600">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-300">
                                            {{ evaluation.name }}
                                        </span>
                                        <span v-if="item?.criteria?.find(criterion => criterion.id === evaluation.id)"
                                            class="flex items-center justify-center w-6 h-6 bg-green-200 rounded-full -start-4 ring-1 ring-white dark:ring-gray-900 dark:bg-green-900">
                                            <svg class="w-3 h-3 text-green-500 dark:text-green-400" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2"
                                                    d="M1 5.917 5.724 10.5 15 1.5" />
                                            </svg>
                                        </span>
                                        <span v-else
                                            class="flex items-center justify-center w-6 h-6 bg-red-100 rounded-full -start-4 ring-1 ring-white dark:ring-red-900 dark:bg-red-900">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-x w-4 h-4 text-red-500 dark:text-red-400"
                                                width="52" height="52" viewBox="0 0 24 24" stroke-width="1.5"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M18 6l-12 12" />
                                                <path d="M6 6l12 12" />
                                            </svg>
                                        </span>

                                    </label>
                                </li>
                            </ul>
                        </div>
                        <CardBoxComponentEmpty v-else />

                        <p class="text-center text-xs text-slate-700 dark:text-slate-300 my-2">Comentarios</p>
                        <FormControl disabled type="textarea" height="h-24" v-model="item.comment"
                            placeholder="Sin comentarios..." />
                    </DropdownItem>
                </li>
            </ul>
        </div>
        <CardBoxComponentEmpty v-else />
    </div>
</template>
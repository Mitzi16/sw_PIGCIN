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
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import { Link, useForm, usePage, Head } from '@inertiajs/vue3';

const props = inject('props');
const getRole = inject('getRole');

const form = useForm({
    article_id: props.article.id,
    editor_id: props.article.editor_id,
    article_status_id: props.article.article_status_id,
    reviewers: props.article.article_reviews.map(
        (reviewer) => reviewer.reviewer_id
    ),
})

const saveAssign = () => {
    form.post(route('articleReview.store'));
}

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

</script>

<template>
    <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">
        Asignar editor y revisores
    </h2>
    <div class="mt-5">
        <div class="lg:mb-5">
            <FormField required label="Selecciona un editor:" :error="form.errors.editor_id">
                <FormControl :disabled="!getRole(['Admin'])" v-model="form.editor_id"
                    :options="props.editors" />
            </FormField>
        </div>

        <div class="lg:mb-5">
            <FormField required label="Selecciona uno o más revisores:" :error="form.errors.reviewers">
                <div v-if="props.reviewers.length > 0" class="border rounded border-gray-400 overflow-y-auto max-h-96">
                    <ul class="">
                        <li v-for="item in props.reviewers" :key="item.id">
                            <label class="p-2 justify-between flex items-center border-b border-slate-200 px-3"
                                :class="{ 'cursor-not-allowed': !getRole(['Editor', 'Admin']), 'cursor-pointer': getRole(['Editor', 'Admin']) }">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">
                                    {{ item.name }}
                                </span>
                                <input :disabled="!getRole(['Editor', 'Admin'])" type="checkbox" class="sr-only peer"
                                    :value="{ id: item.id }" :id="'reviewer_' + item.id"
                                    :checked="checkedReviewer(item)" @change="toggleReviewer(item)">
                                <div
                                    class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </li>
                    </ul>
                </div>
                <CardBoxComponentEmpty v-else />
            </FormField>
        </div>
        <BaseButtons class="mt-5">
            <BaseButton :disabled="!getRole(['Editor', 'Admin'])" @click="saveAssign()" :icon="mdiContentSave" color="success" label="Asignar" small />
        </BaseButtons>
    </div>
</template>
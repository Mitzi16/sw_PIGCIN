<script setup>
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import { computed, watch, ref, onMounted, inject } from "vue";
import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";

const props = inject('props');

const dateToLocal = (date) => {
    if (!date) return "";
    const dateUtc = new Date(date);
    const options = {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    };
    const formattedDate = dateUtc.toLocaleDateString("es-MX", options);
    const [year, month, day, hour, minute] = formattedDate.split(/[/,\s-]/);
    return `${day}-${month}-${year} ${hour}:${minute}`;
    // return `${day}-${month}-${year}`;
};
</script>

<template>
    <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">Pasos del proceso</h2>
    <div class="mt-5 px-5">
        <ol class="relative text-gray-500 border-s border-gray-200 dark:border-gray-700 dark:text-gray-400">
            <li v-for="item in props.articleStatuses" :key="item.id" class="mb-10 ms-6">
                <span v-if="props.article.article_status_id === item.id"
                    class="absolute flex items-center justify-center w-8 h-8 bg-green-200 rounded-full -start-4 ring-4 ring-white dark:ring-gray-900 dark:bg-green-900">
                    <svg class="w-3.5 h-3.5 text-green-500 dark:text-green-400" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 5.917 5.724 10.5 15 1.5" />
                    </svg>
                </span>
                <span v-else
                    class="absolute flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full -start-4 ring-4 ring-white dark:ring-gray-900 dark:bg-gray-700">
                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
                        <path
                            d="M18 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2ZM6.5 3a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM3.014 13.021l.157-.625A3.427 3.427 0 0 1 6.5 9.571a3.426 3.426 0 0 1 3.322 2.805l.159.622-6.967.023ZM16 12h-3a1 1 0 0 1 0-2h3a1 1 0 0 1 0 2Zm0-3h-3a1 1 0 1 1 0-2h3a1 1 0 1 1 0 2Zm0-3h-3a1 1 0 1 1 0-2h3a1 1 0 1 1 0 2Z" />
                    </svg>
                </span>
                <div class="justify-between flex space-x-1">
                    <h3 class="font-medium text-black dark:text-white leading-tight">
                        {{ item.name }}
                    </h3>

                </div>
                <span v-if="props.article.article_status_id === item.id" class="text-xs">
                    Ultima fecha: {{ dateToLocal(props.article.updated_at) }}
                </span>
                <p class="text-sm">{{ item.description }}</p>
            </li>
        </ol>
    </div>

    <h2 class="text-xl font-medium text-gray-700 dark:text-white py-2">
        Comentarios del editor
    </h2>

    <ul v-if="props.article.article_status.is_evaluation" role="list"
        class="max-w-sm divide-y divide-gray-200 dark:divide-gray-700">
        <li class="py-3 sm:py-4">
            <div class="flex items-center space-x-3 rtl:space-x-reverse mb-2">
                <div class="flex-shrink-0">
                    <img class="w-8 h-8 rounded-full" :src="props.article?.editor?.file?.path ?? '/img/user.jpg'" alt="Neil image">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate dark:text-white">
                        {{ props.article?.editor?.name }}
                    </p>
                    <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                        {{ props.article?.editor?.email }}
                    </p>
                </div>
            </div>
            <FormControl disabled type="textarea" height="h-24" v-model="props.article.comment"
                placeholder="Sin comentarios..." />
        </li>
    </ul>
    <CardBoxComponentEmpty v-else />
</template>
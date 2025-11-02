<script setup>
import { ref, computed, watch } from "vue"; 
import { router } from "@inertiajs/vue3";
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import debounce from "lodash.debounce";
import {
    mdiBallotOutline,
    mdiInformation,
    mdiPencil,
    mdiBroom,
    mdiMagnify,
    mdiPlus,
    mdiCheckDecagram,
    mdiReceiptText, 
} from "@mdi/js";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import HeadLogo from "@/Components/HeadLogo.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseIcon from "@/Components/BaseIcon.vue"; 
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    name: "Constancias",
    title: { type: String, required: true },
    articles: { 
        type: Object, 
        required: true 
    },
    filters: {
        type: Object, 
        default: () => ({}),
    },
});

const searchQuery = ref(props.filters.search || "");

const statusMap = {
    3: "Aceptado con observaciones",
    4: "Aceptado",
};

const filteredArticles = computed(() => {
    return props.articles.data || [];
});

watch(
    searchQuery,
    debounce((value) => {
        router.get(
            "/constancias/article", 
            { search: value }, 
            {
                preserveState: true, 
                replace: true,        
                preserveScroll: true, 
            }
        );
    }, 300) // 300ms de espera
);
</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <SectionTitleLineWithButton
            :icon="mdiBallotOutline"
            :title="title"
            main
        />

        <NotificationBar
            v-if="$page.props.flash.success"
            color="success"
            :icon="mdiInformation"
            :outline="false"
        >
            {{ $page.props.flash.success }}
        </NotificationBar>

        <NotificationBar
            v-if="$page.props.flash.error"
            color="danger"
            :icon="mdiInformation"
            :outline="false"
        >
            {{ $page.props.flash.error }}
        </NotificationBar>

        <CardBox class="mb-6">
            <div class="relative mb-4"> 
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por título de artículo..."
                    class="w-full py-2 pl-10 pr-4 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:placeholder-gray-400 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <div
                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                >
                    <BaseIcon
                        :path="mdiMagnify"
                        class="w-5 h-5 text-gray-500 dark:text-gray-400" 
                    />
                </div>
            </div>

            <div
                class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm"
            >
                <table class="min-w-full bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-700"> <tr>
                            <th
                                scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-600 dark:text-gray-300 uppercase"
                            >
                                Título del Artículo
                            </th>
                             <th
                                scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-600 dark:text-gray-300 uppercase"
                            >
                                Folio
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-600 dark:text-gray-300 uppercase"
                            >
                                Autor(es)
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-600 dark:text-gray-300 uppercase"
                            >
                                Estatus
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-600 dark:text-gray-300 uppercase"
                            >
                                Acción
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="filteredArticles.length === 0">
                            <td
                                colspan="4"
                                class="px-6 py-4 text-center text-gray-500 dark:text-gray-400 whitespace-nowrap"
                            >
                                No se encontraron artículos con los estatus
                                "Aceptado" o "Aceptado con observaciones".
                            </td>
                        </tr>

                        <tr
                            v-for="article in filteredArticles"
                            :key="article.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <td
                                class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                            >
                                {{ article.title }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                            >
                                {{ article.article_folio }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap"
                            >
                                {{ article.author_name || "No especificado" }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        article.status_id === 3
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                    ]"
                                >
                                    {{ statusMap[article.status_id] }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
                            >
                                <a
                                    :href="`/articles/${article.id}/certificate`"
                                    target="_blank"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
                                >
                                    <BaseIcon
                                        :path="mdiReceiptText"
                                        class="w-4 h-4 mr-2"
                                    />
                                    Descargar
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination
                v-if="articles.data && articles.data.length > 0"
                :links="articles.links"
                class="mt-6"
            />
        </CardBox>
    </LayoutMain>
</template>
<script setup>
import LayoutMain from '@/Layouts/LayoutMain.vue'
import HeadLogo from '@/Components/HeadLogo.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import { mdiBallotOutline, mdiInformation, mdiPencil, mdiBroom, mdiMagnify, mdiPlus, mdiReceiptText } from '@mdi/js'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import NotificationBar from '@/Components/NotificationBar.vue'
import CardBox from '@/Components/CardBox.vue'
import CardBoxComponentEmpty from '@/Components/CardBoxComponentEmpty.vue'
import Pagination from '@/Shared/Pagination.vue'
import Dropdown from '@/Components/DropdownTable.vue'
import { router, Head } from '@inertiajs/vue3'
import { ref, reactive, provide } from 'vue'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();

// Roles permitidos para exportar
const allowedRoles = ["Admin", "Editor", "Revisor"];

const canExport = computed(() => {
  const roles = page.props.auth?.user?.roles ?? [];
  return roles.some((role) => allowedRoles.includes(role.name));
});


const props = defineProps({
    name: 'Index',
    title: String,
    routeName: String,
    articleTypes: Object,
    search: String,
    direction: String,
})

const search = ref(props.search)
const isLoading = ref(false)

const state = reactive({
    filters: {
        search: search,
        order: 'created_at',
        direction: props.direction,
    },
})
const exportExcel = () => {
  window.open(route('articleType.export'));
};

const filterSearch = () => {
    router.get(route(`${props.routeName}index`, state.filters), { preserveState: true })
}

const cleanFilters = () => {
    isLoading.value = true
    router.get(route(`${props.routeName}index`))
}

const filterBy = (order, direction) => {
    state.filters.order = order
    state.filters.direction = direction
    isLoading.value = true
    router.get(route(`${props.routeName}index`, state.filters))
}

const opts = [
    {
        label: 'Nombre',
        key: 'name',
        menu: [
            { title: 'A - Z', direction: 'asc' },
            { title: 'Z - A', direction: 'desc' },
        ],
    },
]

provide('filterBy', filterBy)
</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <SectionTitleLineWithButton :icon="mdiBallotOutline" :title="title" main />

        <NotificationBar v-if="$page.props.flash.success" color="success" :icon="mdiInformation">
            {{ $page.props.flash.success }}
        </NotificationBar>

        <form @submit.prevent="filterSearch" class="w-full mb-5">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="flex w-full md:w-1/2 my-4">
                    <input type="search" v-model="search"
                        class="block pr-10 md:h-11 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-l-lg md:border-l-gray-300 border-l-gray-300 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-l-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:border-blue-500"
                        placeholder="Buscar tipo de artículo..." />
                    <BaseButton class="h-11 rounded-none" @click="filterSearch" :icon="mdiMagnify" color="info" />
                    <BaseButton class="h-11 rounded-l-none" @click="cleanFilters" :icon="mdiBroom" color="lightDark" />
                </div>

                <BaseButtons class="flex space-x-2">
                    <BaseButton :routeName="`${routeName}create`" color="info" :icon="mdiPlus" label="Nuevo tipo" 
                    class="flex-1 h-11 rounded-l-lg"
                    />
                    <BaseButton :icon="mdiReceiptText" color="success" label="Exportar a Excel" @click="exportExcel"
                    class="flex-1 h-11 rounded-r-lg" v-if="canExport"/>
                </BaseButtons>
            </div>
        </form>

        <CardBox v-if="articleTypes.data.length > 0">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>
                            <Dropdown title="Nombre" :options="opts" />
                        </th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in articleTypes.data" :key="item.id">
                        <td>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-book-half" viewBox="0 0 16 16">
                                <path
                                    d="M8.5 2.687c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z" />
                            </svg>
                        </td>
                        <td>{{ item.name }}</td>
                        <td>
                            <BaseButtons>
                                <BaseButton :routeName="`${routeName}edit`" :parameter="item.id" :icon="mdiPencil"
                                    color="info" small />
                            </BaseButtons>
                        </td>
                    </tr>
                </tbody>
            </table>
        </CardBox>

        <CardBoxComponentEmpty v-else />
        <Pagination :links="articleTypes.links" :total="articleTypes.total" :to="articleTypes.to"
            :from="articleTypes.from" />
        <div class="vl-parent">
            <Loading v-model:active="isLoading" :can-cancel="false" :is-full-page="true" />
        </div>
    </LayoutMain>
</template>

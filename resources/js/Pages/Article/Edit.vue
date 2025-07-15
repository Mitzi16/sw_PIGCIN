<script setup>
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import {
    mdiPencil,
    mdiTrashCan,
    mdiContentSave,
    mdiClose,
    mdiInformation
} from "@mdi/js";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import { computed, watch, ref, onMounted } from "vue";
import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";
import { Tabs, Tab } from "flowbite-vue";
import { provide } from "vue";
import Article from "./Partials/Article.vue";
import Status from "./Partials/Status.vue";
import Assigns from "./Partials/Assigns.vue";
import Evaluation from "./Partials/Evaluation.vue";
import { Link, useForm, usePage, Head } from '@inertiajs/vue3';
import NotificationBar from "@/Components/NotificationBar.vue";
import HeadLogo from "@/Components/HeadLogo.vue";

const props = defineProps({
    name: 'Edit',
    title: {
        type: String,
        required: true
    },
    routeName: {
        type: String,
        required: true
    },
    article: { type: Object, required: true },
    filePath: { type: String, required: true },
    articleStatuses: { type: Object, required: true },
    user: { type: Object, required: true },
    areas: { type: Object, default: {}, required: true },
    institutions: { type: Object, default: {}, required: true },
});
const activeTab = ref('status')

const form = useForm({
    _method: 'patch',
    id: props.article.id,
    title: props.article.title,
    type: props.article.type,
    abstract: props.article.abstract,
    key_works: props.article.key_works,
    knowledge_area_id: props.article?.knowledge_area?.id,
    article_status_id: props.article.article_status_id,
    call: props.article?.call?.title,
    file: null,

    authors: props.article.authors,
    selectedArea: props.article?.knowledge_area?.parent_id,
});

const saveForm = () => {
    form.post(route(`${props.routeName}update`, props.article.id));
};

const getRole = (roleNames) => {
    if (!Array.isArray(roleNames)) {
        roleNames = [roleNames];
    }
    return Array.isArray(props.user.roles) && props.user.roles.some(role => roleNames.includes(role.name));
}

provide('form', form);
provide('props', props);
provide('getRole', getRole);

</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <SectionTitleLineWithButton :icon="mdiPencil" :title="title" main>
            <Link :href="route(`${routeName}index`)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x"
                viewBox="0 0 16 16">
                <path
                    d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
            </svg>
            </Link>
        </SectionTitleLineWithButton>
        <NotificationBar v-if="$page.props.flash.success" color="success" :icon="mdiInformation" :outline="false">
            {{ $page.props.flash.success }}
        </NotificationBar>

        <NotificationBar v-if="$page.props.flash.error" color="danger" :icon="mdiInformation" :outline="false">
            {{ $page.props.flash.error }}
        </NotificationBar>
        <div class="md:grid md:grid-cols-5 gap-4">
            <CardBox isForm @submit.prevent="saveForm" class="col-span-3">
                <Article />

                <BaseButtons class="mt-5">
                    <BaseButton :routeName="`${props.routeName}index`" :icon="mdiClose" color="white"
                        label="Cancelar" />
                    <BaseButton @click="saveForm" :icon="mdiContentSave" type="submit" color="success" label="Actualizar artículo" />
                </BaseButtons>
            </CardBox>
            <div class="col-span-2 h-full lg:relative">
                <CardBox class="lg:sticky lg:top-14 lg:overflow-y-auto">
                    <Tabs v-model="activeTab" variant="underline" class="p-5">
                        <Tab name="status" title="Estatus" :disabled="false">
                            <Status />
                        </Tab>
                    </Tabs>
                </CardBox>
            </div>
        </div>
    </LayoutMain>
</template>

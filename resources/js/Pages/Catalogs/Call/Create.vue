<script setup>
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import { mdiContentSave, mdiClose, mdiPlus } from "@mdi/js";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { Link, Head, useForm } from "@inertiajs/vue3";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import Select from '@/Components/Select.vue';
import { provide, computed, ref } from "vue";
import InputFile from "./Partials/InputFile.vue";
import InputImage from "./Partials/InputImage.vue";
import Iframe from './Partials/Iframe.vue';
import axios from "axios";
import HeadLogo from "@/Components/HeadLogo.vue";

const props = defineProps({
    name: 'Create',
    title: {
        type: String,
        required: true
    },
    routeName: {
        type: String,
        required: true
    },
});

const results = ref(null);
const statuses = [
    { id: 1, name: 'Activo' },
    { id: 0, name: 'No activo' },
]

const form = useForm({
    title: null,
    description: null,
    start_date: null,
    end_date: null,
    status: 1,
    url: null,
    file: null,
    image: null,
    institution_id: null,
});

const saveForm = () => {
    form.post(route(`${props.routeName}store`));
};

const getFileURL = computed(() => {
    if (form.file != null) {
        return URL.createObjectURL(form.file);
    }
    return null;
});

const getImageURL = computed(() => {
    if (form.image != null) {
        return URL.createObjectURL(form.image);
    }
    return null;
});

const filterInstitutions = (search) => {
    axios.get(route(`institution.filter`, search))
        .then((response) => {
            results.value = response.data;
            console.log(results);
        }).catch(function (error) {
            if (error.response) {
                if (error.response.status == 500) {
                    Swal.fire({
                        title: "ERRO!",
                        text: "Error",
                        icon: "info",
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Ok!",
                    });
                }
            }
        });
};

provide('form', form);
</script>

<template>
    <HeadLogo :title="title" />
    <LayoutMain>
        <SectionTitleLineWithButton :icon="mdiPlus" :title="title" main>
            <Link :href="route(`${routeName}index`)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x"
                viewBox="0 0 16 16">
                <path
                    d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
            </svg>
            </Link>
        </SectionTitleLineWithButton>

        <CardBox isForm @submit.prevent="saveForm">
            <FormField label="Título:" required :error="form.errors.title">
                <FormControl height="h-14" v-model="form.title" placeholder="Nombre de la convocatoria" />
            </FormField>

            <FormField label="Descripción:" required help="" :error="form.errors.description">
                <FormControl type="textarea" v-model="form.description" placeholder="Descripción" height="h-56" />
            </FormField>

            <div class="md:grid md:grid-cols-2 gap-4">
                <FormField label="Fecha de inicio:" required help="Inicio de la convocatoria"
                    :error="form.errors.start_date">
                    <FormControl type="date" v-model="form.start_date" />
                </FormField>

                <FormField label="Fecha fin:" required help="Fin de la convocatoria"
                    :error="form.errors.end_date">
                    <FormControl type="date" v-model="form.end_date" />
                </FormField>
            </div>
            <FormField label="Estatus:" required :error="form.errors.status">
                <FormControl v-model="form.status" :options="statuses" />
            </FormField>

            <div class="md:grid md:grid-cols-2 gap-4">
                <FormField label="Url:" help="" :error="form.errors.url">
                    <FormControl v-model="form.url" placeholder="Agrega un enlace" />
                </FormField>

                <FormField required label="Institución:" :error="form.errors.institution_id" class="dark:text-white">
                    <Select placeholder="Busca una institución" v-model="form.institution_id" :options="results" @search="filterInstitutions" />
                </FormField>
            </div>

            <InputFile :file="form.file">
                <template #iframe>
                    <Iframe :url="getFileURL" />
                </template>
            </InputFile>

            <InputImage :image="form.image">
                <template #iframe>
                    <Iframe :url="getImageURL" />
                </template>
            </InputImage>

            <template #footer>
                <BaseButtons>
                    <BaseButton :routeName="`${routeName}index`" :icon="mdiClose" color="white" label="Cancelar" />
                    <BaseButton @click="saveForm" :icon="mdiContentSave" type="submit" color="success"
                        label="Guardar" />
                </BaseButtons>
            </template>
        </CardBox>
    </LayoutMain>
</template>

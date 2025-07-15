<script setup>
import {
    mdiPencil,
    mdiTrashCan,
    mdiContentSave,
    mdiClose,
    mdiInformation,
    mdiPlus
} from "@mdi/js";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import { computed, watch, ref, onMounted, inject } from "vue";
import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import InputError from "@/Components/InputError.vue";
import LabelControl from "@/Components/LabelControl.vue";
import { useForm } from "@inertiajs/vue3";
import Select from '@/Components/Select.vue';
import axios from "axios";
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const form = inject("form");
const props = inject('props');
const areasData = props.areas.filter(area => area.parent_id === null) ?? []
const subareasData = ref([])
const results = ref(props.institutions);
const fileName = computed(() => form.file?.name ?? props.article.file?.name ?? 'Sin archivo');
const fileSize = computed(() => ((form.file?.size ?? props.article.file?.size) / 1000).toFixed(2) + ' KB');

const handleFileInput = (event) => {
    form.file = event.target.files[0];
};

const getFileURL = computed(() =>
    form.file ? URL.createObjectURL(form.file) : props.filePath
);

const prefixes = [
    { id: 'Doctor(a)', name: 'Doctor(a)' },
    { id: 'Maestro(a)', name: 'Maestro(a)' },
    { id: 'Ingeniero(a)', name: 'Ingeniero(a)' },
    { id: 'Licenciado(a)', name: 'Licenciado(a)' },
    { id: 'Profesor(a)', name: 'Profesor(a)' },
];

const addAuthor = () => {
    form.authors.push({
        prefix: null,
        name: null,
        surname: null,
        institution_id: null,
        email: null,
    });
};

const removeAuthor = (index, id) => {
    if (form.authors.length > 0) {
        if (id) {
            axios.delete(route('author.destroy', id))
                .then((response) => {
                    if (response.status === 200) {
                        form.authors.splice(index, 1);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 500) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Se produjo un error al eliminar el autor.',
                            icon: 'error',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Ok',
                        });
                    }
                });
        } else {
            form.authors.splice(index, 1);
        }
    }
};

const getErrorMessage = (index, field) => {
    const errorKey = `authors.${index}.${field}`;
    return form.errors[errorKey] ?? null;
}

const filterAreas = () => {
    if (form.selectedArea !== props.article?.knowledge_area?.parent_id) {
        form.knowledge_area_id = null
    }
    subareasData.value = props.areas.filter(area => area.parent_id === form.selectedArea)
}

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

const getItem = (id) => {
    if (id) {
        return results.value?.find(result => result.id === id);
    }
    return null;
}

watch(() => form.selectedArea, filterAreas);

onMounted(() => { filterAreas() });

</script>

<template>
    <FormField required label="Titulo:" :error="form.errors.title">
        <FormControl height="h-12" v-model="form.title" placeholder="Titulo" />
    </FormField>
    <FormField required label="Estado:" :error="form.errors.state_id">
        <div class="flex items-center ps-4 border border-gray-400 rounded dark:border-gray-700 h-12">
            <input id="bordered-radio-1" type="radio" value="Artículo (6 hojas)" v-model="form.type"
                name="bordered-radio"
                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-800 dark:border-gray-600">
            <label for="bordered-radio-1" class="w-full ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                Artículo normal
                <p class="text-xs font-normal text-gray-500 dark:text-gray-300">
                    6 hojas
                </p>
            </label>
        </div>
        <div class="flex items-center ps-4 border border-gray-400 rounded dark:border-gray-700 h-12">
            <input id="bordered-radio-2" type="radio" value="Artículo corto - Poster (2 hojas)" v-model="form.type"
                name="bordered-radio"
                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-800 dark:border-gray-600">
            <label for="bordered-radio-2" class="w-full ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                Artículo corto
                <p class="text-xs font-normal text-gray-500 dark:text-gray-300">
                    Poster (2 hojas)
                </p>
            </label>
        </div>
    </FormField>
    <FormField required label="Resumen:" :error="form.errors.abstract">
        <FormControl type="textarea" height="h-56" v-model="form.abstract" placeholder="Resumen" />
    </FormField>
    <div class="md:flex md:space-x-4 my-5">
        <div class="md:w-1/2 max-lg:mb-5">
            <FormField label="Area de conocimiento:">
                <FormControl v-model="form.selectedArea" :options="areasData" />
            </FormField>
        </div>
        <div class="md:w-1/2">
            <FormField required label="SubArea:" :error="form.errors.knowledge_area_id">
                <FormControl v-model="form.knowledge_area_id" :options="subareasData" />
            </FormField>
        </div>
    </div>
    <FormField required label="Convocatoria:" :error="form.errors.call_id">
        <FormControl disabled v-model="form.call" placeholder="Convocatoria" />
    </FormField>
    <FormField required label="Palabras clave:" :error="form.errors.key_works">
        <FormControl v-model="form.key_works" placeholder="Palabras clave" />
    </FormField>

    <div class="my-6">
        <div class="justify-between flex mb-6">
            <label class="font-bold">Lista de autores</label>
            <BaseButton v-if="form._method" :icon="mdiPlus" @click="addAuthor" label="Agregar autor" color="info" />
        </div>
        <div class="max-h-96 overflow-y-auto overflow-x-auto">
            <table class="" v-if="form.authors.length > 0">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Prefijo</th>
                        <th>Nombre(s)</th>
                        <th>Apellido(s)</th>
                        <th>Institución</th>
                        <th>Email</th>
                        <th v-if="form._method">Acciones</th>
                    </tr>
                </thead>
                <tbody v-if="form._method">
                    <tr v-for="(author, index) in form.authors" :key="index">
                        <td data-label="No">
                            {{ index + 1 }}
                        </td>
                        <td data-label="Prefijo">
                            <FormControl v-model="author.prefix" :options="prefixes" height="h-8" textClass="tex-xs" />
                            <InputError :message="getErrorMessage(index, 'prefix')" />
                        </td>
                        <td data-label="Nombre(s)">
                            <FormControl v-model="author.name" placeholder="Nombre" height="h-8" textClass="tex-xs" />
                            <InputError :message="getErrorMessage(index, 'name')" />
                        </td>
                        <td data-label="Apellido(s)">
                            <FormControl v-model="author.surname" placeholder="Apellido" height="h-8"
                                textClass="tex-sm" />
                            <InputError :message="getErrorMessage(index, 'surname')" />
                        </td>
                        <td data-label="Institución">
                            <FormField v-if="form._method" :error="getErrorMessage(index, 'institution_id')"
                                textClass="text-sm" class="dark:text-white">
                                <Select :disabled="!form._method" :itemSelected="getItem(author.institution_id)"
                                    placeholder="Busca una institución" height="h-8" v-model="author.institution_id"
                                    required :options="results" @search="filterInstitutions"
                                    dropdownClass="p-2 relative w-full z-50 bg-white dark:bg-gray-800 dark:text-white border border-gray-300 mt-1 max-h-32 overflow-hidden overflow-y-scroll rounded-md shadow-md" />
                            </FormField>
                            <FormControl v-else v-model="author.institution.name" placeholder="Institucion" height="h-8"
                                textClass="tex-sm" />

                        </td>
                        <td data-label="Email">
                            <FormControl type="email" v-model="author.email" placeholder="Email" height="h-8"
                                textClass="tex-xs" />
                            <InputError :message="getErrorMessage(index, 'email')" />
                        </td>
                        <td data-label="Acciones">
                            <BaseButtons>
                                <BaseButton :icon="mdiTrashCan" small @click="removeAuthor(index, author?.id)"
                                    color="danger" />
                            </BaseButtons>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr v-for="(author, index) in form.authors" :key="index">
                        <td data-label="No">
                            {{ index + 1 }}
                        </td>
                        <td data-label="Prefijo">
                            <LabelControl text="text-xs" :value="author.prefix" />
                        </td>
                        <td data-label="Nombre(s)">
                            <LabelControl text="text-xs" :value="author.name" />
                        </td>
                        <td data-label="Apellido(s)">
                            <LabelControl text="text-xs" :value="author.surname" />
                        </td>
                        <td data-label="Institución">
                            <LabelControl class="w-full" text="text-xs"
                                :value="author?.institution?.name ?? 'Sin registro'" />
                        </td>
                        <td data-label="Email">
                            <LabelControl text="text-xs" :value="author.email" />
                        </td>
                    </tr>
                </tbody>
            </table>
            <CardBoxComponentEmpty v-else />
        </div>
    </div>

    <FormField label="Cargar archivo PDF:" required help="Solo archivos PDF (MAX 100M)" :error="form.errors.file">
        <div
            class="bg-slate-100 border-4 border-dashed border-gray-400 flex flex-col items-center justify-center rounded-lg shadow-lg p-6 md:p-10 mb-4 dark:bg-gray-800 dark:border-gray-600">
            <div class="w-auto mb-6 text-sm md:text-base font-medium text-gray-700 dark:text-gray-300">
                <div class="flex justify-center space-x-2">
                    <FormControl v-if="form._method" @input="handleFileInput" height="h-10.5" type="file" />
                </div>
                <p class="font-semibold mt-4">Nombre del archivo: {{ fileName }}</p>
                <p class="font-semibold">Tamaño: {{ fileSize }}</p>
            </div>

            <div class="w-full flex justify-center mt-8 mb-4">
                <template v-if="form.file || props.filePath">
                    <iframe class="w-full h-96 border rounded-lg shadow-md" :src="getFileURL" />
                </template>
            </div>
        </div>
    </FormField>
</template>
<script setup>
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import { mdiPencil, mdiTrashCan, mdiContentSave, mdiClose } from "@mdi/js";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { Link, Head } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import { computed, watch, ref, onMounted } from "vue";
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
    institution: { type: Object, required: true },
    countries: { type: Object, default: {}, required: true },
    states: { type: Object, default: {}, required: true }
});
const statesData = ref([])
const form = useForm({
    id: props.institution.id,
    name: props.institution.name,
    country_id: props.institution.country_id, // carga pais si es que existe
    state_id: props.institution.state_id,
    status: props.institution.status,
    created_at: props.institution.created_at,
    updated_at: props.institution.updated_at,
    deleted_at: props.institution.deleted_at,
});

const saveForm = () => {
    form.put(route(`${props.routeName}update`, props.institution.id));
};
const deleteForm = () => {
    Swal.fire({
        title: "¿Esta seguro?",
        text: "Esta acción no se puede revertir",
        icon: "warning",
        showCancelButton: true,
        cancelButtonText: "Cancelar",
        cancelButtonColor: "#1C64F2",
        confirmButtonColor: "#E02424",
        confirmButtonText: "Si!, eliminar registro!",
    }).then((res) => {
        if (res.isConfirmed) {
            form.delete(route(`${props.routeName}destroy`, props.institution.id));
        }
    });
};

const statuses = [
    { id: 1, name: "Activo" },
    { id: 0, name: "Inactivo" },
]

const filterStates = () => {
    if (form.country_id !== props.institution.country_id) {
        form.state_id = null
    }
    statesData.value = props.states.filter(state => state.country_id == form.country_id);
}

watch(() => form.country_id, filterStates);

onMounted(() => {
    filterStates()
});

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
        <CardBox isForm @submit.prevent="saveForm">
            <FormField label="Nombre:" required :error="form.errors.name">
                <FormControl v-model="form.name" placeholder="Nombre de la institución" />
            </FormField>

            <div class="md:flex md:space-x-4 my-5">
                <div class="md:w-1/2 max-lg:mb-5">
                    <FormField required label="Pais:" :error="form.errors.country_id">
                        <FormControl v-model="form.country_id" :options="countries" />
                    </FormField>
                </div>
                <div class="md:w-1/2 max-lg:mb-5">
                    <FormField required label="Estado:" :error="form.errors.state_id">
                        <FormControl v-model="form.state_id" :options="statesData" />
                    </FormField>
                </div>
            </div>
            <FormField required label="Estatus:" :error="form.errors.status">
                <FormControl v-model="form.status" :options="statuses" />
            </FormField>
            <template #footer>
                <BaseButtons>
                    <BaseButton :routeName="`${routeName}index`" :icon="mdiClose" color="white" label="Cancelar" />
                    <BaseButton @click="saveForm" :icon="mdiContentSave" type="submit" color="success"
                        label="Guardar" />
                    <BaseButton color="danger" :icon="mdiTrashCan" @click="deleteForm" label="Eliminar" />
                </BaseButtons>
            </template>
        </CardBox>
    </LayoutMain>
</template>

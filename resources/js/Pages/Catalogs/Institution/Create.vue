<script setup>
import CardBox from "@/Components/CardBox.vue";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import { mdiContentSave, mdiClose, mdiPlus } from "@mdi/js";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { Link, Head } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import { computed, watch, ref } from "vue";
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
    countries: { type: Object, default: {}, required: true },
    states: { type: Object, default: {}, required: true }
});
const statesData = ref([])
const form = useForm({ 
    name: null, 
    country_id: null, 
    state_id: null, 
    status: true 
});

const saveForm = () => {
    form.post(route(`${props.routeName}store`));
};

const filterStates = () => {
    form.state_id = null
    statesData.value = props.states.filter(state => state.country_id == form.country_id);
}

const statuses = [
    { id: true, name: "Activo" },
    { id: false, name: "Inactivo" },
]

watch(() => form.country_id, filterStates);

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
                </BaseButtons>
            </template>
        </CardBox>
    </LayoutMain>
</template>

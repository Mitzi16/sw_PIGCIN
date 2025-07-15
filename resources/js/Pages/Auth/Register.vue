<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    mdiAccount,
    mdiAsterisk,
    mdiLogin,
    mdiEmail,
    mdiMagnify
} from '@mdi/js'
import LayoutGuest from '@/Layouts/LayoutGuest.vue';
import SectionFullScreen from '@/Components/SectionFullScreen.vue';
import CardBox from '@/Components/CardBox.vue';
import FormValidationErrors from '@/Components/FormValidationErrors.vue';
import NotificationBarInCard from '@/Components/NotificationBarInCard.vue';
import FormField from '@/Components/FormField.vue';
import FormControl from '@/Components/FormControl.vue';
import FormCheckRadioGroup from '@/Components/FormCheckRadioGroup.vue';
import BaseButton from '@/Components/BaseButton.vue';
import BaseButtons from '@/Components/BaseButtons.vue';
import Select from '@/Components/Select.vue';
import BaseDivider from '@/Components/BaseDivider.vue';
import { computed, watch, ref } from "vue";
import axios from "axios";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import JetInputError from "@/Components/InputError.vue";
import { trimEnd } from 'lodash';
import HeadLogo from '@/Components/HeadLogo.vue';

const props = defineProps({
    areas: { type: Object, required: true, default: {} },
})

const areasData = props.areas.filter(area => area.parent_id === null) ?? []
const subareasData = ref([])
const form = useForm({
    curp: null,
    name: null,
    email: null,
    password: null,
    password_confirmation: null,
    knowledge_area_id: null,
    institution_id: null,
    selectedArea: null,
});

const isLoading = ref(false);

const submit = () => {
    form.post(route('register'))
};

const getCurp = () => {
    isLoading.value = true;
    axios.get(route(`user.getCurp`, form.curp.toUpperCase()))
        .then((response) => {
            isLoading.value = false;
            const data = response.data.dataCurp;
            form.name = data.nombres.toUpperCase() + ' ' + data.apellidoPaterno.toUpperCase() + ' ' + data.apellidoMaterno.toUpperCase()
        }).catch(function (error) {
            if (error.response) {
                if (error.response.status == 500) {
                    Swal.fire({
                        title: "CURP Incorrecta!",
                        text: "La CURP ingresada no es valida, intente nuevamente",
                        icon: "info",
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Ok!",
                    });
                }
            }
            form.reset('name')
            isLoading.value = false
        })
};

const results = ref(null);
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

const filterAreas = () => {
    form.knowledge_area_id = null
    subareasData.value = props.areas.filter(area => area.parent_id === form.selectedArea)
}

watch(() => form.selectedArea, filterAreas);
// watch(() => sea, filterAreas);

</script>

<template>
    <div class="vl-parent">
        <loading v-model:active="isLoading" :can-cancel="false" :is-full-page="true" />
    </div>
    <HeadLogo title="Registro" />
    <LayoutGuest>
        <SectionFullScreen bg="white">
            <div class="max-w-2xl py-10">
                <CardBox rounded="rounded-lg" padding="p-6" is-form @submit.prevent="submit">
                    <h1 class="mb-4 text-center text-2xl lg:text-3xl font-bold dark:text-white">Registrate!</h1>
                    <FormValidationErrors class="mt-4" />

                    <div class="mb-5">
                        <label class="font-bold dark:text-white">CURP:</label>
                        <div class="md:flex md:space-x-1 mb-1 mt-2">
                            <div class="md:w-full max-lg:mb-5">
                                <FormControl textClass="dark:text-white" placeholder="Ingresa CURP" v-model="form.curp" maxlength="18" />
                            </div>
                            <div class="max-lg:mb-5 my-auto">
                                <BaseButton class="w-full" label="Buscar CURP" color="info" :icon="mdiMagnify"
                                    @click="getCurp()" />
                            </div>
                        </div>
                        <JetInputError class="mb-5" :message="form.errors.curp" />
                    </div>

                    <FormField required textClass="dark:text-white" label="Nombre completo:" help="Por favor, introduce tu nombre completo"
                        :error="form.errors.name">
                        <FormControl v-model="form.name" :icon="mdiAccount" placeholder="Nombre completo" required />
                    </FormField>

                    <FormField required textClass="dark:text-white" label="Correo electrónico:" help="Por favor, introduce tu email"
                        :error="form.errors.email">
                        <FormControl v-model="form.email" :icon="mdiEmail" type="email" placeholder="Correo electrónico"
                            required />
                    </FormField>

                    <FormField required textClass="dark:text-white" label="Contraseña:" help="Por favor, introduce una contraseña"
                        :error="form.errors.password">
                        <FormControl v-model="form.password" :icon="mdiAsterisk" type="password"
                            placeholder="Contraseña" required />
                    </FormField>

                    <FormField required textClass="dark:text-white"  label="Confirmar contraseña:" help="Las contraseñas deben coincidir"
                        :error="form.errors.password_confirmation">
                        <FormControl v-model="form.password_confirmation" :icon="mdiAsterisk" type="password"
                            placeholder="Confirmar contraseña" required />
                    </FormField>

                    <div class="md:flex md:space-x-4 my-5">
                        <div class="md:w-1/2 max-lg:mb-5">
                            <FormField textClass="dark:text-white"  label="Area de conocimiento:">
                                <FormControl  v-model="form.selectedArea" :options="areasData" />
                            </FormField>
                        </div>
                        <div class="md:w-1/2">
                            <FormField textClass="dark:text-white" required label="SubArea:" :error="form.errors.knowledge_area_id">
                                <FormControl v-model="form.knowledge_area_id" :options="subareasData" />
                            </FormField>
                        </div>
                    </div>

                    <FormField required label="Institución:" :error="form.errors.institution_id"
                        class="dark:text-white">
                        <Select placeholder="Busca una institución" v-model="form.institution_id" required :options="results"
                            @search="filterInstitutions" />
                    </FormField>
                    
                    <BaseButtons class="mt-6">
                        <BaseButton class="w-full" type="submit" color="info" label="Crear una cuenta"
                            :class="{ 'opacity-25': form.processing }" :procesing="form.processing" />
                    </BaseButtons>
                </CardBox>
            </div>
        </SectionFullScreen>
    </LayoutGuest>
</template>

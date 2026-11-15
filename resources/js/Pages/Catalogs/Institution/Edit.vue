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

        <!-- CardBox con el diseño adaptado -->
        <CardBox isForm @submit.prevent="saveForm" class="!p-0 overflow-hidden border border-gray-200 shadow-sm rounded-xl mb-10">
            
            <!-- Cabecera Azul -->
            <div class="bg-[#1e3a8a] px-6 py-5 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z" />
                </svg>
                <h1 class="text-white text-lg font-bold">Editar Datos de la Institución</h1>
            </div>

            <!-- Contenido del Formulario -->
            <div class="p-8">
                
                <!-- Subtítulo de la sección -->
                <div class="flex items-center gap-2 mb-8 border-b border-gray-200 pb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#1e3a8a]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z" />
                    </svg>
                    <h2 class="text-xl font-bold text-[#1e3a8a]">Datos de la Institución</h2>
                </div>

                <div class="space-y-6">
                    <!-- Nombre -->
                    <FormField :error="form.errors.name" class="!mb-0">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-y-2 gap-x-8 items-start">
                            <label class="col-span-1 font-bold text-gray-800 text-right pt-3 flex justify-end gap-1">
                                <span class="text-red-500">*</span> Nombre:
                            </label>
                            <div class="col-span-3">
                                <FormControl v-model="form.name" placeholder="Nombre de la institución" />
                            </div>
                        </div>
                    </FormField>

                    <!-- País -->
                    <FormField :error="form.errors.country_id" class="!mb-0">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-y-2 gap-x-8 items-start">
                            <label class="col-span-1 font-bold text-gray-800 text-right pt-3 flex justify-end gap-1">
                                <span class="text-red-500">*</span> País:
                            </label>
                            <div class="col-span-3">
                                <FormControl v-model="form.country_id" :options="countries" />
                            </div>
                        </div>
                    </FormField>

                    <!-- Estado -->
                    <FormField :error="form.errors.state_id" class="!mb-0">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-y-2 gap-x-8 items-start">
                            <label class="col-span-1 font-bold text-gray-800 text-right pt-3 flex justify-end gap-1">
                                <span class="text-red-500">*</span> Estado:
                            </label>
                            <div class="col-span-3">
                                <FormControl v-model="form.state_id" :options="statesData" />
                            </div>
                        </div>
                    </FormField>

                    <!-- Estatus -->
                    <FormField :error="form.errors.status" class="!mb-0">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-y-2 gap-x-8 items-start">
                            <label class="col-span-1 font-bold text-gray-800 text-right pt-3 flex justify-end gap-1">
                                <span class="text-red-500">*</span> Estatus:
                            </label>
                            <div class="col-span-3">
                                <FormControl v-model="form.status" :options="statuses" />
                            </div>
                        </div>
                    </FormField>
                </div>

            </div>

            <!-- Footer Original con botones de acción -->
            <template #footer>
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-start w-full">
                    <BaseButtons>
                        <BaseButton :routeName="`${routeName}index`" :icon="mdiClose" color="white" label="Cancelar" />
                        <BaseButton @click="saveForm" :icon="mdiContentSave" type="submit" color="success" label="Guardar" />
                        <BaseButton color="danger" :icon="mdiTrashCan" @click="deleteForm" label="Eliminar" />
                    </BaseButtons>
                </div>
            </template>
        </CardBox>
    </LayoutMain>
</template>
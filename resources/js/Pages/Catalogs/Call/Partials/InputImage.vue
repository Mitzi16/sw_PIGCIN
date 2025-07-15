<script setup>
import { inject, computed } from "vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";

const form = inject('form');

const props = defineProps({
    image: {
        required: true
    }
});

const handleFileInput = (event) => {
    form.image = event.target.files[0];
};

</script>

<template>
    <FormField label="Cargar imagen" required help="Solo archivos jpeg, jpg y png (MAX 1MB)" :error="form.errors.image">
        <div
            class="bg-slate-100 border-2 border-dotted border-slate-500 justify-center items-center h-auto rounded-sm p-10 mb-2 dark:bg-slate-800">
            <div class="justify-center flex mb-5">
                <FormControl @input="handleFileInput" type="file" height="h-10.5" />
            </div>

            <div v-if="image">
                <div class="text-sm text-gray-700 dark:text-gray-200 font-medium max-md:text-xs text-center mb-5">
                    <p>Nombre del archivo: {{ image?.name }}</p>
                    <p>Tamaño: {{ image?.size / 1000 }} kilobytes</p>
                </div>

                <slot name="iframe">

                </slot>
            </div>
        </div>
    </FormField>
</template>
<script setup>
import { inject, computed } from "vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";

const form = inject('form');

const props = defineProps({
    file: {
        required: true
    }
});

const handleFileInput = (event) => {
    form.file = event.target.files[0];
};

</script>

<template>
    <FormField label="Cargar archivo PDF" required help="Solo archivos pdf (MAX 10MB)" :error="form.errors.file">
        <div
            class="bg-slate-100 border-2 border-dotted border-slate-500 justify-center items-center h-auto rounded-sm p-10 mb-2 dark:bg-slate-800">
            <div class="justify-center flex mb-5">
                <FormControl @input="handleFileInput" type="file" height="h-10.5" />
            </div>

            <div v-if="file">
                <div class="text-sm text-gray-700 dark:text-gray-200 font-medium max-md:text-xs text-center mb-5">
                    <p>Nombre del archivo: {{ file?.name }}</p>
                    <p>Tamaño: {{ file?.size / 1000 }} kilobytes</p>
                </div>

                <slot name="iframe">

                </slot>
            </div>
        </div>
    </FormField>
</template>
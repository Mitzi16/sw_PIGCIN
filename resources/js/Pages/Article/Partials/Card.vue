<script setup>
import BaseButton from '@/Components/BaseButton.vue';
import BaseButtons from '@/Components/BaseButtons.vue';
import { mdiArrowRight, mdiCheck } from '@mdi/js';
import { ref, watch, watchEffect, inject } from "vue";

const props = defineProps({
    call: {
        type: Object,
        required: true
    }
});

const form = inject('form')

</script>

<template>
    <div
        class="pb-4 rounded-md border shadow-sm w-full max-w-sm group transition-all duration-200 hover:translate-y-2.5 hover:shadow-lg dark:border-gray-800 dark:bg-gray-800">

        <img :src="call.image != null ? '/storage/' + call.image.path : '/img/login-image.jpg'"
            alt="imagen convocatoria" class="h-60 w-full rounded-t object-cover">

        <div class="px-4">
            <div class="mt-3 text-left space-y-2 ">
                <span class=" text-sm font-light text-gray-800 dark:text-gray-400">{{ call.start_date }}</span>
                <h4
                    class="line-clamp-2 h-14 mt-2 text-lg font-semibold group-hover:text-blue-500 text-gray-800 dark:text-white">
                    {{ call.title }}
                </h4>
            </div>

            <div class="line-clamp-2 text-ellipsis h-12">
                <p class="mt-2 text-sm text-justify text-gray-600 dark:text-gray-300">
                    {{ call.description }}
                </p>
            </div>

            <div class="justify-end flex space-x-2 mt-4">
                <BaseButton small :icon="mdiCheck" @click="form.call_id = call.id"
                    :color="form.call_id == call.id ? 'success' : 'constrast'"
                    :label="form.call_id == call.id ? 'Seleccionado' : 'Seleccionar'" />
            </div>
        </div>
    </div>
</template>
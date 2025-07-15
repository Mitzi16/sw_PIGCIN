<script setup>
import BaseIcon from "@/Components/BaseIcon.vue";
import Modal from '@/Components/Modal.vue';
import moment from "moment";
import { inject, ref } from 'vue';
import { mdiOpenInNew, mdiCalendarRange, mdiClockOutline, mdiCalendarRemove, mdiFilePdfBox, mdiClose } from "@mdi/js";
import CardBox from "@/Components/CardBox.vue";
import BaseButton from "@/Components/BaseButton.vue";
const { showModal, setShowModal } = inject('showModal');
defineProps({
    call: Object
})

const readingTime = (text) => {
    const words = text.split(/\s+/).length;
    return Math.ceil(words / 200);
};

const showPdf = ref(false)

const togglePdf = () => {
    showModal.value = !showModal.value;
    showPdf.value = !showPdf.value;
}

</script>

<template>
    <Modal :show="showModal" @close="setShowModal(false)" :closeable="true">
        <div class="px-4 mb-4">
            <img :src="call.image != null ? '/storage/' + call.image.path : '/img/login-image.jpg'"
                class="rounded mt-8 pt-4 w-full h-80 object-cover" alt="imagen convocatoria" />

            <div class="my-4 pr-12">
                <h2 class="text-left text-lg font-bold">
                    {{ call.title }}
                </h2>
            </div>

            <div class="mb-2 justify-start flex text-gray-700 md:w-1/2">
                <BaseIcon :path="mdiCalendarRange" size="15" />
                <p class="text-left text-gray-600">
                    Fecha de inicio
                </p>
                <p class="ml-auto text-black font-bold">
                    {{ moment(call.start_date, "YYYY-MM-DD").format("DD-MM-YYYY") }}
                </p>
            </div>

            <div class="mb-2 justify-start flex text-gray-700 md:w-1/2">
                <BaseIcon :path="mdiCalendarRemove" size="15" />
                <p class="text-left text-gray-600">
                    Fecha de cierre
                </p>
                <p class="ml-auto text-black font-bold">
                    {{ moment(call.end_date, "YYYY-MM-DD").format("DD-MM-YYYY") }}
                </p>
            </div>

            <div class="mb-2 justify-start flex text-gray-700 md:w-1/2">
                <BaseIcon :path="mdiClockOutline" size="15" />
                <p class="text-left text-gray-600">
                    Tiempo de lectura
                </p>
                <p class="ml-auto text-black font-bold">
                    {{ readingTime(call.description) }} min
                </p>
            </div>

            <div v-if="call.url" class="mb-2 justify-start flex text-gray-700 md:w-1/2">
                <BaseIcon :path="mdiOpenInNew" size="15" />
                <p class="text-left text-gray-600"> URL </p>
                <a target="_blank" :href="call.url"
                    class="ml-auto text-blue-500 underline decoration-sky-450 font-bold transition hover:cursor-pointer hover:text-blue-600">
                    Click aquí
                </a>
            </div>

            <div v-if="call.file" class="mb-4 justify-start flex text-gray-700 md:w-1/2">
                <BaseIcon :path="mdiFilePdfBox" size="15" />
                <p class="text-left text-gray-600"> Convocatoria </p>
                <a @click="togglePdf()"
                    class="ml-auto text-blue-500 underline decoration-sky-450 font-bold transition hover:cursor-pointer hover:text-blue-600">
                    Click aquí
                </a>
            </div>

            <hr class="border-t-2 border-solid border-blue-300" />

            <div class="">
                <p class="mt-4 text-base text-justify text-gray-500"> {{ call.description }} </p>
            </div>

        </div>
    </Modal>

    <div v-if="showPdf"
        class="fixed top-0 left-0 w-full h-full flex items-center justify-center z-50 bg-slate-800 bg-opacity-50"
        tabindex="0" @keydown.esc="togglePdf()">
        <div class="relative w-full max-w-4xl mx-auto">
            <CardBox class="mt-5" :is-modal="true">
                <div class="justify-between flex p-2">
                    <span class="font-bold text-sm">
                        Archivo: {{ call.file.name }}
                    </span>
                    <BaseButton color="danger" :icon="mdiClose" small @click="togglePdf()" />
                </div>
                <div class="overflow-hidden rounded-md">
                    <iframe :src="'/storage/' + call.file.path" class="w-full h-[75vh]" />
                </div>
            </CardBox>
        </div>
    </div>
</template>
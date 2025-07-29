<script setup>
import HeadLogo from '@/Components/HeadLogo.vue';
import Calls from './Partials/Calls/Calls.vue';
import HeroSection from './Partials/HeroSection.vue';
import LayoutWelcome from '@/Layouts/LayoutWelcome.vue';
import { Head, Link } from '@inertiajs/vue3';
const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    calls:{
        type: Object,
        required: true
    },
    search: { type: String, required: true },
    title: {
        type: String,
        required: true
    }
});

</script>

<template>
    <HeadLogo :title="title" />
    <LayoutWelcome>
        <HeroSection>
            <template #title>
                5to. Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA)
            </template>
            <template #description>
                El Tecnológico Nacional de México (TecNM) a través del Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET), invita a toda la comunidad académica, científica, profesional y estudiantil a enviar sus contribuciones al 5to. Congreso Internacional de Tecnología y Ciencias Aplicadas (CITCA), que se llevará a cabo de manera HÍBRIDA (presencial o virtual) en la ciudad de Cuernavaca Morelos, del 26 al 28 de noviembre de 2025.
            </template>
        </HeroSection>
        
        <Calls :calls="calls" :search="search" />
    </LayoutWelcome>
</template>

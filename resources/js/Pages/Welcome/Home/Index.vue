<script setup>
import HeadLogo from '@/Components/HeadLogo.vue';
import Calls from './Partials/Calls/Calls.vue';
import HeroSection from './Partials/HeroSection.vue';
import LayoutWelcome from '@/Layouts/LayoutWelcome.vue';

// 1. Importamos el componente de la carpeta Place
import LugarIndex from '../Place/Index.vue';

// 1. Importamos el buscador
import ArticleSearch from '@/Pages/Welcome/Article/Search.vue';

import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    calls: { type: Object, required: true },
    search: { type: String, required: true },
    title: { type: String, required: true }
});
</script>

<template>
    <HeadLogo :title="title" />

    <LayoutWelcome>

        <!-- Sección de arriba (Hero) -->
        <HeroSection>
            <template #title>
                PIGCIN
            </template>
            <template #description>
                El Tecnológico Nacional de México (TecNM), a través del Centro Nacional de Investigación y Desarrollo
                Tecnológico (CENIDET), presenta una Plataforma Inteligente de Gestión de Congresos Nacionales e
                Internacionales, diseñada para fortalecer la organización, administración y participación en eventos
                académicos, científicos y tecnológicos.
            </template>
        </HeroSection>

        <!-- Convocatorias -->
        <Calls :calls="calls" :search="search" />

        <!-- Buscador de Articulo -->
        <ArticleSearch />

        <!-- Lugar (Mapa del CENIDET) -->
        <LugarIndex />

    </LayoutWelcome>
</template>
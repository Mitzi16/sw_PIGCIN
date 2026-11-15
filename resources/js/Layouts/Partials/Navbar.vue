<script setup>
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import NavLink from "@/Components/NavLink.vue";
import { Link, usePage } from '@inertiajs/vue3';
import { useStyleStore } from "@/stores/style.js";
import { ref } from 'vue';
import { mdiClose, mdiMenu, mdiWhiteBalanceSunny, mdiWeatherNight } from '@mdi/js';
import BaseIcon from "@/Components/BaseIcon.vue";

const styleStore = useStyleStore();
const isDarkMode = ref(styleStore.darkMode);
const isAuthenticate = usePage().props.auth.user?.id ? true : false;

const handleToggle = () => {
    styleStore.setDarkMode();
};

const showMenu = ref(false);
const toggleNav = () => {
    showMenu.value = !showMenu.value;
}
</script>

<template>
    <!-- Fondo azul oficial TecNM con borde inferior sutil -->
    <div class="bg-[#1B396A] border-b border-white/10 sticky top-0 z-50">
        <nav class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">

                <!-- 1. LOGO -->
                <div class="flex items-center">
                    <Link href="/" class="flex items-center space-x-2">
                        <!-- Si el logo es oscuro, deberías usar la versión blanca aquí -->
                        <img src="/img/tecnm_white.png" alt="Logo CITCA" class="h-10 w-auto">
                        <span class="text-white font-extrabold text-xl tracking-tight hidden sm:block">
                            PIGCIN
                        </span>
                    </Link>
                </div>

                <!-- Botón menú móvil -->
                <div @click="toggleNav" class="flex lg:hidden">
                    <BaseButton :icon="showMenu ? mdiClose : mdiMenu" color="info" class="text-white" />
                </div>

                <!-- 2. ENLACES DE NAVEGACIÓN -->
                <div :class="showMenu ? 'flex flex-col absolute top-20 left-0 w-full bg-[#1B396A] border-b border-white/10 pb-6 px-4 shadow-lg lg:shadow-none lg:border-none lg:pb-0 lg:px-0 lg:static lg:w-auto' : 'hidden'"
                    class="lg:flex lg:items-center lg:space-x-8">

                    <ul
                        class="flex flex-col space-y-4 lg:flex-row lg:space-y-0 lg:space-x-8 lg:items-center mt-4 lg:mt-0">
                        <li>
                            <!-- CAMBIO: Texto blanco por defecto, gris al pasar el cursor -->
                            <NavLink :active="$page.component === 'Welcome/Home/Index'" href="/"
                                class="text-xs font-bold uppercase tracking-wide text-white hover:text-gray-400 transition-colors">
                                <span
                                    :class="$page.component === 'Welcome/Home/Index' ? 'border-b-2 border-white pb-1 text-white' : ''">
                                    INICIO
                                </span>
                            </NavLink>
                        </li>
                        <li>
                            <Link href="/#estatus"
                                class="text-xs font-bold uppercase tracking-wide text-white hover:text-gray-400 transition-colors text-center leading-tight max-w-[120px]">
                                CONSULTAR ESTATUS<br>DE MI ARTÍCULO
                            </Link>
                        </li>
                        <li>
                            <!-- Cambiamos NavLink por una etiqueta <a> normal -->
                            <Link href="/#lugar"
                                class="text-xs font-bold uppercase tracking-wide text-white hover:text-gray-400 transition-colors">
                                LUGAR
                            </Link>
                        </li>
                        <li>
                            <NavLink :active="$page.component === 'Components/CitcaInfo/CitcaContainer'"
                                :href="route('welcome.info')"
                                class="text-xs font-bold uppercase tracking-wide text-white hover:text-gray-400 transition-colors text-center leading-tight max-w-[120px]">
                                CONOCE MÁS DE<br>CITCA-2025
                            </NavLink>
                        </li>
                    </ul>
                </div>

                <!-- 3. ACCIONES (Botones e interruptor) -->
                <div class="hidden lg:flex items-center space-x-6">

                    <!-- Botones de Sesión -->
                    <div class="flex items-center space-x-3">
                        <Link v-if="!isAuthenticate" href="/login"
                            class="px-4 py-2 text-sm font-semibold text-white border border-white hover:bg-white/10 rounded-md transition-colors">
                            Iniciar sesión
                        </Link>
                        <Link v-if="!isAuthenticate" href="/register"
                            class="px-4 py-2 text-sm font-semibold text-[#1B396A] bg-white rounded-md hover:bg-gray-100 transition-colors shadow-sm">
                            Registrarse
                        </Link>
                        <Link v-if="isAuthenticate" href="/dashboard"
                            class="px-4 py-2 text-sm font-semibold text-[#1B396A] bg-white rounded-md hover:bg-gray-100 transition-colors shadow-sm">
                            Ir al Panel
                        </Link>
                    </div>

                    <!-- Separador vertical -->
                    <div class="h-6 w-px bg-white/20"></div>

                    <!-- Interruptor Modo Oscuro -->
                    <div class="flex items-center gap-2">
                        <BaseIcon :path="mdiWhiteBalanceSunny" class="text-gray-300" size="20" />

                        <label class="relative inline-flex items-center cursor-pointer">
                            <input @click="handleToggle" type="checkbox" v-model="isDarkMode" class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-white">
                            </div>
                        </label>

                        <BaseIcon :path="mdiWeatherNight" class="text-gray-300" size="20" />
                    </div>

                </div>
            </div>
        </nav>
    </div>
</template>
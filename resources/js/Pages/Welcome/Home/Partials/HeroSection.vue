<script setup>
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { mdiArrowRight  } from '@mdi/js';

</script>

<template>
    <section class="py-20 bg-white dark:bg-blue-950">
        <div
            class="max-w-screen-xl mx-auto text-gray-600 gap-x-12 items-center justify-between overflow-hidden md:flex md:px-8">
            <div class="flex-none space-y-5 px-4 sm:max-w-lg md:px-0 lg:max-w-xl">
                <!-- <p class="text-6xl text-blue-600 dark:text-blue-400 font-medium">
                    5to.
                </p> -->
                <h1 class="text-4xl text-gray-800 dark:text-blue-400 font-extrabold md:text-5xl">
                    <slot name="title">
                    </slot>
                </h1>
                <p class="dark:text-gray-400">
                    <slot name="description">
                    </slot>
                </p>

                <BaseButtons>
                    <BaseButton label="Registra un artículo" 
                    color="info" :icon="mdiArrowRight" href="/register"/>

                    <BaseButton label="Consultar el estatus de mi artículo" 
                    color="success" :icon="mdiArrowRight" href="/welcome/article/search"/>
                </BaseButtons>
                
            </div>
            <div class="flex-none mt-14 md:mt-0 md:max-w-xl">
                <img src="/img/LogoCitca.png"
                    class=" md:rounded-tl-[108px]" alt="" />
            </div>
        </div>
    </section>
</template>
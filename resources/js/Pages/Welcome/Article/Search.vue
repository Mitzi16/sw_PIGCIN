<script setup>
import HeadLogo from '@/Components/HeadLogo.vue';
import LayoutWelcome from '@/Layouts/LayoutWelcome.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BaseButtons from '@/Components/BaseButtons.vue';
import BaseButton from '@/Components/BaseButton.vue';
import FormField from '@/Components/FormField.vue';
import FormControl from '@/Components/FormControl.vue';
import {
    mdiArrowRight,
    mdiQrcode
} from "@mdi/js";
import BaseDivider from '@/Components/BaseDivider.vue'; 
import Swal from "sweetalert2";

// Props
const props = defineProps({
    title: { type: String, required: true },
    code: { type: String, required: false, default: null }
});

// Form
const form = useForm({ code: props.code });

// Submit handler con validación
const submitForm = () => {
  if (!form.code || form.code.trim() === "") {
    Swal.fire({
      icon: 'warning',
      title: 'Folio requerido',
      text: 'Por favor ingresa el número de folio antes de consultar.',
      confirmButtonText: 'OK'
    });
    return;
  }

  form.get(route('welcome.show.article', { code: form.code }));
};
</script>

<template>
  <HeadLogo :title="title" />
  <LayoutWelcome>
    <section class="min-h-screen py-20 bg-white dark:bg-blue-950">
      <div class="max-w-screen-xl mx-auto text-gray-600 gap-x-12 items-center justify-between overflow-hidden md:flex md:px-8">
        <div class="flex-none space-y-5 px-4 sm:max-w-lg md:px-0 lg:max-w-xl">
          <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">NUEVO</p>
          <h1 class="text-4xl text-gray-800 dark:text-blue-400 font-extrabold md:text-5xl">
            Buscador de artículos
          </h1>

          <!-- Formulario con @submit.prevent llamando a submitForm -->
          <form class="space-y-5" @submit.prevent="submitForm">
            <p class="dark:text-gray-400">
              <FormField label="Identificador o Número de Artículo" :error="form.errors.code" help="Ingresa el número de artículo o folio">
                <FormControl v-model="form.code" placeholder="Ingresa el número de artículo o folio" :icon="mdiQrcode" />
              </FormField>
            </p>

            <BaseButtons>
              <BaseButton
                :class="[{ 'animate-bounce animate-twice': form.code != null }]"
                label="Consultar"
                type="submit"
                color="info"
                :icon="mdiArrowRight"
              />
            </BaseButtons>
          </form>
        </div>

        <!-- Imagen -->
        <div class="flex-none mt-14 md:mt-0 hidden md:block">
          <img src="/img/qr.gif" class="lg:w-96 md:w-60 dark:bg-white rounded-3xl" alt="" width="380" />
        </div>

        <!-- Vista móvil -->
        <div class="flex-none w-full block md:hidden">
          <BaseDivider />
          <div class="p-4 text-center flex items-center justify-center h-full text-white lg:bg-black/45">
            <div class="block px-2">
              <h2 class="font-bold text-xl lg:text-3xl mb-4 text-black lg:text-white dark:text-white">
                ¿Quieres registrar un nuevo artículo?
              </h2>
              <p class="text-xs text-gray-600 lg:text-white dark:text-white">
                Hazlo de forma rápida y sencilla
              </p>
              <Link href="/register"
                class="lg:hidden text-xs text-blue-500 underline-offset-4 hover:underline hover:text-blue-700 hover:dark:text-blue-400">
                Saber más
              </Link>

              <BaseButton class="w-auto mt-6 hidden lg:flex" type="submit" color="white"
                label="Volver al inicio" routeName="welcome" small />
            </div>
          </div>
        </div>
      </div>
    </section>
  </LayoutWelcome>
</template>
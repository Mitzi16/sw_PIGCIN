<script setup>
import HeadLogo from '@/Components/HeadLogo.vue';
import LayoutWelcome from '@/Layouts/LayoutWelcome.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        required: true
    }
});

</script>

<template>
    <HeadLogo :title="title" />
    <LayoutWelcome>
        <section>
            <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:py-20 lg:px-8">
                <div class="max-w-2xl lg:max-w-4xl mx-auto text-center">
                    <h2 class="dark:text-blue-200 text-3xl sm:text-4xl font-semibold text-gray-900">Visita nuestras
                        instalaciones</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-3">Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
                </div>
                <div class="mt-16 lg:mt-20">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="rounded-lg overflow-hidden">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1887.6125772172147!2d-99.22129268561007!3d18.877090098988177!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85cddf3a4772c8c3%3A0xca05f1ffbc24908!2sTecNM%20Cenidet!5e0!3m2!1ses-419!2smx!4v1723260987127!5m2!1ses-419!2smx"
                                width="100%" height="480" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                        <div>
                            <div class="max-w-full mx-auto rounded-lg overflow-hidden">
                                <div class="px-6 py-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200">
                                        Centro Nacional de Investigación y Desarrollo Tecnológico
                                    </h3>
                                    <p class="text-justify mt-1 text-gray-600 dark:text-gray-400">
                                        El Tecnológico Nacional de México/Centro Nacional de Investigación y Desarrollo
                                        Tecnológico (TecNM/Cenidet) está ubicado en Cuernavaca, Morelos, México, es un
                                        Centro de excelencia en los posgrados que imparte y en la investigación que
                                        desarrolla, siendo una institución colaborativa e interdisciplinaria con un
                                        enfoque ingenieril en las áreas de electrónica, mecánica y ciencias
                                        computacionales, con una amplia gama de aplicaciones en el sector tecnológico y
                                        en la industria.
                                    </p>
                                </div>
                                <div class="border-t border-gray-400 dark:border-gray-200 px-6 py-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200">Dirección</h3>
                                    <p class="mt-1 text-gray-600 dark:text-gray-400">Interior Internado Palmira S/N,
                                        Palmira, 62493 Cuernavaca, Mor.</p>
                                </div>

                                <div class="border-t border-gray-400 dark:border-gray-200 px-6 py-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200">Contacto</h3>
                                    <p class="mt-1 text-gray-600 dark:text-gray-400">Email: info@example.com</p>
                                    <p class="mt-1 text-gray-600 dark:text-gray-400">Teléfono: +52 777 362 77 70</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </LayoutWelcome>
</template>
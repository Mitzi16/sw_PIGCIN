<script setup>
// Importaciones necesarias
</script>

<template>
    <section class="relative min-h-[400px] md:min-h-[500px] flex flex-col bg-[#1B396A] overflow-hidden">

        <!-- 2. Imagen de Fondo -->
        <div class="absolute inset-0 bg-[url('/img/img.png')] bg-cover bg-center"></div>

        <!-- 3. Degradado Mejorado: Más sólido y largo para proteger el texto -->
        <!-- Cambiamos la opacidad y las posiciones: from-10% via-45% to-75% -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#1B396A] from-10% via-[#1B396A]/90 via-45% to-transparent to-75%"></div>

        <!-- Opcional: Una capa extra muy sutil para bajar el brillo general de la imagen -->
        <div class="absolute inset-0 bg-black/10"></div>

        <!-- 4. Contenedor del Contenido -->
        <div class="relative z-10 w-full max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 flex-grow flex flex-col justify-center py-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

                <!-- Columna Izquierda (Texto) -->
                <div class="lg:col-span-5 xl:col-span-4 text-white space-y-4">

                    <!-- Logos -->
                    <div class="flex items-center gap-4 mb-2">
                        <img src="/img/cenidet.png" alt="CENIDET"
                            class="h-9 md:h-10 w-auto object-contain bg-white p-1 rounded-md shadow-md">
                        <div class="h-8 w-px bg-white/30"></div>
                        <img src="/img/tecnm_white.png" alt="TecNM"
                            class="h-9 md:h-10 w-auto object-contain drop-shadow-md">
                    </div>

                    <!-- Título con gradiente y sombra más fuerte -->
                    <h1 class="text-3xl md:text-4xl xl:text-5xl font-extrabold leading-tight drop-shadow-[0_4px_4px_rgba(0,0,0,0.5)] pt-2">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-blue-50 to-[#F5A623]">
                            <slot name="title"></slot>
                        </span>
                    </h1>

                    <!-- Descripción con texto blanco puro y sombra fuerte -->
                    <div class="text-sm md:text-base text-white leading-relaxed text-justify drop-shadow-[0_2px_2px_rgba(0,0,0,0.8)] border-l-4 border-[#F5A623] pl-4">
                        <slot name="description"></slot>
                    </div>

                </div>

                <!-- Columna Derecha (Vacía para la imagen) -->
                <div class="hidden lg:block lg:col-span-7 xl:col-span-8"></div>

            </div>
        </div>

        <!-- 5. Barra Inferior de Estadísticas -->
        <div class="relative z-20 w-full bg-[#1B396A]/95 border-t border-white/10 backdrop-blur-md mt-auto">
            <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4 text-center text-white">
                    <div>
                        <p class="text-2xl md:text-3xl font-extrabold text-white">12</p>
                        <p class="text-xs md:text-sm text-gray-300 mt-1 font-medium">Programas de Posgrado</p>
                    </div>
                    <div>
                        <p class="text-2xl md:text-3xl font-extrabold text-white">11</p>
                        <p class="text-xs md:text-sm text-gray-300 mt-1 font-medium">Estudiantes Activos</p>
                    </div>
                    <div>
                        <p class="text-2xl md:text-3xl font-extrabold text-white">67</p>
                        <p class="text-xs md:text-sm text-gray-300 mt-1 font-medium">Docentes</p>
                    </div>
                    <div>
                        <p class="text-2xl md:text-3xl font-extrabold text-white">SNP</p>
                        <p class="text-xs md:text-sm text-gray-300 mt-1 font-medium">Reconocimiento CONAHCYT</p>
                    </div>
                </div>
            </div>
        </div>

    </section>
</template>
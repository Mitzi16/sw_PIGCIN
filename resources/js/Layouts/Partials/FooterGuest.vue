<script setup>
import BaseIcon from "@/Components/BaseIcon.vue";
import { 
    mdiChevronRight, 
    mdiPhone, 
    mdiEmailOutline, 
    mdiWeb, 
    mdiFacebook, 
    mdiTwitter, 
    mdiYoutube, 
    mdiInstagram 
} from "@mdi/js";
</script>

<template>
    <footer class="bg-[#1B396A] text-white">
        <!-- Sección Principal del Footer -->
        <!-- Reducido el padding vertical de py-12 lg:py-16 a py-10 lg:py-12 -->
        <div class="max-w-screen-xl mx-auto px-6 py-10 lg:py-12">
            <!-- Reducido el gap de 10 a 8 -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                
                <!-- 1. Columna: Logo e Información -->
                <div class="space-y-3">
                    <!-- Logo un poco más pequeño (h-14 en lugar de h-16) -->
                    <img src="/img/tecnm_white.png" alt="TecNM" class="h-14 w-auto object-contain">
                    <div class="space-y-1">
                        <!-- Título un poco más compacto (text-base en lugar de text-lg) -->
                        <p class="font-bold text-base leading-tight">Centro Nacional de Investigación y Desarrollo Tecnológico</p>
                        <p class="text-blue-100 text-xs sm:text-sm">Tecnológico Nacional de México</p>
                    </div>
                    <p class="text-xs sm:text-sm italic text-gray-300 pt-1">"Excelencia en Educación Tecnológica"</p>
                </div>

                <!-- 2. Columna: Enlaces Rápidos -->
                <div>
                    <!-- Título más pequeño y menos margen inferior (text-lg mb-4) -->
                    <h4 class="text-lg font-bold mb-4">Enlaces Rápidos</h4>
                    <!-- Reducido el espacio entre enlaces (space-y-2) -->
                    <ul class="space-y-2">
                        <li>
                            <a href="/" class="flex items-center text-sm text-gray-300 hover:text-white transition-colors group">
                                <BaseIcon :path="mdiChevronRight" size="18" class="mr-1.5 text-gray-400 group-hover:text-white transition-colors" />
                                Inicio
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-sm text-gray-300 hover:text-white transition-colors group">
                                <BaseIcon :path="mdiChevronRight" size="18" class="mr-1.5 text-gray-400 group-hover:text-white transition-colors" />
                                Programas Académicos
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-sm text-gray-300 hover:text-white transition-colors group">
                                <BaseIcon :path="mdiChevronRight" size="18" class="mr-1.5 text-gray-400 group-hover:text-white transition-colors" />
                                Servicios Escolares
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-sm text-gray-300 hover:text-white transition-colors group">
                                <BaseIcon :path="mdiChevronRight" size="18" class="mr-1.5 text-gray-400 group-hover:text-white transition-colors" />
                                Trámites
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-sm text-gray-300 hover:text-white transition-colors group">
                                <BaseIcon :path="mdiChevronRight" size="18" class="mr-1.5 text-gray-400 group-hover:text-white transition-colors" />
                                Contacto
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-sm text-gray-300 hover:text-white transition-colors group">
                                <BaseIcon :path="mdiChevronRight" size="18" class="mr-1.5 text-gray-400 group-hover:text-white transition-colors" />
                                Reconocimientos
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- 3. Columna: Nuestros Campus -->
                <div>
                    <h4 class="text-lg font-bold mb-4">Nuestros Campus</h4>
                    
                    <!-- Menos margen entre campus (mb-4) -->
                    <div class="mb-4">
                        <h5 class="font-bold text-sm mb-1">Campus Principal</h5>
                        <p class="text-xs sm:text-sm text-gray-300 leading-snug">
                            Interior Internado Palmira S/N, Col. Palmira,<br>
                            C.P. 62490, Cuernavaca, Morelos
                        </p>
                    </div>

                    <div>
                        <h5 class="font-bold text-sm mb-1">Campus Ing. Mecánica</h5>
                        <p class="text-xs sm:text-sm text-gray-300 leading-snug">
                            Prolongación Av. Palmira S/N, Col. Palmira,<br>
                            C.P. 62490, Cuernavaca, Morelos
                        </p>
                    </div>
                </div>

                <!-- 4. Columna: Contacto -->
                <div>
                    <h4 class="text-lg font-bold mb-4">Contacto</h4>
                    
                    <!-- Menos espacio entre items (space-y-3) -->
                    <ul class="space-y-3">
                        <!-- Teléfono -->
                        <li class="flex items-start">
                            <!-- Iconos un poco más pequeños (p-2 y size 18) -->
                            <div class="bg-white/10 p-2 rounded-lg mr-3 shrink-0">
                                <BaseIcon :path="mdiPhone" size="18" class="text-white" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-300">Teléfono</p>
                                <p class="font-semibold text-sm">(777) 362-7770</p>
                            </div>
                        </li>
                        
                        <!-- Correo -->
                        <li class="flex items-start">
                            <div class="bg-white/10 p-2 rounded-lg mr-3 shrink-0">
                                <BaseIcon :path="mdiEmailOutline" size="18" class="text-white" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-300">Correo electrónico</p>
                                <a href="mailto:contacto@cenidet.tecnm.mx" class="font-semibold text-sm hover:text-blue-200 transition-colors break-all">contacto@cenidet.tecnm.mx</a>
                            </div>
                        </li>

                        <!-- Sitio Web -->
                        <li class="flex items-start">
                            <div class="bg-white/10 p-2 rounded-lg mr-3 shrink-0">
                                <BaseIcon :path="mdiWeb" size="18" class="text-white" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-300">Sitio web oficial</p>
                                <a href="https://www.cenidet.tecnm.mx" target="_blank" class="font-semibold text-sm hover:text-blue-200 transition-colors">www.cenidet.tecnm.mx</a>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Barra Inferior: Copyright y Redes Sociales -->
        <!-- Reducido el padding de py-4 a py-3 -->
        <div class="bg-[#132D54] border-t border-white/10">
            <div class="max-w-screen-xl mx-auto px-6 py-3 flex flex-col md:flex-row justify-between items-center gap-3">
                
                <p class="text-xs sm:text-sm text-gray-300 text-center md:text-left">
                    &copy; 2026 TecNM - CENIDET. Todos los derechos reservados.
                </p>
                
                <div class="flex items-center space-x-3">
                    <a href="#" target="_blank" class="text-white hover:text-blue-300 transition-colors">
                        <BaseIcon :path="mdiFacebook" size="18" />
                    </a>
                    <a href="#" target="_blank" class="text-white hover:text-blue-300 transition-colors">
                        <BaseIcon :path="mdiTwitter" size="18" />
                    </a>
                    <a href="#" target="_blank" class="text-white hover:text-blue-300 transition-colors">
                        <BaseIcon :path="mdiYoutube" size="18" />
                    </a>
                    <a href="#" target="_blank" class="text-white hover:text-blue-300 transition-colors">
                        <BaseIcon :path="mdiInstagram" size="18" />
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>
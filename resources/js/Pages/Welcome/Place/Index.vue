<script setup>
import { 
    mdiMapMarker, 
    mdiEmailOutline, 
    mdiOpenInNew,
    mdiStar
} from "@mdi/js";
</script>

<template>
    <section id="lugar" class="relative py-16 bg-white overflow-hidden">
        
        <!-- ============================================== -->
        <!-- NUEVAS DECORACIONES CON IMAGEN -->
        <!-- ============================================== -->
        
        <!-- Decoración Esquina Inferior Izquierda -->
        <div class="absolute bottom-0 left-0 w-64 h-64 md:w-80 md:h-80 lg:w-[400px] lg:h-[400px] pointer-events-none z-0">
            <!-- Cambia el src por la ruta real de tu imagen -->
            <img src="/img/bg-decor-left.png" alt="Decoración fondo" class="w-full h-full object-contain object-left-bottom" />
        </div>

        <!-- Decoración Esquina Superior Derecha (Opcional, si tienes la imagen) -->
        <div class="absolute top-0 right-0 w-48 h-48 md:w-64 md:h-64 pointer-events-none z-0 opacity-80">
            <!-- Si no tienes una imagen específica para la derecha, puedes borrar este bloque -->
           
        </div>

        <!-- ============================================== -->

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
            
            <!-- Encabezado -->
            <div class="text-center mb-10">
                <div class="flex items-center justify-center gap-3 mb-2">
                    <svg class="w-8 h-8 text-[#1B396A]" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M15 19.5L9 21l-5.2-1.7c-.5-.2-.8-.6-.8-1.1V4.5c0-.8.7-1.4 1.5-1.1L9 5l6-1.5 5.2 1.7c.5.2.8.6.8 1.1v13.7c0 .8-.7 1.4-1.5 1.1L15 19.5zm-6-1.1l5-1.3V4.6L9 5.9v12.5zm7-12.7v12.5l4 1.3V6.8l-4-1.1z"/>
                    </svg>
                    <h2 class="text-[#1B396A] text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Visita nuestras instalaciones
                    </h2>
                </div>
                <div class="w-40 h-1 bg-[#1B396A] mx-auto my-3"></div>
                <p class="text-gray-600 text-base">
                    Conoce el campus donde se llevará a cabo el congreso.
                </p>
            </div>
            
            <!-- Contenedor Principal -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch max-w-5xl mx-auto">
                
                <!-- ================= COLUMNA IZQUIERDA: MAPA ================= -->
                <div class="lg:col-span-5 relative rounded-2xl overflow-hidden shadow-lg border border-gray-200 bg-white min-h-[320px]">
                    
                    <!-- Pestaña Superior "Ubicación del evento" -->
                    <div class="absolute top-0 left-0 z-20 bg-[#1B396A] text-white px-4 py-2 rounded-br-xl flex items-center gap-2 shadow-md">
                        <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiMapMarker" /></svg>
                        <span class="font-bold text-xs">Ubicación del evento</span>
                    </div>

                    <!-- Tarjeta flotante de información -->
                    <div class="absolute top-12 left-3 z-10 bg-white p-3 rounded-lg shadow-md border border-gray-100 w-56">
                        <h4 class="font-bold text-gray-800 text-xs">TecNM Cenidet</h4>
                        <p class="text-[10px] text-gray-600 mt-1 leading-tight">
                            Interior Internado, Palmira S/N, Palmira, 62490 Cuernavaca, Mor.
                        </p>
                        <div class="flex items-center gap-1 mt-1.5 text-[10px]">
                            <span class="font-bold text-gray-700">4.7</span>
                            <svg class="w-3 h-3 text-yellow-500" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiStar" /></svg>
                            <span class="text-blue-500">(102)</span>
                        </div>
                        <button class="mt-2 w-full bg-[#1B396A] text-white text-[10px] font-bold py-1.5 rounded-md flex items-center justify-center gap-1 hover:bg-[#132A4F] transition-colors">
                            <svg class="w-3 h-3" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiOpenInNew" /></svg>
                            Abrir en Maps
                        </button>
                    </div>

                    <!-- Iframe del Mapa -->
                    <div class="w-full h-full absolute inset-0">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1887.6125772172147!2d-99.22129268561007!3d18.877090098988177!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85cddf3a4772c8c3%3A0xca05f1ffbc24908!2sTecNM%20Cenidet!5e0!3m2!1ses-419!2smx!4v1723260987127!5m2!1ses-419!2smx"
                            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" class="w-full h-full">
                        </iframe>
                    </div>
                </div>
                
                <!-- ================= COLUMNA DERECHA: INFORMACIÓN ================= -->
                <div class="lg:col-span-7 relative bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden flex flex-col justify-between">
                    
                    <!-- Decoración superior izquierda del cuadro blanco (se mantiene igual, es el listón azul pequeño) -->
                    <div class="absolute top-0 left-0 w-24 h-12 bg-[#1B396A]" style="clip-path: polygon(0 0, 100% 0, 0 100%);"></div>
                    <div class="absolute top-2 left-3 text-white">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>

                    <!-- Contenido Principal -->
                    <div class="px-6 pt-10 pb-4 relative z-10">
                        <h3 class="text-lg md:text-xl font-bold text-[#1B396A] text-center mb-3 leading-tight">
                            Centro Nacional de Investigación y Desarrollo Tecnológico
                        </h3>
                        
                        <p class="text-justify text-gray-600 leading-relaxed text-xs md:text-sm">
                            El Tecnológico Nacional de México/Centro Nacional de Investigación y Desarrollo
                            Tecnológico (TecNM/Cenidet) está ubicado en Cuernavaca, Morelos, México, es un
                            Centro de excelencia en los posgrados que imparte y en la investigación que
                            desarrolla, siendo una institución colaborativa e interdisciplinaria con un
                            enfoque ingenieril en las áreas de electrónica, mecánica y ciencias
                            computacionales, con una amplia gama de aplicaciones en el sector tecnológico y
                            en la industria.
                        </p>
                    </div>
                    
                    <!-- Dirección y Contacto -->
                    <div class="border-t border-gray-200 px-6 py-4 relative z-10 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white/80 backdrop-blur-sm">
                        <!-- Dirección -->
                        <div class="flex gap-2 items-start">
                            <div class="w-8 h-8 shrink-0 bg-[#1B396A] rounded-md flex items-center justify-center text-white shadow-sm">
                                <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiMapMarker" /></svg>
                            </div>
                            <div>
                                <h4 class="text-[11px] font-extrabold text-[#1B396A] uppercase tracking-wide">Dirección</h4>
                                <p class="text-[11px] text-gray-600 mt-0.5 leading-snug">Interior Internado Palmira S/N, Palmira, 62493 Cuernavaca, Mor.</p>
                            </div>
                        </div>

                        <!-- Contacto -->
                        <div class="flex gap-2 items-start">
                            <div class="w-8 h-8 shrink-0 bg-[#1B396A] rounded-md flex items-center justify-center text-white shadow-sm">
                                <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" :d="mdiEmailOutline" /></svg>
                            </div>
                            <div>
                                <h4 class="text-[11px] font-extrabold text-[#1B396A] uppercase tracking-wide">Contacto</h4>
                                <p class="text-[11px] text-gray-600 mt-0.5 leading-snug">Email: contacto@cenidet.tecnm.mx</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
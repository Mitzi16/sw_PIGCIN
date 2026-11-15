<script setup>
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import BaseIcon from "@/Components/BaseIcon.vue";
import { mdiLogout, mdiPlus, mdiMinus, mdiMonitor, mdiCircleSmall } from "@mdi/js";

const props = defineProps({
    menu: { type: Array, required: true },
    isAsideMobileExpanded: Boolean,
    // isAsideLgActive ya no es necesario aquí si es estático, pero lo dejamos por si acaso
    isAsideLgActive: Boolean,
});

// Eliminamos "aside-lg-close-click" de los emits
const emit = defineEmits(["menu-click"]);

const menuClick = (event, item) => {
    emit("menu-click", event, item);
};

const activeSubmenu = ref(null);

const toggleSubmenu = (event, item) => {
    if (item.menu && item.menu.length > 0) {
        if (activeSubmenu.value === item.label) {
            activeSubmenu.value = null;
        } else {
            activeSubmenu.value = item.label;
        }
    }
    menuClick(event, item); 
};
</script>

<template>
    <!-- 'fixed inset-y-0 left-0' ya hace que sea estática -->
    <aside class="fixed inset-y-0 left-0 z-50 w-60 bg-[#15396F] flex flex-col shadow-2xl">

        <!-- Logo / Encabezado -->
        <div class="relative h-28 flex items-center justify-center border-b border-white/10 flex-shrink-0 pt-2">
            <div class="text-center flex flex-col items-center">
                <img src="/img/tecnm_white.png" alt="Logo TecNM" class="h-16 w-auto object-contain mb-1" />
                <span class="text-white font-bold tracking-widest text-sm mt-1">TECNM</span>
            </div>
            
            <!-- ELIMINADO: El botón de la "X" estaba aquí -->
        </div>

        <!-- Lista de Menú -->
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1 no-scrollbar">
            <template v-for="(item, index) in menu" :key="index">

                <div v-if="!item.isLogout">
                    
                    <!-- Elemento Activo -->
                    <Link v-if="item.route && route().current(item.route)" :href="route(item.route)"
                        class="flex items-center gap-3 px-3 py-3 rounded-lg bg-[#0f52ba] text-white font-semibold transition-colors shadow-sm mb-1"
                        @click="menuClick($event, item)">
                        <BaseIcon :path="item.icon || mdiMonitor" size="20" />
                        <span class="text-sm">{{ item.label }}</span>
                    </Link>

                    <!-- Elemento Inactivo -->
                    <Link v-else-if="item.route" :href="route(item.route)"
                        class="flex items-center gap-3 px-3 py-3 rounded-lg text-gray-200 hover:bg-white/10 hover:text-white transition-colors mb-1"
                        @click="menuClick($event, item)">
                        <BaseIcon :path="item.icon || mdiMonitor" size="20" />
                        <span class="text-sm">{{ item.label }}</span>
                    </Link>

                    <!-- Elementos con Submenú -->
                    <div v-else-if="item.label">
                        
                        <!-- Botón del Submenú -->
                        <div class="flex items-center justify-between px-3 py-3 rounded-lg text-gray-200 hover:bg-white/10 hover:text-white cursor-pointer transition-colors mb-1"
                            @click="toggleSubmenu($event, item)">
                            <div class="flex items-center gap-3">
                                <BaseIcon :path="item.icon || mdiMonitor" size="20" />
                                <span class="text-sm">{{ item.label }}</span>
                            </div>
                            <BaseIcon :path="activeSubmenu === item.label ? mdiMinus : mdiPlus" size="16" class="text-gray-400" />
                        </div>

                        <!-- Lista de Hijos -->
                        <div v-if="activeSubmenu === item.label && item.menu" class="pl-4 pr-2 space-y-1 mb-2 mt-1">
                            <Link v-for="(child, childIndex) in item.menu" :key="childIndex" 
                                :href="child.route ? route(child.route) : '#'"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/10 hover:text-white transition-colors"
                                :class="{'bg-white/5 text-white': child.route && route().current(child.route)}"
                                @click="menuClick($event, child)">
                                
                                <BaseIcon :path="child.icon || mdiCircleSmall" size="14" class="opacity-70" />
                                <span>{{ child.label }}</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Línea blanca separadora -->
                    <div v-if="item.label === 'Seguridad'" class="h-px bg-white/40 my-3 mx-1"></div>

                </div>
            </template>
        </div>

        <!-- Botón de Cerrar Sesión -->
        <div class="p-4 flex-shrink-0">
            <button @click="menuClick($event, { isLogout: true })"
                class="w-full flex items-center justify-center gap-2 bg-[#0f52ba] hover:bg-blue-600 text-white py-3 rounded-lg font-medium transition-colors shadow-sm">
                <BaseIcon :path="mdiLogout" size="20" />
                <span class="text-sm font-semibold">Cerrar Sesión</span>
            </button>
        </div>

    </aside>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
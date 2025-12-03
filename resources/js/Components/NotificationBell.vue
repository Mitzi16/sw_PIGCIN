<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    userId: { type: Number, required: true }
});

const notifications = ref([]);
const unreadCount = ref(0);
const showDropdown = ref(false);
const bellButton = ref(null); // Referencia al botón
const dropdownPosition = ref({ top: '0px', left: '0px' }); // Posición dinámica

// --- LÓGICA DE POSICIONAMIENTO Y UI ---

const updatePosition = () => {
    if (bellButton.value) {
        const rect = bellButton.value.getBoundingClientRect();
        // Calculamos para que aparezca debajo del botón
        // Ajustamos 'left' para que el menú no se salga de pantalla si está muy a la derecha
        const menuWidth = 320; // Ancho aproximado del menú (w-80)
        let leftPos = rect.left;
        
        // Si al abrirse a la derecha se sale de la pantalla, lo alineamos a la derecha del botón
        if (leftPos + menuWidth > window.innerWidth) {
             leftPos = rect.right - menuWidth;
        }

        dropdownPosition.value = {
            top: `${rect.bottom + 10}px`, // 10px de separación vertical
            left: `${leftPos}px`
        };
    }
};

const closeDropdown = (event) => {
    // Verificamos si el clic fue fuera del dropdown Y fuera del botón
    const dropdownEl = document.getElementById('notification-dropdown-content');
    if (
        showDropdown.value && 
        dropdownEl && 
        !dropdownEl.contains(event.target) && 
        !bellButton.value.contains(event.target)
    ) {
        showDropdown.value = false;
    }
};

const toggleDropdown = async () => {
    if (!showDropdown.value) {
        updatePosition(); // Recalcular posición antes de abrir
    }
    showDropdown.value = !showDropdown.value;
};

// Listener para cerrar al hacer scroll (para que el menú no se quede flotando solo)
const onScroll = () => {
    if (showDropdown.value) showDropdown.value = false;
};

// --- LÓGICA DE WEBSOCKETS (Mantenida igual) ---
const connectReverb = () => {
    if (window.Echo) {
        window.Echo.leave(`App.Models.User.${props.userId}`);
        window.Echo.private(`App.Models.User.${props.userId}`)
            .notification((notification) => {
                notifications.value.unshift({
                    id: Date.now(),
                    data: { message: notification.message || 'Nueva notificación' },
                    read_at: null
                });
                unreadCount.value++;
            });
    }
};

onMounted(() => {
    window.addEventListener('click', closeDropdown);
    window.addEventListener('resize', onScroll); // Cerrar si cambian tamaño pantalla
    window.addEventListener('scroll', onScroll, true); // Cerrar al hacer scroll
    setTimeout(() => connectReverb(), 1000);
});

onUnmounted(() => {
    window.removeEventListener('click', closeDropdown);
    window.removeEventListener('resize', onScroll);
    window.removeEventListener('scroll', onScroll, true);
    if (window.Echo) window.Echo.leave(`App.Models.User.${props.userId}`);
});
</script>

<template>
    <div class="inline-flex items-center dark:bg-gray-800">
        
        <button 
            ref="bellButton"
            @click.stop="toggleDropdown" 
            class="relative p-2 rounded-full text-gray-500 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-blue-400 dark:hover:bg-slate-700 transition-all focus:outline-none"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>

            <span v-if="unreadCount > 0" class="absolute top-1.5 right-1.5 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full border-2 border-white dark:border-slate-800 shadow-sm">
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <Teleport to="body">
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="transform opacity-0 scale-95 translate-y-2"
                enter-to-class="transform opacity-100 scale-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="transform opacity-100 scale-100 translate-y-0"
                leave-to-class="transform opacity-0 scale-95 translate-y-2"
            >
                <div 
                    v-if="showDropdown" 
                    id="notification-dropdown-content"
                    class="fixed w-80 bg-white dark:bg-slate-800 rounded-xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 ring-1 ring-black ring-opacity-5 z-[9999]"
                    :style="{ top: dropdownPosition.top, left: dropdownPosition.left }"
                >
                    <div class="px-4 py-3 flex justify-between items-center border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-slate-800">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Notificaciones</span>
                        <span v-if="unreadCount > 0" class="text-xs text-blue-600 cursor-pointer hover:underline" @click="unreadCount = 0">
                            Marcar leídas
                        </span>
                    </div>
                    
                    <div class="max-h-[60vh] overflow-y-auto dark:hover:bg-slate-700">
                         <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-gray-500 text-sm dark:hover:bg-slate-700">
                            Sin notificaciones nuevas
                        </div>
                        <div 
                            v-else
                            v-for="notif in notifications" 
                            :key="notif.id" 
                            class="px-4 py-3 border-b border-gray-50 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 cursor-pointer flex gap-3"
                        >
                            <div class="mt-1 w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                {{ notif.data.message }}
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </Teleport>
    </div>
</template>
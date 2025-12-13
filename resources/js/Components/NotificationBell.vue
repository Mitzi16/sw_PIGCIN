<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Modal from '@/Components/Modal.vue'; 
import axios from 'axios';

const props = defineProps({
    userId: { type: Number, required: true },
    userRoles: { type: Array, required: false, default: () => [] },
});

// --- 1. LÓGICA DE PERMISOS ---
const showBell = computed(() => {
    const allowedRoles = ['Editor', 'Revisor', 'Editor/Revisor'];
    return props.userRoles.some(role => allowedRoles.includes(role));
});

// --- 2. ESTADOS ---
const notifications = ref([]);
const unreadCount = ref(0);
const showDropdown = ref(false);
const bellButton = ref(null);
const dropdownPosition = ref({}); // Se inicia vacío

// Estados del Modal
const isModalOpen = ref(false);
const processing = ref(false);
const form = ref({
    recipient_id: '',
    message: ''
});

// --- 3. FUNCIONES DE API (Backend) ---
const fetchNotifications = async () => {
    try {
        const response = await axios.get('/notifications'); 
        notifications.value = response.data.notifications;
        unreadCount.value = notifications.value.filter(n => n.read_at === null).length;
    } catch (error) {
        console.error("Error cargando notificaciones:", error);
    }
};

const markAsReadLocal = async () => {
    if (unreadCount.value === 0) return;
    
    // Optimista
    const previousCount = unreadCount.value; 
    unreadCount.value = 0;
    notifications.value.forEach(n => {
        if (!n.read_at) n.read_at = new Date().toISOString();
    });

    try {
        await axios.post('/notifications/mark-all-read');
    } catch (error) {
        console.error("Error marcando leídas:", error);
        unreadCount.value = previousCount; // Revertir si falla
        alert("Error de conexión al marcar como leídas.");
    }
};

// --- 4. LÓGICA DE UI Y RESPONSIVIDAD ---

const updatePosition = () => {
    // [RESPONSIVIDAD] Si es pantalla pequeña (< 640px), NO calculamos posición.
    // Dejamos que CSS se encargue de centrarlo.
    if (window.innerWidth < 640) {
        dropdownPosition.value = {}; 
        return;
    }

    // Si es pantalla grande, calculamos coordenadas para anclarlo al botón
    if (bellButton.value) {
        const rect = bellButton.value.getBoundingClientRect();
        const menuWidth = 320;
        let leftPos = rect.left;
        
        // Evitar que se salga por la derecha
        if (leftPos + menuWidth > window.innerWidth) {
             leftPos = rect.right - menuWidth;
        }
        
        dropdownPosition.value = {
            top: `${rect.bottom + 10}px`,
            left: `${leftPos}px`
        };
    }
};

const closeDropdown = (event) => {
    const dropdownEl = document.getElementById('notification-dropdown-content');
    // Lógica para cerrar al hacer clic fuera (principalmente Desktop)
    if (showDropdown.value && dropdownEl && !dropdownEl.contains(event.target) && !bellButton.value.contains(event.target)) {
        showDropdown.value = false;
    }
};

const toggleDropdown = async () => {
    if (!showDropdown.value) {
        updatePosition();
    }
    showDropdown.value = !showDropdown.value;
};

const onScroll = (event) => {
   if (!showDropdown.value) return;

    // Obtenemos el elemento del dropdown
    const dropdownEl = document.getElementById('notification-dropdown-content');

    // Si el evento de scroll viene de DENTRO del dropdown (event.target),
    // o el target está contenido en el dropdown, NO hacemos nada (return).
    if (dropdownEl && event.target && dropdownEl.contains(event.target)) {
        return; 
    }

    // Solo cerramos si el scroll es de la ventana principal (body/html) y es PC/Tablet
    if (window.innerWidth >= 640) {
        showDropdown.value = false;
    }
};

// --- 5. LÓGICA DEL MODAL ---
const openComposeModal = (notification = null) => {
    showDropdown.value = false;
    isModalOpen.value = true;
    
    // Limpiar form
    form.value.recipient_id = '';
    form.value.message = '';

    // Autorelleno inteligente
    if (notification && notification.data) {
        const possibleEmails = 
            notification.data.reply_to ||       
            notification.data.sender_email ||   
            notification.data.email ||          
            notification.data.reviewer_emails;  

        if (possibleEmails) {
            form.value.recipient_id = Array.isArray(possibleEmails) 
                ? possibleEmails.join(', ') 
                : possibleEmails;
        }

        const title = notification.data.article_title || notification.data.title || notification.data.subject;
        if (title) {
            form.value.message = `Hola, respecto a: "${title}"... \n`;
        } else if (notification.data.message) {
            const shortMsg = notification.data.message.substring(0, 50); 
            form.value.message = `Hola, respecto a tu notificación: "${shortMsg}..." \n`;
        }
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.value = { recipient_id: '', message: '' };
    processing.value = false;
};

const sendMessage = async () => {
    const recipientsRaw = form.value.recipient_id.trim();
    if (!form.value.message.trim() || !recipientsRaw) {
        alert("Por favor completa el destinatario y el mensaje.");
        return;
    }

    processing.value = true;
    try {
        const response = await axios.post('/notifications/send', {
            recipient_id: recipientsRaw, 
            message: form.value.message
        });

        closeModal();

        if (response.data.warning) {
           alert(`Mensaje enviado, pero: ${response.data.warning}`);
        } else {
            alert('Notificación enviada con éxito.');
        }

    } catch (error) {
        console.error(error);
        alert("Error al enviar el mensaje. Verifica los datos o tu conexión.");
    } finally {
        processing.value = false;
    }
};

// --- 6. WEBSOCKETS (Reverb) ---
const connectReverb = () => {
    if (window.Echo) {
        window.Echo.leave(`App.Models.User.${props.userId}`);
        window.Echo.private(`App.Models.User.${props.userId}`)
            .notification((notification) => {
                const newNotification = {
                    id: notification.id, 
                    read_at: null,
                    created_at: new Date().toISOString(),
                    data: {
                        message: notification.message,
                        article_title: notification.article_title,
                        reviewer_names: notification.reviewer_names,
                        reviewer_emails: notification.reviewer_emails,
                        role_target: notification.role_target
                    }
                };
                notifications.value.unshift(newNotification);
                unreadCount.value++;
            });
    }
};

// --- 7. UTILIDADES ---
const getRoleBadgeStyle = (role) => {
    switch (role?.toLowerCase()) {
        case 'editor': return 'bg-purple-100 text-purple-700 border-purple-200';
        case 'revisor': return 'bg-teal-100 text-teal-700 border-teal-200';
        default: return 'bg-gray-100 text-gray-600 border-gray-200';
    }
};

// --- CICLO DE VIDA ---
onMounted(() => {
    window.addEventListener('click', closeDropdown);
    window.addEventListener('resize', () => { if(showDropdown.value) updatePosition(); });
    window.addEventListener('scroll', onScroll, true);
    
    fetchNotifications(); 
    setTimeout(() => connectReverb(), 1000);
});

onUnmounted(() => {
    window.removeEventListener('click', closeDropdown);
    window.removeEventListener('resize', () => {});
    window.removeEventListener('scroll', onScroll, true);
    if (window.Echo) window.Echo.leave(`App.Models.User.${props.userId}`);
});
</script>

<template>
    <div v-if="showBell" class="inline-flex items-center">
        
        <button 
            ref="bellButton"
            @click.stop="toggleDropdown" 
            class="relative p-2 rounded-full text-gray-500 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-blue-400 dark:hover:bg-slate-700 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/50"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>

            <span v-if="unreadCount > 0" class="absolute top-1.5 right-1.5 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full border-2 border-white dark:border-slate-800 shadow-sm">
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <Teleport to="body">
            
            <div 
                v-if="showDropdown" 
                class="fixed inset-0 bg-black/30 z-[9980] sm:hidden backdrop-blur-sm"
                @click="showDropdown = false"
            ></div>

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
                    class="
                        fixed z-[9990] 
                        bg-white dark:bg-slate-800 
                        rounded-xl shadow-2xl overflow-hidden 
                        border border-gray-100 dark:border-gray-700 
                        ring-1 ring-black ring-opacity-5
                        
                        /* --- ESTILOS RESPONSIVOS --- */
                        /* Móvil: Centrado superior con margen */
                        top-20 left-4 right-4 w-auto max-w-[400px] mx-auto
                        
                        /* Tablet/PC: Ancho fijo y posicionado dinámicamente */
                        sm:w-80 sm:mx-0
                    "
                    :style="dropdownPosition" 
                >
                    <div class="px-4 py-3 flex justify-between items-center border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-slate-800 backdrop-blur-sm sticky top-0 z-10">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Notificaciones</span>
                        <div class="flex items-center gap-3">
                            <button 
                                @click="openComposeModal()" 
                                class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 transition-colors p-1 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700"
                                title="Redactar nueva notificación"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <span v-if="unreadCount > 0" class="text-xs text-blue-600 cursor-pointer hover:underline font-medium" @click="markAsReadLocal">
                                Marcar Leídas
                            </span>
                        </div>
                    </div>
                    
                    <div class="max-h-[60vh] sm:max-h-[500px] overflow-y-auto custom-scrollbar overscroll-contain">
                         <div v-if="notifications.length === 0" class="px-4 py-10 flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <span class="text-sm">Sin notificaciones nuevas</span>
                        </div>
                        
                        <div 
                            v-else
                            v-for="notif in notifications" 
                            :key="notif.id" 
                            class="group px-4 py-3 border-b border-gray-50 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors relative"
                        >
                            <div class="flex gap-3">
                                <div v-if="!notif.read_at" class="mt-1.5 w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 shadow-sm animate-pulse"></div>
                                <div v-else class="mt-1.5 w-2 h-2 bg-transparent flex-shrink-0"></div> 

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center flex-wrap gap-2 mb-1">
                                        <span 
                                            v-if="notif.data.role_target" 
                                            class="px-1.5 py-0.5 text-[10px] uppercase font-bold tracking-wide rounded border shrink-0"
                                            :class="getRoleBadgeStyle(notif.data.role_target)"
                                        >
                                            {{ notif.data.role_target }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300 leading-snug mb-2 break-words">
                                        {{ notif.data.message }}
                                    </div>
                                    
                                    <button 
                                        @click="openComposeModal(notif)"
                                        class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors focus:outline-none flex items-center gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                        </svg>
                                        Responder
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </Teleport>

        <Modal :show="isModalOpen" maxWidth="md" @close="closeModal">
            <div class="p-4 sm:p-6 bg-white dark:bg-slate-800 text-gray-900 dark:text-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">
                        Nueva Notificación
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Para (Email)</label>
                        <input 
                            v-model="form.recipient_id"
                            type="text" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors text-sm py-2.5"
                            placeholder="Ej: usuario@empresa.com"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mensaje</label>
                        <textarea 
                            v-model="form.message"
                            rows="4" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors text-sm resize-none py-2.5"
                            placeholder="Escribe tu mensaje aquí..."
                        ></textarea>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-3">
                    <button 
                        @click="closeModal" 
                        class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-slate-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-slate-600 transition-colors"
                        :disabled="processing"
                    >
                        Cancelar
                    </button>
                    <button 
                        @click="sendMessage" 
                        class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm flex items-center justify-center transition-colors"
                        :class="{ 'opacity-75 cursor-not-allowed': processing }"
                        :disabled="processing"
                    >
                        <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ processing ? 'Enviando...' : 'Enviar' }}
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>
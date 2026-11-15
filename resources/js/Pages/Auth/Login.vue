<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import axios from 'axios' // 👈 1. IMPORTANTE: Importar Axios
import LayoutGuest from '@/Layouts/LayoutGuest.vue'
import FormValidationErrors from '@/Components/FormValidationErrors.vue'
import NotificationBarInCard from '@/Components/NotificationBarInCard.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import HeadLogo from '@/Components/HeadLogo.vue'
import { 
    mdiEmailOutline, 
    mdiLockOutline, 
    mdiEye, 
    mdiEyeOff, 
    mdiLoginVariant, 
    mdiAccountPlusOutline 
} from '@mdi/js'

const props = defineProps({
    canResetPassword: Boolean,
    status: {
        type: String,
        default: null
    }
})

const form = useForm({
    email: '',
    password: '',
    remember: []
})

const showPassword = ref(false);

// 👈 2. IMPORTANTE: La función submit ahora es "async"
const submit = async () => {
    try {
        // 3. Pedir la cookie de seguridad (CSRF) al backend antes de enviar el formulario
        // Aquí debes poner la URL exacta de tu backend (IP y puerto)
        await axios.get('http://192.168.100.111:8080/sanctum/csrf-cookie');

        // 4. Enviar el formulario con Inertia
        form
            .transform(data => ({
                ...data,
                remember: form.remember && form.remember.length ? 'on' : ''
            }))
            .post(route('login.store'), {
                onFinish: () => form.reset('password'),
            });
            
    } catch (error) {
        // Si falla la conexión con el backend, lo mostramos en consola
        console.error("Error al obtener el token CSRF:", error);
    }
}
</script>

<template>
    <HeadLogo title="Iniciar sesión" />
    <LayoutGuest>
        <!-- Contenedor Principal con fondo decorado -->
        <section class="relative min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 overflow-hidden">
            
            <!-- Decoraciones geométricas de fondo (Difuminados azules) -->
            <div class="absolute top-0 left-0 w-96 h-96 bg-[#1B396A] opacity-[0.03] pointer-events-none rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-[#1B396A] opacity-[0.03] pointer-events-none rounded-full blur-3xl translate-x-1/3 translate-y-1/3"></div>
            
            <!-- Marca de agua con tu imagen azul.png en el lado izquierdo -->
            <div class="absolute top-1/2 left-0 -translate-y-1/2 -translate-x-1/4 opacity-10 pointer-events-none">
                <img src="/img/azul.png" alt="Engrane de fondo" class="w-[500px] h-[500px] object-contain" />
            </div>

            <!-- CONTENEDOR PRINCIPAL DE LA TARJETA -->
            <div class="relative z-10 w-full max-w-3xl bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.1)] overflow-hidden grid grid-cols-1 lg:grid-cols-2">
                
                <!-- COLUMNA IZQUIERDA: FORMULARIO -->
                <div class="p-8 md:p-10 flex flex-col justify-center bg-white relative z-20">
                    
                    <!-- Encabezado -->
                    <div class="mb-6">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1B396A] tracking-tight">
                            Bienvenido a PIGCIN
                        </h1>
                        <!-- Línea decorativa -->
                        <div class="w-16 h-1.5 bg-[#1B396A] mt-3 mb-4 rounded-full"></div>
                        <p class="text-gray-500 text-sm">
                            Accede a tu cuenta para gestionar tus artículos.
                        </p>
                    </div>

                    <FormValidationErrors class="mb-4" />

                    <NotificationBarInCard v-if="status" color="info" class="mb-4">
                        {{ status }}
                    </NotificationBarInCard>

                    <form @submit.prevent="submit" class="space-y-4">
                        
                        <!-- Input: Correo Electrónico -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">Correo electrónico</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <BaseIcon :path="mdiEmailOutline" size="20" class="text-gray-400" />
                                </span>
                                <input 
                                    id="email" 
                                    name="email"
                                    v-model="form.email" 
                                    type="email" 
                                    required 
                                    placeholder="Ingresa tu correo electrónico" 
                                    class="w-full pl-10 pr-4 py-2.5 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#1B396A] focus:border-[#1B396A] outline-none transition-all text-sm" 
                                />
                            </div>
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>

                        <!-- Input: Contraseña -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">Contraseña</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <BaseIcon :path="mdiLockOutline" size="20" class="text-gray-400" />
                                </span>
                                <input 
                                    id="password"
                                    name="password"
                                    v-model="form.password" 
                                    :type="showPassword ? 'text' : 'password'" 
                                    required 
                                    placeholder="Ingresa tu contraseña" 
                                    class="w-full pl-10 pr-10 py-2.5 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#1B396A] focus:border-[#1B396A] outline-none transition-all text-sm" 
                                />
                                <button 
                                    type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-[#1B396A] transition-colors"
                                >
                                    <BaseIcon :path="showPassword ? mdiEyeOff : mdiEye" size="20" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                        </div>

                        <!-- Recordarme y Olvidé Contraseña -->
                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center justify-center">
                                    <input 
                                        type="checkbox" 
                                        v-model="form.remember" 
                                        value="remember"
                                        class="peer sr-only"
                                    />
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded-md bg-white peer-checked:bg-[#1B396A] peer-checked:border-[#1B396A] transition-colors flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="text-xs sm:text-sm text-gray-600 font-medium group-hover:text-[#1B396A] transition-colors">Recordarme</span>
                            </label>

                            <Link 
                                v-if="canResetPassword" 
                                :href="route('password.request')" 
                                class="text-xs sm:text-sm font-bold text-[#1B396A] hover:underline"
                            >
                                ¿Olvidaste tu contraseña?
                            </Link>
                        </div>

                        <!-- Botón Submit -->
                        <div class="pt-4">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="w-full relative group flex items-center justify-center gap-2 bg-[#1B396A] text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-[#132A4F] transition-all shadow-md overflow-hidden disabled:opacity-50"
                            >
                                <BaseIcon :path="mdiLoginVariant" size="20" class="relative z-10" />
                                <span class="relative z-10">Iniciar sesión</span>
                                <span class="absolute right-0 top-0 bottom-0 w-12 bg-white/10 transform skew-x-[-20deg] translate-x-4 pointer-events-none"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- COLUMNA DERECHA: IMAGEN DE FONDO -->
                <div class="hidden lg:block relative bg-[url('/img/biblioteca.png')] bg-cover bg-center overflow-hidden">
                    
                    <!-- Capa oscura semitransparente -->
                    <div class="absolute inset-0 bg-[#0B1A33]/75 z-0"></div>
                    
                    <!-- Contenido de la derecha -->
                    <div class="relative z-10 h-full flex flex-col items-center justify-center p-8 text-center text-white">
                        
                        <h2 class="text-2xl sm:text-3xl font-extrabold mb-4 leading-tight drop-shadow-lg">
                            ¿Aún no tienes <br> una cuenta?
                        </h2>
                        
                        <p class="text-blue-100 text-sm mb-10 px-4 leading-relaxed drop-shadow-md">
                            Regístrate y forma parte de nuestra comunidad académica.
                        </p>
                        
                        <Link 
                            href="/register"
                            class="group border-2 border-white/80 text-white px-8 py-3 rounded-full font-bold text-sm flex items-center gap-2 hover:bg-white hover:text-[#1B396A] transition-all duration-300 transform hover:scale-105 shadow-xl backdrop-blur-sm"
                        >
                            <BaseIcon :path="mdiAccountPlusOutline" size="20" class="transition-transform group-hover:scale-110" />
                            Crear cuenta
                        </Link>
                    </div>
                </div>

            </div>
        </section>
    </LayoutGuest>
</template>
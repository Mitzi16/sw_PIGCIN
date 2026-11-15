<script setup>
import { provide, computed, ref } from "vue";
import { useForm } from '@inertiajs/vue3';
import {
    mdiAccount, mdiLock, mdiTrashCan, 
    mdiChevronDown, mdiChevronUp, mdiCalendar
} from "@mdi/js";
import LayoutMain from "@/Layouts/LayoutMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import HeadLogo from "@/Components/HeadLogo.vue";

import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

const props = defineProps({
    name: 'Edit',    
    user: { type: Object, default: {}, required: true }
});

provide('user', props.user);

const formPhoto = useForm({
    _method: 'patch',
    id: props.user.id,
    photo: null,
});

const getImage = computed(() => {
    if (formPhoto.photo !== null) return URL.createObjectURL(formPhoto.photo);
    if (props.user.file) return props.user.file.path;
    return 'img/user.jpg';
});

const fileInput = ref(null);
const triggerFileInput = () => fileInput.value.click();
const handleFileInput = (event) => formPhoto.photo = event.target.files[0];

const deletePhoto = () => {
    formPhoto.delete(route('profile.destroyPhoto'), {
        onError: (error) => console.log('error: ', error),
    });
};

const userRoles = computed(() => props.user.roles.map(r => r.name).join(', ') || 'Usuario del sistema');

// Obtener la primera convocatoria asignada (asumiendo que el usuario se registró solo a una)
const activeCall = computed(() => {
    return props.user.calls && props.user.calls.length > 0 ? props.user.calls[0] : null;
});

const activeTab = ref('personal'); 
const toggleTab = (tab) => activeTab.value = activeTab.value === tab ? '' : tab;
</script>

<template>
    <LayoutMain>
        <HeadLogo title="Perfil" />

        <div class="max-w-[1100px] mx-auto bg-white rounded-xl shadow-2xl overflow-hidden flex flex-col md:flex-row relative border border-gray-200 mb-10">
            
            <a :href="route('dashboard')" class="absolute top-4 right-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full p-1.5 z-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd" />
                </svg>
            </a>

            <!-- ================= COLUMNA IZQUIERDA (Foto y Datos) ================= -->
            <div class="md:w-[32%] bg-[#f8fafc] relative flex flex-col items-center border-r border-gray-200">
                <div class="w-full h-44 bg-[#1e3a8a] relative overflow-hidden flex justify-center">
                    <svg class="absolute -top-10 -left-10 w-40 h-40 text-white opacity-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 15.5A3.5 3.5 0 1 1 15.5 12 3.5 3.5 0 0 1 12 15.5m7.43-2.53c.04-.32.07-.64.07-.97 0-.33-.03-.66-.07-1l2.11-1.63c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.31-.61-.22l-2.49 1c-.52-.39-1.06-.73-1.69-.98l-.37-2.65A.506.506 0 0 0 14 2h-4c-.25 0-.46.18-.5.42l-.37 2.65c-.63.25-1.17.59-1.69.98l-2.49-1c-.22-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64L4.57 11c-.04.34-.07.67-.07 1 0 .33.03.65.07.97l-2.11 1.66c-.19.15-.25.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1.01c.52.4 1.06.74 1.69.99l.37 2.65c.04.24.25.42.5.42h4c.25 0 .46-.18.5-.42l.37-2.65c.63-.26 1.17-.59 1.69-.99l2.49 1.01c.22.08.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.66Z"/></svg>
                    <svg class="absolute -bottom-10 -right-10 w-48 h-48 text-white opacity-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 15.5A3.5 3.5 0 1 1 15.5 12 3.5 3.5 0 0 1 12 15.5m7.43-2.53c.04-.32.07-.64.07-.97 0-.33-.03-.66-.07-1l2.11-1.63c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.31-.61-.22l-2.49 1c-.52-.39-1.06-.73-1.69-.98l-.37-2.65A.506.506 0 0 0 14 2h-4c-.25 0-.46.18-.5.42l-.37 2.65c-.63.25-1.17.59-1.69.98l-2.49-1c-.22-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64L4.57 11c-.04.34-.07.67-.07 1 0 .33.03.65.07.97l-2.11 1.66c-.19.15-.25.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1.01c.52.4 1.06.74 1.69.99l.37 2.65c.04.24.25.42.5.42h4c.25 0 .46-.18.5-.42l.37-2.65c.63-.26 1.17-.59 1.69-.99l2.49 1.01c.22.08.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.66Z"/></svg>
                </div>

                <div class="absolute top-20 w-36 h-36 rounded-full border-[6px] border-white bg-white shadow-sm overflow-hidden flex items-center justify-center">
                    <img v-if="getImage" :src="getImage" class="w-full h-full object-cover" alt="Avatar" />
                </div>

                <div class="mt-20 text-center w-full px-6">
                    <h2 class="text-[22px] font-bold text-[#1e3a8a]">{{ user.name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ userRoles }}</p>
                    <hr class="my-5 border-gray-300 w-3/4 mx-auto">

                    <input type="file" ref="fileInput" class="hidden" @change="handleFileInput" accept="image/*">
                    <div class="flex items-center justify-center gap-2 px-2">
                        <button @click="triggerFileInput" class="bg-[#1e3a8a] text-white px-3 py-2 rounded-lg text-sm font-semibold hover:bg-blue-900 transition flex items-center justify-center gap-2 flex-1 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M19.5 21a3 3 0 003-3v-4.5a3 3 0 00-3-3h-15a3 3 0 00-3 3V18a3 3 0 003 3h15zM1.5 10.146V6a3 3 0 013-3h5.379a2.25 2.25 0 011.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 013 3v1.146A4.483 4.483 0 0019.5 9h-15a4.483 4.483 0 00-3 1.146z" /></svg>
                            Elegir archivo
                        </button>
                        <div class="bg-white border border-gray-300 text-gray-600 px-3 py-2 rounded-lg text-sm w-20 truncate text-center">
                            {{ formPhoto.photo ? formPhoto.photo.name : 'Ning...' }}
                        </div>
                        <button @click="deletePhoto" class="bg-red-600 text-white p-2.5 rounded-lg hover:bg-red-700 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 013.878.512.75.75 0 11-.256 1.478l-.209-.035-1.005 13.07a3 3 0 01-2.991 2.77H8.084a3 3 0 01-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 01-.256-1.478A48.567 48.567 0 017.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 013.369 0c1.603.051 2.815 1.387 2.815 2.951zm-6.136-1.452a51.196 51.196 0 013.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 00-6 0v-.113c0-.794.609-1.428 1.364-1.452zm-.355 5.945a.75.75 0 10-1.5.058l.347 9a.75.75 0 101.499-.058l-.346-9zm5.48.058a.75.75 0 10-1.498-.058l-.347 9a.75.75 0 001.5.058l.345-9z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>

                    <div class="mt-5 bg-[#eff6ff] rounded-xl p-4 flex items-start gap-3 text-left mx-2 border border-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-[#1e3a8a] shrink-0"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 01.67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 11-.671-1.34l.041-.022zM12 9a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>
                        <div class="text-[13px] text-gray-700 leading-tight">
                            <span class="font-bold block mb-0.5">Formatos permitidos:</span>
                            JPG, PNG. Tamaño máx. 2 MB
                        </div>
                    </div>
                </div>
                
                <div class="absolute bottom-0 left-0 w-full h-2 bg-gradient-to-r from-[#1e3a8a] via-[#1e3a8a] to-yellow-500"></div>
            </div>

            <!-- ================= COLUMNA DERECHA (Formularios) ================= -->
            <div class="md:w-[68%] p-8 bg-white space-y-4">
                
                <!-- 👇 TARJETA DE LA CONVOCATORIA ASIGNADA 👇 -->
                <div v-if="activeCall" class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-2 text-blue-800 font-bold text-lg">
                        <BaseIcon :path="mdiCalendar" size="24" />
                        <h3>Convocatoria Activa</h3>
                    </div>
                    <h4 class="text-xl font-extrabold text-gray-800">{{ activeCall.title }}</h4>
                    <p class="text-gray-600 mt-2 text-sm">{{ activeCall.description }}</p>
                    <div class="mt-3 flex gap-4 text-sm font-medium text-gray-700">
                        <span v-if="activeCall.start_date"><strong class="text-blue-800">Inicio:</strong> {{ activeCall.start_date }}</span>
                        <span v-if="activeCall.end_date"><strong class="text-red-600">Cierre:</strong> {{ activeCall.end_date }}</span>
                    </div>
                </div>

                <!-- 1. Formulario Información Personal -->
                <UpdateProfileInformationForm />

                <!-- 2. Formulario Cambiar Contraseña -->
                <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <button @click="toggleTab('password')" class="w-full flex items-center justify-between bg-white group">
                        <div class="flex items-center flex-1">
                            <div class="bg-[#1e3a8a] text-white px-4 py-3 flex items-center gap-3 rounded-r-lg z-10">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm3.75 8.25v-3a3.75 3.75 0 10-7.5 0v3h7.5z" clip-rule="evenodd" /></svg>
                                <span class="font-bold text-[15px]">Cambiar contraseña</span>
                            </div>
                            <div class="h-[46px] w-6 bg-yellow-500 -skew-x-[20deg] -ml-2 z-0"></div>
                            <div class="flex-1 h-[2px] bg-gray-200 ml-2 mr-4"></div>
                        </div>
                        <div class="pr-4">
                            <component :is="activeTab === 'password' ? mdiChevronUp : mdiChevronDown" class="w-5 h-5 text-gray-500" />
                        </div>
                    </button>
                    <div v-show="activeTab === 'password'" class="p-6 border-t border-gray-100">
                        <p class="text-[13.5px] text-gray-500 mb-6">Actualice su contraseña para mantener la seguridad de su cuenta.</p>
                        <UpdatePasswordForm />
                    </div>
                </div>

                <!-- 3. Formulario Eliminar Cuenta -->
                <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <button @click="toggleTab('delete')" class="w-full flex items-center justify-between bg-white group">
                        <div class="flex items-center flex-1">
                            <div class="bg-[#1e3a8a] text-white px-4 py-3 flex items-center gap-3 rounded-r-lg z-10">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 013.878.512.75.75 0 11-.256 1.478l-.209-.035-1.005 13.07a3 3 0 01-2.991 2.77H8.084a3 3 0 01-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 01-.256-1.478A48.567 48.567 0 017.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 013.369 0c1.603.051 2.815 1.387 2.815 2.951zm-6.136-1.452a51.196 51.196 0 013.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 00-6 0v-.113c0-.794.609-1.428 1.364-1.452zm-.355 5.945a.75.75 0 10-1.5.058l.347 9a.75.75 0 101.499-.058l-.346-9zm5.48.058a.75.75 0 10-1.498-.058l-.347 9a.75.75 0 001.5.058l.345-9z" clip-rule="evenodd" /></svg>
                                <span class="font-bold text-[15px]">Eliminar cuenta</span>
                            </div>
                            <div class="h-[46px] w-6 bg-yellow-500 -skew-x-[20deg] -ml-2 z-0"></div>
                            <div class="flex-1 h-[2px] bg-gray-200 ml-2 mr-4"></div>
                        </div>
                        <div class="pr-4">
                            <component :is="activeTab === 'delete' ? mdiChevronUp : mdiChevronDown" class="w-5 h-5 text-gray-500" />
                        </div>
                    </button>
                    <div v-show="activeTab === 'delete'" class="p-6 border-t border-gray-100">
                        <p class="text-[13.5px] text-gray-500 mb-6">Elimine su cuenta del sistema de forma permanente.</p>
                        <DeleteUserForm />
                    </div>
                </div>

            </div>
        </div>
    </LayoutMain>
</template>
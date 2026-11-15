<script setup>
import { mdiForwardburger, mdiBackburger } from "@mdi/js"; // Quitamos mdiMenu
import { ref, computed } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";

import { getMenu } from "@/menuAside.js";
import menuNavBar from "@/menuNavBar.js";
import { useMainStore } from "@/stores/main.js";
import { useStyleStore } from "@/stores/style.js";
import BaseIcon from "@/Components/BaseIcon.vue";
import FormControl from "@/Components/FormControl.vue";
import NavBar from "@/Components/NavBar.vue";
import NavBarItemPlain from "@/Components/NavBarItemPlain.vue";
import AsideMenu from "@/Components/AsideMenu.vue";
import FooterBar from "@/Components/FooterBar.vue";
import NotificationBell from '@/Components/NotificationBell.vue';

const userRoles = computed(() => {
    const roles = usePage().props.auth.user?.roles || [];
    return roles.map(role => role.name);
});

const menuAside = computed(() => getMenu(userRoles.value));

useMainStore().setUser({
  name: "",
  email: "",
  avatar:
    "https://avatars.dicebear.com/api/avataaars/example.svg?options[top][]=shortHair&options[accessoriesChance]=93",
});

// 1. Cambiamos 'xl:pl-60' por 'lg:pl-60' para que en laptops también respete el menú
const layoutAsidePadding = "lg:pl-60"; 

const styleStore = useStyleStore();

// Solo mantenemos el estado para móviles
const isAsideMobileExpanded = ref(false);
// 2. Eliminamos isAsideLgActive porque ya no se puede cerrar en PC

router.on("navigate", () => {
  isAsideMobileExpanded.value = false;
});

const menuClick = (event, item) => {
  if (item.isToggleLightDark) {
    styleStore.setDarkMode();
  }

  if (item.isLogout) {
    router.post(route("logout"));
  }
};
</script>

<template>
  <div
    :class="{
      dark: styleStore.darkMode,
      'overflow-hidden lg:overflow-visible': isAsideMobileExpanded,
    }"
  >
    <div
      :class="[layoutAsidePadding, { 'ml-60 lg:ml-0': isAsideMobileExpanded }]"
      class="pt-14 min-h-screen w-full transition-position lg:w-auto bg-gray-50 dark:bg-slate-800 dark:text-slate-100"
    >
      <NavBar
        :menu="menuNavBar"
        :class="[
          layoutAsidePadding,
          { 'ml-60 lg:ml-0': isAsideMobileExpanded },
          'z-40',
        ]"
        @menu-click="menuClick"
      >
        <!-- Botón de menú para móviles (se mantiene) -->
        <NavBarItemPlain
          display="flex lg:hidden"
          @click.prevent="isAsideMobileExpanded = !isAsideMobileExpanded"
        >
          <BaseIcon
            :path="isAsideMobileExpanded ? mdiBackburger : mdiForwardburger"
            size="24"
          />
        </NavBarItemPlain>

        <!-- 3. ELIMINAMOS EL BOTÓN DE HAMBURGUESA PARA LG/XL QUE ESTABA AQUÍ -->

        <NavBarItemPlain use-margin class="mr-auto"> 
           <NotificationBell :user-id="$page.props.auth.user.id" :user-roles="userRoles" />
        </NavBarItemPlain>

      </NavBar>

      <!-- 4. Limpiamos las propiedades que controlaban el cierre del menú -->
      <AsideMenu
        :is-aside-mobile-expanded="isAsideMobileExpanded"
        :menu="menuAside"
        @menu-click="menuClick"
      />
      
      <slot />
      
      <FooterBar>
        Get more with
        <a
          href="https://tailwind-vue.justboil.me/"
          target="_blank"
          class="text-blue-600"
        >Premium version</a>
      </FooterBar>
    </div>
  </div>
</template>
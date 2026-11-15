<script setup>
import { ref } from "vue";
import { mdiClose, mdiDotsVertical } from "@mdi/js";
import { containerMaxW } from "@/config.js";
import BaseIcon from "@/Components/BaseIcon.vue";
import NavBarMenuList from "@/Components/NavBarMenuList.vue";
import NavBarItemPlain from "@/Components/NavBarItemPlain.vue";

defineProps({ menu: { type: Array, required: true } });
const emit = defineEmits(["menu-click"]);
const menuClick = (event, item) => { emit("menu-click", event, item); };
const isMenuNavBarActive = ref(false);
</script>

<template>
  <!-- QUITAMOS 'fixed', 'top-0', 'w-screen' y dejamos que sea un bloque estático -->
  <nav class="bg-white h-14 w-full dark:bg-slate-900 border-b border-gray-100 dark:border-slate-700">
    <div class="flex lg:items-stretch h-full w-full px-4">
      <div class="flex flex-1 items-center h-full">
        <slot />
      </div>
      <div class="flex-none items-center flex h-14 lg:hidden">
        <NavBarItemPlain @click.prevent="isMenuNavBarActive = !isMenuNavBarActive">
          <BaseIcon :path="isMenuNavBarActive ? mdiClose : mdiDotsVertical" size="24" />
        </NavBarItemPlain>
      </div>
      <div
        class="max-h-screen-menu overflow-y-auto lg:overflow-visible absolute w-screen top-14 left-0 bg-white shadow-lg lg:w-auto lg:flex lg:static lg:shadow-none dark:bg-slate-800"
        :class="[isMenuNavBarActive ? 'block' : 'hidden']"
      >
        <NavBarMenuList :menu="menu" @menu-click="menuClick" />
      </div>
    </div>
  </nav>
</template>
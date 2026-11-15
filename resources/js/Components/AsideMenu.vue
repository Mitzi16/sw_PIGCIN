<script setup>
import AsideMenuLayer from "@/Components/AsideMenuLayer.vue";
import OverlayLayer from "@/Components/OverlayLayer.vue";

defineProps({
  menu: { type: Array, required: true },
  isAsideMobileExpanded: Boolean,
});

const emit = defineEmits(["menu-click"]);

const menuClick = (event, item) => {
  emit("menu-click", event, item);
};
</script>

<template>
  <AsideMenuLayer
    :menu="menu"
    :class="[
      isAsideMobileExpanded ? 'left-0' : '-left-60 lg:left-0'
    ]"
    @menu-click="menuClick"
  />
  
  <OverlayLayer
    v-show="isAsideMobileExpanded"
    z-index="z-30"
    @overlay-click="$emit('update:isAsideMobileExpanded', false)" 
  />
</template>
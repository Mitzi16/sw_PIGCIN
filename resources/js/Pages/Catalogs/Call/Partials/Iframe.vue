<script setup>
const props = defineProps({
    url: {
        type: String
    }
})
</script>

<template>
    <div class="w-full flex justify-center">
        <iframe :src="url" class="w-full md:w-2/3 h-96 md:h-[400px]" />
    </div>
</template>
<script setup>
import { onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const page = usePage()

onMounted(() => {
  const alert = page.props.alert
  if (alert) {
    Swal.fire({
      title: alert.title,
      text: alert.text,
      icon: alert.icon,
      confirmButtonText: 'Entendido'
    }).then(() => {
      router.visit(alert.redirectTo)
    })
  }
})
</script>

<template>
  <!-- Se puede dejar vacío o poner un spinner -->
  <div></div>
</template>

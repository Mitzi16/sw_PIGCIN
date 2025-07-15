<script setup>
import LayoutMain from '@/Layouts/LayoutMain.vue'
import HeadLogo from '@/Components/HeadLogo.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import { mdiContentSave, mdiClose, mdiPlus } from '@mdi/js'
import { Link, useForm } from '@inertiajs/vue3'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'

const props = defineProps({
  title: String,
  routeName: String,
})

const form = useForm({ name: null })

const saveForm = () => {
  form.post(route(`${props.routeName}store`))
}
</script>

<template>
  <HeadLogo :title="title" />
  <LayoutMain>
    <SectionTitleLineWithButton :icon="mdiPlus" :title="title" main>
      <Link :href="route(`${routeName}index`)">
        ❌
      </Link>
    </SectionTitleLineWithButton>

    <CardBox isForm @submit.prevent="saveForm">
      <FormField label="Nombre:" :required="true" help="Nombre del tipo" :error="form.errors.name">
        <FormControl v-model="form.name" placeholder="..." />
      </FormField>

      <template #footer>
        <BaseButtons>
          <BaseButton :routeName="`${routeName}index`" :icon="mdiClose" color="white" label="Cancelar" />
          <BaseButton @click="saveForm" :icon="mdiContentSave" type="submit" color="success" label="Guardar" />
        </BaseButtons>
      </template>
    </CardBox>
  </LayoutMain>
</template>

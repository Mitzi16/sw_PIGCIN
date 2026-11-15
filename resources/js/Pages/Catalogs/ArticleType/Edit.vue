<script setup>
import LayoutMain from '@/Layouts/LayoutMain.vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import { mdiPencil, mdiTrashCan, mdiContentSave, mdiClose, mdiPlus } from '@mdi/js'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

const props = defineProps({
    title: String,
    routeName: String,
    articleType: Object,
})

const form = useForm({ ...props.articleType })

const saveForm = () => {
    form.put(route(`${props.routeName}update`, props.articleType.id), {
        onError: (errors) => console.log("Errores de validación:", errors)
    })
}

const deleteForm = () => {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta acción no se puede revertir',
        icon: 'warning',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        cancelButtonColor: '#6b7280',
        confirmButtonColor: '#E02424',
        confirmButtonText: 'Sí, eliminar',
    }).then((res) => {
        if (res.isConfirmed) {
            form.delete(route(`${props.routeName}destroy`, props.articleType.id))
        }
    })
}

// Límites para las rúbricas
const addItem = (rubricIndex) => {
    if (form.rubrics[rubricIndex].items.length < 10) {
        form.rubrics[rubricIndex].items.push({ question: '', answers: [] })
    }
}
const addAnswer = (rubricIndex, itemIndex) => {
    if (form.rubrics[rubricIndex].items[itemIndex].answers.length < 5) {
        form.rubrics[rubricIndex].items[itemIndex].answers.push({ text: '', score: 0 })
    }
}
const removeItem = (rubricIndex, itemIndex) => {
    form.rubrics[rubricIndex].items.splice(itemIndex, 1)
}
const removeAnswer = (rubricIndex, itemIndex, answerIndex) => {
    form.rubrics[rubricIndex].items[itemIndex].answers.splice(answerIndex, 1)
}
</script>

<template>
    <div class="bg-slate-50 min-h-screen relative overflow-hidden">
        <!-- Decoración de fondo -->
        <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-blue-50 to-transparent -z-10"></div>

        <Head :title="title" />
        <LayoutMain>

            <form @submit.prevent="saveForm" class="max-w-5xl mx-auto pt-6 pb-24 relative z-10">
                
                <!-- CONTENEDOR ÚNICO -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">

                    <!-- Header Principal -->
                    <div class="bg-[#15396F] px-6 py-3 flex items-center justify-between text-white">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path :d="mdiPencil" />
                            </svg>
                            <h2 class="text-sm font-bold">{{ title }}</h2>
                        </div>
                        <Link :href="route(`${routeName}index`)" class="text-white hover:text-red-400 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </Link>
                    </div>

                    <div class="p-6 space-y-8">

                        <!-- SECCIÓN 1: DATOS BÁSICOS -->
                        <div>
                            <h3 class="text-base font-bold text-[#15396F] mb-4 flex items-center gap-2 border-b border-gray-200 pb-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" /></svg>
                                Datos del tipo de artículo
                            </h3>

                            <div class="space-y-4">
                                <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start md:items-center">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2">
                                        <span class="text-red-500">*</span> Nombre del Tipo:
                                    </label>
                                    <div class="w-full md:w-3/4">
                                        <input v-model="form.name" type="text" placeholder="Ej: Artículo Científico, Ensayo, etc."
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50" required>
                                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 2: RÚBRICA -->
                        <div v-for="(rubric, rubricIndex) in form.rubrics" :key="rubricIndex">
                            <h3 class="text-base font-bold text-[#15396F] mb-4 flex items-center gap-2 border-b border-gray-200 pb-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                                Configuración de la Rúbrica
                            </h3>

                            <div class="space-y-4">
                                <!-- Título de la Rúbrica -->
                                <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start md:items-center">
                                    <label class="w-full md:w-1/4 text-sm font-bold text-gray-800 mt-2">
                                        <span class="text-red-500">*</span> Título de la Rúbrica:
                                    </label>
                                    <div class="w-full md:w-3/4">
                                        <input v-model="rubric.title" type="text" placeholder="Ej: Criterios de Evaluación General"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-gray-50" required>
                                        <div v-if="form.errors[`rubrics.${rubricIndex}.title`]" class="text-red-500 text-xs mt-1">{{ form.errors[`rubrics.${rubricIndex}.title`] }}</div>
                                    </div>
                                </div>

                                <!-- PREGUNTAS -->
                                <div class="mt-8">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="text-sm font-bold text-[#15396F]">Preguntas / Criterios</h4>
                                        <div>
                                            <button type="button" v-if="rubric.items.length < 10" @click="addItem(rubricIndex)" 
                                                class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-[#0d6efd] hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path :d="mdiPlus" /></svg>
                                                Agregar Pregunta
                                            </button>
                                            <p v-else class="text-xs text-red-500 italic font-medium">Máximo 10 preguntas alcanzado</p>
                                        </div>
                                    </div>

                                    <div v-for="(item, itemIndex) in rubric.items" :key="itemIndex" class="mb-6 p-5 bg-gray-50 border border-gray-200 rounded-xl relative">
                                        
                                        <!-- Botón Eliminar Pregunta -->
                                        <button type="button" @click="removeItem(rubricIndex, itemIndex)" 
                                            class="absolute top-4 right-4 bg-red-100 text-red-600 hover:bg-red-200 p-2 rounded-lg transition-colors" title="Eliminar Pregunta">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path :d="mdiTrashCan" /></svg>
                                        </button>

                                        <h5 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-[#15396F] text-white flex items-center justify-center text-xs">{{ itemIndex + 1 }}</span>
                                            Pregunta
                                        </h5>

                                        <div class="flex flex-col md:flex-row gap-2 md:gap-6 items-start md:items-center mb-6 pr-12">
                                            <label class="w-full md:w-1/4 text-sm font-bold text-gray-800">Texto de la pregunta:</label>
                                            <div class="w-full md:w-3/4">
                                                <input v-model="item.question" type="text" placeholder="Ej: ¿El artículo presenta una metodología clara?"
                                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-white" required>
                                                <div v-if="form.errors[`rubrics.${rubricIndex}.items.${itemIndex}.question`]" class="text-red-500 text-xs mt-1">{{ form.errors[`rubrics.${rubricIndex}.items.${itemIndex}.question`] }}</div>
                                            </div>
                                        </div>

                                        <!-- RESPUESTAS -->
                                        <div class="pl-0 md:pl-8 border-l-0 md:border-l-2 border-gray-300">
                                            <div class="flex justify-between items-center mb-3">
                                                <span class="text-xs font-bold text-gray-600">Opciones de Respuesta (y Puntaje)</span>
                                                <div>
                                                    <button type="button" v-if="item.answers.length < 5" @click="addAnswer(rubricIndex, itemIndex)" 
                                                        class="flex items-center gap-1 px-3 py-1.5 text-[11px] font-bold text-[#0d6efd] bg-blue-50 hover:bg-blue-100 rounded-md transition-colors">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path :d="mdiPlus" /></svg>
                                                        Añadir respuesta
                                                    </button>
                                                    <p v-else class="text-[11px] text-red-500 italic">Máximo 5 respuestas</p>
                                                </div>
                                            </div>

                                            <div v-for="(answer, answerIndex) in item.answers" :key="answerIndex" class="flex flex-col md:flex-row gap-3 mb-2 items-end">
                                                <div class="flex-1 w-full">
                                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Respuesta {{ answerIndex + 1 }}:</label>
                                                    <input v-model="answer.text" type="text" placeholder="Ej: Excelente, Bueno, Regular..."
                                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-white" required>
                                                    <div v-if="form.errors[`rubrics.${rubricIndex}.items.${itemIndex}.answers.${answerIndex}.text`]" class="text-red-500 text-[10px] mt-1">{{ form.errors[`rubrics.${rubricIndex}.items.${itemIndex}.answers.${answerIndex}.text`] }}</div>
                                                </div>
                                                <div class="w-full md:w-24">
                                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Puntaje:</label>
                                                    <input v-model="answer.score" type="number" step="0.1" placeholder="0.0"
                                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#15396F] text-sm text-gray-600 bg-white" required>
                                                    <div v-if="form.errors[`rubrics.${rubricIndex}.items.${itemIndex}.answers.${answerIndex}.score`]" class="text-red-500 text-[10px] mt-1">{{ form.errors[`rubrics.${rubricIndex}.items.${itemIndex}.answers.${answerIndex}.score`] }}</div>
                                                </div>
                                                <button type="button" @click="removeAnswer(rubricIndex, itemIndex, answerIndex)" 
                                                    class="bg-red-50 text-red-500 hover:bg-red-100 p-2.5 rounded-lg transition-colors h-[38px] flex items-center justify-center" title="Eliminar Respuesta">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path :d="mdiTrashCan" /></svg>
                                                </button>
                                            </div>
                                            
                                            <div v-if="item.answers.length === 0" class="text-xs text-gray-500 italic mt-2">
                                                No hay opciones de respuesta agregadas.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div v-if="rubric.items.length === 0" class="text-sm text-gray-500 italic text-center py-6 border-2 border-dashed border-gray-300 rounded-xl">
                                        Aún no has agregado preguntas a esta rúbrica.
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <!-- BOTONES INFERIORES -->
                <div class="flex justify-between items-center mt-8 relative">
                    <div class="flex gap-2">
                        <Link :href="route(`${routeName}index`)"
                            class="px-6 py-2.5 text-sm font-bold text-[#15396F] bg-white border border-gray-300 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 shadow-sm z-10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Cancelar
                        </Link>
                        
                        <!-- Botón de eliminar -->
                        <button type="button" @click="deleteForm"
                            class="px-6 py-2.5 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-full transition-colors flex items-center gap-2 shadow-sm z-10">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path :d="mdiTrashCan" /></svg>
                            Eliminar
                        </button>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="px-8 py-2.5 text-sm font-bold text-white bg-[#15396F] hover:bg-[#0f2853] rounded-full transition-colors flex items-center gap-2 shadow-md disabled:opacity-50 z-10">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path :d="mdiContentSave" /></svg>
                        Guardar Cambios
                    </button>
                </div>

            </form>
        </LayoutMain>
    </div>
</template>
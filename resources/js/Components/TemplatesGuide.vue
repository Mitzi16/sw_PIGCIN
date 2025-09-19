<script setup>
const articleTemplates = [
  {
    title: "Artículo Congreso (4-6 hojas máximo)",
    description: `Describir en forma breve pero completa los resultados obtenidos de sus desarrollos y/o 
    innovaciones tecnológicas, aplicaciones del conocimiento, producto de trabajos de innovatec, 
    resultados del verano científico, trabajos de estadías y/o residencias profesionales, 
    proyectos finales de curso. Este rubro es bastante sugerido para estudiantes de licenciatura, 
    con apoyo de los docentes. Los trabajos deben tener entre 4 y 6 páginas, máximo 6 autores.`,
    downloads: [
      { label: "Word", link: "/download/ArtículoCongreso(4-6 hojas).docx", filename: "ArtículoCongreso(4-6 hojas).docx", type: "file" },
      { label: "LaTeX", link: "/download/plantilla_latex_4to_CITCA.zip", filename: "plantilla_latex_4to_CITCA.zip", type: "file" }
    ]
  },
  {
    title: "Artículo Revista",
    description: `Describir en forma completa con el rigor teórico y matemático necesario para la obtención 
    de resultados de la investigación científica que aporte la generación de conocimiento. 
    Producto de proyectos de investigación, tesis de posgrado, etc. Sugerido para investigadores, 
    docentes-investigadores y estudiantes de posgrado. Más de 6 páginas, con límites de autores.`,
    downloads: [
      { label: "Word", link: "/download/office_2-0.zip", filename: "office_2-0.zip", type: "file" },
      { label: "LaTeX", link: "/download/latex-1.4.zip", filename: "latex-1.4.zip", type: "file" }
    ]
  }
];

const posterTemplates = [
  {
    title: "Póster Científico",
    description: `Presentar a una audiencia especializada de manera clara, concisa y visualmente atractiva 
    los resultados, metodologías o avances de una investigación científica.`,
    downloads: [
      { label: "PPT", link: "/download/poster_cientifico_4to_CITCA.pptx", filename: "poster_cientifico_4to_CITCA.pptx", type: "file" },
      { label: "Canva", link: "https://www.canva.com/design/DAGfAJcZYcc/BS61YSpbSKpfPUdlMPByMg/view?utm_content=DAGfAJcZYcc&utm_campaign=designshare&utm_medium=link&utm_source=publishsharelink&mode=preview", type: "link" }
    ]
  },
  {
    title: "Póster de Divulgación",
    description: `Comunicar información científica o técnica de manera accesible y comprensible para 
    un público no especializado. Busca educar, informar y despertar interés en un tema específico.`,
    downloads: [
      { label: "PPT", link: "/download/poster_divulgacion_4to_CITCA.pptx", filename: "poster_divulgacion_4to_CITCA.pptx", type: "file" },
      { label: "Canva", link: "https://www.canva.com/design/DAGe_2PZq_Y/HHOFJMDlgM7DYr_Jrq3eww/view?utm_content=DAGe_2PZq_Y&utm_campaign=designshare&utm_medium=link&utm_source=publishsharelink&mode=preview", type: "link" }
    ]
  }
];
</script>

<template>
  <div class="space-y-10">
    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">Plantillas para Contribuciones</h1>
    <!-- Artículos -->
    <section>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Artículos</h2>
      <div class="grid gap-6 md:grid-cols-2">
        <div
          v-for="(article, index) in articleTemplates"
          :key="index"
          class="p-6 rounded-2xl shadow bg-white dark:bg-gray-800"
        >
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
            {{ article.title }}
          </h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            {{ article.description }}
          </p>

          <div class="flex flex-wrap gap-3">
            <template v-for="(dl, i) in article.downloads" :key="i">
              <a
                v-if="dl.type === 'file'"
                :href="dl.link"
                :download="dl.filename"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition"
              >
                Descargar {{ dl.label }}
              </a>
              <a
                v-else
                :href="dl.link"
                target="_blank"
                class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition"
              >
                Abrir en {{ dl.label }}
              </a>
            </template>
          </div>
        </div>
      </div>
    </section>

    <!-- Pósters -->
    <section>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Pósters</h2>
      <div class="grid gap-6 md:grid-cols-2">
        <div
          v-for="(poster, index) in posterTemplates"
          :key="index"
          class="p-6 rounded-2xl shadow bg-white dark:bg-gray-800"
        >
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
            {{ poster.title }}
          </h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            {{ poster.description }}
          </p>

          <div class="flex flex-wrap gap-3">
            <template v-for="(dl, i) in poster.downloads" :key="i">
              <a
                v-if="dl.type === 'file'"
                :href="dl.link"
                :download="dl.filename"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition"
              >
                Descargar {{ dl.label }}
              </a>
              <a
                v-else
                :href="dl.link"
                target="_blank"
                class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition"
              >
                Abrir en {{ dl.label }}
              </a>
            </template>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
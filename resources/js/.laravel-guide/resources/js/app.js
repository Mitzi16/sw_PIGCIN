import "../css/main.css";

// 1. Cargar configuración base (axios, etc)
import './bootstrap'; 

// 2. IMPORTANTE: Cargar la configuración de Echo/Reverb
// Si esta línea no está, window.Echo nunca existirá.
import './echo'; 

import { createPinia } from "pinia";
import { useStyleStore } from "@/stores/style.js";
import { darkModeKey, styleKey } from "@/config.js";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";

const appName =
  window.document.getElementsByTagName("title")[0]?.innerText || "Laravel";

const pinia = createPinia();

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob("./Pages/**/*.vue")
    ),
  setup({ el, App, props, plugin }) {
    // Nota: Aquí no necesitas importar echo, ya se importó arriba globalmente
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(pinia)
      .use(ZiggyVue, Ziggy)
      .mount(el);
  },
  progress: {
    color: "#4B5563",
  },
});

const styleStore = useStyleStore(pinia);

/* App style */
styleStore.setStyle(localStorage[styleKey] ?? "basic");

/* Dark mode */
if (
  (!localStorage[darkModeKey] &&
    window.matchMedia("(prefers-color-scheme: dark)").matches) ||
  localStorage[darkModeKey] === "1"
) {
  styleStore.setDarkMode(true);
}
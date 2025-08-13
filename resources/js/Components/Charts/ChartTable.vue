
<template>
    <div class="container mx-auto mt-6 bg-white dark:bg-gray-900 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
            {{ title }}
        </h2>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th
                        v-for="header in headers"
                        :key="header"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ header.label }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                    v-for="(row, index) in rows"
                    :key="index"
                    class="hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <td
                        v-for="header in headers"
                        :key="header.key"
                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200"
                    >
                        {{ row[header.key] }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        default: 'Datos de la Gráfica',
    },
    // headers: [{ label: "Municipio", key: "Municipio" }, { label: "Incidencias", key: "Incidencias" }]
    headers: {
        type: Array,
        required: true,
    },
    rows: {
        type: Array,
        required: true,
    },
});
</script>
<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'

const activeMonthId = ref<number | null>(null)

const emit = defineEmits<{
    (e: 'selectedMonth', value: number): void
}>()

const props = defineProps<{
    months: { id: number; name: string }[]
}>()

// fallback: si no hay props.months o no tiene el mes actual
const monthsName = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
]

onMounted(() => {
    const date = new Date()
    const day = date.getDate()
    const monthIndex = date.getMonth()

    // Buscar el id basado en la lógica (día > 5, siguiente mes o actual)
    const targetMonthName = day > 5
        ? monthsName[Math.min(monthIndex + 1, 11)]
        : monthsName[monthIndex]

    // Buscar id por nombre en props.months
    const month = props.months.find(m => m.name === targetMonthName)
    activeMonthId.value = month ? month.id : null
})

watch(activeMonthId, (newId) => {
    if (newId !== null) emit('selectedMonth', newId)
})
</script>

<template>
    <div class="flex space-x-2 overflow-x-auto pb-2">
        <button
            v-for="month in months"
            :key="month.id"
            @click="activeMonthId = month.id"
            :class="[
                'px-4 py-2 rounded-full text-sm whitespace-nowrap',
                activeMonthId === month.id
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'
            ]"
        >
            {{ month.name }}
        </button>
    </div>
</template>

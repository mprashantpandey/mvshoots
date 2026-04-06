<script setup>
defineProps({
    label: String,
    value: [String, Number],
    icon: String,
    hint: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="glass-card stat-card">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <div class="text-secondary small mb-2">{{ label }}</div>
                <div class="fs-4 fw-bold">{{ value }}</div>
                <div v-if="hint" class="text-secondary small mt-2">{{ hint }}</div>
            </div>
            <div class="stat-icon">
                <i :class="['bi', icon]"></i>
            </div>
        </div>
    </div>
</template>

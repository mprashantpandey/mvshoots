<script setup>
const props = defineProps({
    value: {
        type: String,
        required: true,
    },
});

const map = {
    active: 'bg-success-subtle text-success',
    inactive: 'bg-secondary-subtle text-secondary',
    pending: 'bg-warning-subtle text-warning-emphasis',
    confirmed: 'bg-info-subtle text-info-emphasis',
    assigned: 'bg-primary-subtle text-primary',
    accepted: 'bg-success-subtle text-success-emphasis',
    in_progress: 'bg-info-subtle text-info',
    completed: 'bg-success-subtle text-success',
    advance: 'bg-primary-subtle text-primary',
    final: 'bg-success-subtle text-success',
    paid: 'bg-success-subtle text-success',
};

const label = props.value.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase());
</script>

<template>
    <span class="badge badge-soft" :class="map[value] || 'bg-light text-dark'">
        {{ label }}
    </span>
</template>

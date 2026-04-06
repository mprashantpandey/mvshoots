<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="d-flex flex-wrap gap-2">
        <component
            :is="link.url ? Link : 'span'"
            v-for="link in links"
            :key="`${link.label}-${link.url}`"
            :href="link.url"
            class="btn btn-sm"
            :class="link.active ? 'btn-primary' : 'btn-outline-secondary'"
            v-html="link.label"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const status = computed(() => page.props.flash?.status);
const errors = computed(() => page.props.flash?.errors ?? []);
</script>

<template>
    <div v-if="status" class="alert alert-success border-0 shadow-sm rounded-4">
        {{ status }}
    </div>

    <div v-if="errors.length" class="alert alert-danger border-0 shadow-sm rounded-4">
        <div class="fw-semibold mb-1">Please review the form</div>
        <ul class="mb-0 ps-3">
            <li v-for="error in errors" :key="error">{{ error }}</li>
        </ul>
    </div>
</template>

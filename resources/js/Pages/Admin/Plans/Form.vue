<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    plan: {
        type: Object,
        default: null,
    },
    categories: {
        type: Array,
        required: true,
    },
    cities: {
        type: Array,
        default: () => [],
    },
    submitUrl: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        required: true,
    },
});

const form = useForm({
    category_id: props.plan?.category_id ?? props.categories[0]?.id ?? '',
    title: props.plan?.title ?? '',
    description: props.plan?.description ?? '',
    price: props.plan?.price ?? '',
    duration: props.plan?.duration ?? '',
    inclusions: props.plan?.inclusions?.join('\n') ?? '',
    city_ids: props.plan?.city_ids ?? [],
    status: props.plan?.status ?? 'active',
    _method: props.method === 'put' ? 'put' : 'post',
});

const title = computed(() => (props.plan ? 'Edit Plan' : 'Create Plan'));

function submit() {
    form.post(props.submitUrl);
}
</script>

<template>
    <AdminLayout :title="title">
        <Head :title="title" />

        <div class="glass-card p-4">
            <h2 class="h5 mb-4">{{ props.plan ? 'Update service plan' : 'Add new service plan' }}</h2>

            <form @submit.prevent="submit">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select v-model="form.category_id" class="form-select" required>
                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select v-model="form.status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Available In Cities</label>
                        <select v-model="form.city_ids" class="form-select" multiple size="6">
                            <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                        </select>
                        <div class="form-text">Leave empty to make this plan available in all cities.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input v-model="form.title" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Price</label>
                        <input v-model="form.price" class="form-control" type="number" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Duration</label>
                        <input v-model="form.duration" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea v-model="form.description" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Inclusions (one per line)</label>
                        <textarea v-model="form.inclusions" class="form-control" rows="5"></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit" :disabled="form.processing">Save Plan</button>
                    <Link class="btn btn-outline-secondary" href="/admin/plans">Cancel</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

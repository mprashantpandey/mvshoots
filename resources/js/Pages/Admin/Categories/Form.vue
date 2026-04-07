<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    category: {
        type: Object,
        default: null,
    },
    submitUrl: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        required: true,
    },
    cities: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: props.category?.name ?? '',
    description: props.category?.description ?? '',
    status: props.category?.status ?? 'active',
    image: null,
    city_ids: props.category?.city_ids ?? [],
    _method: props.method === 'put' ? 'put' : 'post',
});

const title = computed(() => (props.category ? 'Edit Category' : 'Create Category'));

function submit() {
    form.post(props.submitUrl, {
        forceFormData: true,
    });
}
</script>

<template>
    <AdminLayout :title="title">
        <Head :title="title" />

        <div class="glass-card p-4">
            <h2 class="h5 mb-4">{{ props.category ? 'Update category' : 'Add new category' }}</h2>

            <form @submit.prevent="submit">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input v-model="form.name" class="form-control" required>
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
                        <div class="form-text">Leave empty to make this category available in all cities.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea v-model="form.description" class="form-control"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Image</label>
                        <input class="form-control" type="file" @input="form.image = $event.target.files[0]">
                    </div>
                    <div class="col-12" v-if="props.category?.image">
                        <div class="small text-secondary mb-2">Current image</div>
                        <img :src="props.category.image" :alt="props.category.name" class="rounded-4 object-fit-cover" width="120" height="120">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit" :disabled="form.processing">Save Category</button>
                    <Link class="btn btn-outline-secondary" href="/admin/categories">Cancel</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

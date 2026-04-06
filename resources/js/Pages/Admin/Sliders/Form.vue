<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    slider: {
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
});

const form = useForm({
    title: props.slider?.title ?? '',
    subtitle: props.slider?.subtitle ?? '',
    image: null,
    app_target: props.slider?.app_target ?? 'user',
    sort_order: props.slider?.sort_order ?? 0,
    status: props.slider?.status ?? 'active',
    _method: props.method === 'put' ? 'put' : 'post',
});

const title = computed(() => (props.slider ? 'Edit Slider' : 'Create Slider'));

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
            <h2 class="h5 mb-4">{{ props.slider ? 'Update slider' : 'Add new slider' }}</h2>

            <form @submit.prevent="submit">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input v-model="form.title" class="form-control" placeholder="Optional short title">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target App</label>
                        <select v-model="form.app_target" class="form-select">
                            <option value="user">User App</option>
                            <option value="partner">Partner App</option>
                            <option value="both">Both Apps</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Subtitle</label>
                        <input v-model="form.subtitle" class="form-control" placeholder="Optional short subtitle">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Image</label>
                        <input class="form-control" type="file" accept="image/*" @input="form.image = $event.target.files[0]">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort Order</label>
                        <input v-model="form.sort_order" class="form-control" type="number" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select v-model="form.status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12" v-if="props.slider?.image">
                        <div class="small text-secondary mb-2">Current image</div>
                        <img :src="props.slider.image" alt="Slider image" class="rounded-4 object-fit-cover" width="240" height="140">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit" :disabled="form.processing">Save Slider</button>
                    <Link class="btn btn-outline-secondary" href="/admin/sliders">Cancel</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

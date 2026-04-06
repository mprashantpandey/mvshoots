<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    reel: {
        type: Object,
        default: null,
    },
    categories: {
        type: Array,
        required: true,
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
    title: props.reel?.title ?? '',
    status: props.reel?.status ?? 'active',
    video_url: props.reel?.video_url ?? '',
    video_file: null,
    thumbnail: null,
    category_id: props.reel?.category_id ?? '',
    _method: props.method === 'put' ? 'put' : 'post',
});

const title = computed(() => (props.reel ? 'Edit Reel' : 'Create Reel'));

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
            <h2 class="h5 mb-4">{{ props.reel ? 'Update reel' : 'Add new reel' }}</h2>

            <form @submit.prevent="submit">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input v-model="form.title" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select v-model="form.status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Video URL</label>
                        <input v-model="form.video_url" class="form-control" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Video File</label>
                        <input class="form-control" type="file" accept="video/*" @input="form.video_file = $event.target.files[0]">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Thumbnail</label>
                        <input class="form-control" type="file" accept="image/*" @input="form.thumbnail = $event.target.files[0]">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select v-model="form.category_id" class="form-select">
                            <option value="">None</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                        </select>
                    </div>
                    <div class="col-12" v-if="props.reel?.thumbnail">
                        <div class="small text-secondary mb-2">Current thumbnail</div>
                        <img :src="props.reel.thumbnail" :alt="props.reel.title" class="rounded-4 object-fit-cover" width="160" height="120">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit" :disabled="form.processing">Save Reel</button>
                    <Link class="btn btn-outline-secondary" href="/admin/reels">Cancel</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

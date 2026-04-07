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
const maxVideoBytes = 128 * 1024 * 1024;
const maxThumbnailBytes = 4 * 1024 * 1024;
const allowedVideoTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm'];
const allowedThumbnailTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

function submit() {
    form.post(props.submitUrl, {
        forceFormData: true,
    });
}

function handleVideoInput(event) {
    const file = event.target.files?.[0] ?? null;
    form.clearErrors('video_file');
    if (!file) {
        form.video_file = null;
        return;
    }

    if (!allowedVideoTypes.includes(file.type)) {
        form.video_file = null;
        form.setError('video_file', 'Video file must be MP4, MOV, AVI, or WEBM.');
        event.target.value = '';
        return;
    }

    if (file.size > maxVideoBytes) {
        form.video_file = null;
        form.setError('video_file', 'Video file must be 128 MB or smaller.');
        event.target.value = '';
        return;
    }

    form.video_file = file;
}

function handleThumbnailInput(event) {
    const file = event.target.files?.[0] ?? null;
    form.clearErrors('thumbnail');
    if (!file) {
        form.thumbnail = null;
        return;
    }

    if (!allowedThumbnailTypes.includes(file.type)) {
        form.thumbnail = null;
        form.setError('thumbnail', 'Thumbnail must be a JPG, PNG, WEBP, or GIF image.');
        event.target.value = '';
        return;
    }

    if (file.size > maxThumbnailBytes) {
        form.thumbnail = null;
        form.setError('thumbnail', 'Thumbnail must be 4 MB or smaller.');
        event.target.value = '';
        return;
    }

    form.thumbnail = file;
}
</script>

<template>
    <AdminLayout :title="title">
        <Head :title="title" />

        <div class="glass-card p-4">
            <h2 class="h5 mb-4">{{ props.reel ? 'Update reel' : 'Add new reel' }}</h2>

            <div v-if="Object.keys(form.errors).length" class="alert alert-danger py-3">
                Please review the highlighted fields below. Upload issues usually come from file type, file size, or server upload limits.
            </div>

            <form @submit.prevent="submit">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input v-model="form.title" class="form-control" :class="{ 'is-invalid': form.errors.title }" required>
                        <div v-if="form.errors.title" class="invalid-feedback d-block">{{ form.errors.title }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select v-model="form.status" class="form-select" :class="{ 'is-invalid': form.errors.status }">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div v-if="form.errors.status" class="invalid-feedback d-block">{{ form.errors.status }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Video URL</label>
                        <input v-model="form.video_url" class="form-control" :class="{ 'is-invalid': form.errors.video_url }" placeholder="https://">
                        <div v-if="form.errors.video_url" class="invalid-feedback d-block">{{ form.errors.video_url }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Video File</label>
                        <input class="form-control" :class="{ 'is-invalid': form.errors.video_file }" type="file" accept="video/mp4,video/quicktime,video/x-msvideo,video/webm" @input="handleVideoInput">
                        <div class="form-text">Upload MP4, MOV, AVI, or WEBM. Recommended max size: 128 MB.</div>
                        <div v-if="form.errors.video_file" class="invalid-feedback d-block">{{ form.errors.video_file }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Thumbnail</label>
                        <input class="form-control" :class="{ 'is-invalid': form.errors.thumbnail }" type="file" accept="image/jpeg,image/png,image/webp,image/gif" @input="handleThumbnailInput">
                        <div class="form-text">Upload a JPG, PNG, or WEBP thumbnail. Recommended max size: 4 MB.</div>
                        <div v-if="form.errors.thumbnail" class="invalid-feedback d-block">{{ form.errors.thumbnail }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select v-model="form.category_id" class="form-select" :class="{ 'is-invalid': form.errors.category_id }">
                            <option value="">None</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                        </select>
                        <div v-if="form.errors.category_id" class="invalid-feedback d-block">{{ form.errors.category_id }}</div>
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

<script setup>
import { computed, reactive, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    notifications: Object,
    filters: Object,
    recipientOptions: Object,
});

const filters = reactive({
    search: props.filters?.search ?? '',
    user_type: props.filters?.user_type ?? '',
    type: props.filters?.type ?? '',
    is_read: props.filters?.is_read ?? '',
});

const form = useForm({
    user_type: 'user',
    user_id: '',
    title: '',
    type: 'manual_notification',
    reference_id: '',
    body: '',
});

const availableRecipients = computed(() => props.recipientOptions?.[form.user_type] ?? []);

watch(() => form.user_type, () => {
    form.user_id = availableRecipients.value[0]?.id ?? '';
}, { immediate: true });

function submitFilters() {
    router.get('/admin/notifications', filters, {
        preserveState: true,
        replace: true,
    });
}

function submitNotification() {
    form.post('/admin/notifications');
}

function toggleReadState(notification) {
    router.post(`/admin/notifications/${notification.id}/read-state`, {
        is_read: !notification.is_read,
    });
}
</script>

<template>
    <AdminLayout title="Notifications">
        <div class="row g-4">
            <div class="col-xl-4">
                <div class="glass-card p-4 h-100">
                    <h2 class="h5 mb-3">Send Manual Notification</h2>
                    <form @submit.prevent="submitNotification">
                        <div class="mb-3">
                            <label class="form-label">Target User Type</label>
                            <select v-model="form.user_type" class="form-select" name="user_type">
                                <option value="user">User</option>
                                <option value="partner">Partner</option>
                                <option value="owner">Owner</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Target User</label>
                            <select v-model="form.user_id" class="form-select" required>
                                <option value="" disabled>Select recipient</option>
                                <option v-for="recipient in availableRecipients" :key="`${form.user_type}-${recipient.id}`" :value="recipient.id">
                                    {{ recipient.label }}
                                </option>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Title</label><input v-model="form.title" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Type</label><input v-model="form.type" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Reference ID</label><input v-model="form.reference_id" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Message</label><textarea v-model="form.body" class="form-control" required></textarea></div>
                        <button class="btn btn-primary w-100" :disabled="form.processing">Send Notification</button>
                    </form>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="glass-card p-4 mb-4">
                    <form class="filter-grid" @submit.prevent="submitFilters">
                        <input v-model="filters.search" class="form-control" placeholder="Search title or body">
                        <select v-model="filters.user_type" class="form-select">
                            <option value="">All targets</option>
                            <option value="user">User</option>
                            <option value="partner">Partner</option>
                            <option value="owner">Owner</option>
                            <option value="admin">Admin</option>
                        </select>
                        <input v-model="filters.type" class="form-control" placeholder="Type">
                        <select v-model="filters.is_read" class="form-select">
                            <option value="">All states</option>
                            <option value="0">Unread</option>
                            <option value="1">Read</option>
                        </select>
                        <button class="btn btn-outline-primary" type="submit">Filter</button>
                    </form>
                </div>
                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>Title</th><th>Target</th><th>Type</th><th>Read</th><th>Created</th><th class="text-end">Action</th></tr></thead>
                            <tbody>
                                <tr v-for="notification in notifications.data" :key="notification.id">
                                    <td><div class="fw-semibold">{{ notification.title }}</div><div class="small text-secondary">{{ notification.body }}</div></td>
                                    <td>{{ notification.target_label }}</td>
                                    <td>{{ notification.type }}</td>
                                    <td><StatusBadge :value="notification.is_read ? 'active' : 'pending'" /></td>
                                    <td>{{ notification.created_at }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" @click="toggleReadState(notification)">
                                            {{ notification.is_read ? 'Mark Unread' : 'Mark Read' }}
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!notifications.data.length"><td colspan="6" class="text-center py-5 text-secondary">No notifications logged yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3"><PaginationLinks :links="notifications.links" /></div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    settings: Object,
});

const tabs = [
    { key: 'general', label: 'General' },
    { key: 'dialogs', label: 'Dialogs' },
    { key: 'mobile', label: 'Mobile Apps' },
    { key: 'mail', label: 'Email / SMTP' },
    { key: 'firebase', label: 'Firebase' },
];

const activeTab = ref('general');

const form = useForm({
    app_name: props.settings?.app_name ?? '',
    contact_email: props.settings?.contact_email ?? '',
    contact_phone: props.settings?.contact_phone ?? '',
    booking_advance_percentage: props.settings?.booking_advance_percentage ?? '20',
    global_maintenance_mode: props.settings?.global_maintenance_mode ?? false,
    maintenance_message: props.settings?.maintenance_message ?? '',
    user_dialog_enabled: props.settings?.user_dialog_enabled ?? false,
    user_dialog_title: props.settings?.user_dialog_title ?? '',
    user_dialog_message: props.settings?.user_dialog_message ?? '',
    user_dialog_image_url: props.settings?.user_dialog_image_url ?? '',
    user_dialog_primary_button_text: props.settings?.user_dialog_primary_button_text ?? 'OK',
    user_dialog_primary_button_action_type: props.settings?.user_dialog_primary_button_action_type ?? 'dismiss',
    user_dialog_primary_button_action_value: props.settings?.user_dialog_primary_button_action_value ?? '',
    user_dialog_secondary_button_text: props.settings?.user_dialog_secondary_button_text ?? '',
    user_dialog_secondary_button_action_type: props.settings?.user_dialog_secondary_button_action_type ?? 'dismiss',
    user_dialog_secondary_button_action_value: props.settings?.user_dialog_secondary_button_action_value ?? '',
    user_dialog_dismissible: props.settings?.user_dialog_dismissible ?? true,
    partner_dialog_enabled: props.settings?.partner_dialog_enabled ?? false,
    partner_dialog_title: props.settings?.partner_dialog_title ?? '',
    partner_dialog_message: props.settings?.partner_dialog_message ?? '',
    partner_dialog_image_url: props.settings?.partner_dialog_image_url ?? '',
    partner_dialog_primary_button_text: props.settings?.partner_dialog_primary_button_text ?? 'OK',
    partner_dialog_primary_button_action_type: props.settings?.partner_dialog_primary_button_action_type ?? 'dismiss',
    partner_dialog_primary_button_action_value: props.settings?.partner_dialog_primary_button_action_value ?? '',
    partner_dialog_secondary_button_text: props.settings?.partner_dialog_secondary_button_text ?? '',
    partner_dialog_secondary_button_action_type: props.settings?.partner_dialog_secondary_button_action_type ?? 'dismiss',
    partner_dialog_secondary_button_action_value: props.settings?.partner_dialog_secondary_button_action_value ?? '',
    partner_dialog_dismissible: props.settings?.partner_dialog_dismissible ?? true,
    owner_dialog_enabled: props.settings?.owner_dialog_enabled ?? false,
    owner_dialog_title: props.settings?.owner_dialog_title ?? '',
    owner_dialog_message: props.settings?.owner_dialog_message ?? '',
    owner_dialog_image_url: props.settings?.owner_dialog_image_url ?? '',
    owner_dialog_primary_button_text: props.settings?.owner_dialog_primary_button_text ?? 'OK',
    owner_dialog_primary_button_action_type: props.settings?.owner_dialog_primary_button_action_type ?? 'dismiss',
    owner_dialog_primary_button_action_value: props.settings?.owner_dialog_primary_button_action_value ?? '',
    owner_dialog_secondary_button_text: props.settings?.owner_dialog_secondary_button_text ?? '',
    owner_dialog_secondary_button_action_type: props.settings?.owner_dialog_secondary_button_action_type ?? 'dismiss',
    owner_dialog_secondary_button_action_value: props.settings?.owner_dialog_secondary_button_action_value ?? '',
    owner_dialog_dismissible: props.settings?.owner_dialog_dismissible ?? true,
    user_android_latest_version: props.settings?.user_android_latest_version ?? '',
    user_android_force_update: props.settings?.user_android_force_update ?? false,
    user_android_store_url: props.settings?.user_android_store_url ?? '',
    partner_android_latest_version: props.settings?.partner_android_latest_version ?? '',
    partner_android_force_update: props.settings?.partner_android_force_update ?? false,
    partner_android_store_url: props.settings?.partner_android_store_url ?? '',
    owner_android_latest_version: props.settings?.owner_android_latest_version ?? '',
    owner_android_force_update: props.settings?.owner_android_force_update ?? false,
    owner_android_store_url: props.settings?.owner_android_store_url ?? '',
    mail_mailer: props.settings?.mail_mailer ?? 'log',
    mail_host: props.settings?.mail_host ?? '',
    mail_port: props.settings?.mail_port ?? '2525',
    mail_username: props.settings?.mail_username ?? '',
    mail_password: props.settings?.mail_password ?? '',
    mail_encryption: props.settings?.mail_encryption ?? '',
    mail_from_address: props.settings?.mail_from_address ?? '',
    mail_from_name: props.settings?.mail_from_name ?? '',
    firebase_project_id: props.settings?.firebase_project_id ?? '',
    firebase_api_key: props.settings?.firebase_api_key ?? '',
    firebase_app_id: props.settings?.firebase_app_id ?? '',
    firebase_messaging_sender_id: props.settings?.firebase_messaging_sender_id ?? '',
    firebase_storage_bucket: props.settings?.firebase_storage_bucket ?? '',
    firebase_database_url: props.settings?.firebase_database_url ?? '',
    firebase_measurement_id: props.settings?.firebase_measurement_id ?? '',
    firebase_web_push_key: props.settings?.firebase_web_push_key ?? '',
    firebase_service_account_json: props.settings?.firebase_service_account_json ?? '',
    branding_logo: null,
    _method: 'put',
});

function submit() {
    form.post('/admin/settings', {
        forceFormData: true,
    });
}

const activeTabLabel = computed(() => tabs.find((tab) => tab.key === activeTab.value)?.label ?? 'Settings');
</script>

<template>
    <AdminLayout title="Settings">
        <Head title="Settings" />
        <form @submit.prevent="submit" class="d-grid gap-4">
            <div class="glass-card p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                    <div>
                        <h2 class="h5 mb-1">Platform Settings</h2>
                        <p class="text-secondary mb-0">Manage app, mobile, email, and Firebase configuration from one place.</p>
                    </div>
                    <div class="small text-secondary">Active tab: {{ activeTabLabel }}</div>
                </div>

                <div class="settings-tabbar mb-4">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="btn"
                        :class="activeTab === tab.key ? 'btn-primary' : 'btn-outline-secondary'"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div v-show="activeTab === 'general'" class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">App Name</label>
                        <input v-model="form.app_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Brand Logo</label>
                        <input class="form-control" type="file" accept="image/*" @input="form.branding_logo = $event.target.files[0]">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Email</label>
                        <input v-model="form.contact_email" class="form-control" type="email">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Phone</label>
                        <input v-model="form.contact_phone" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Advance Payment Percentage</label>
                        <input v-model="form.booking_advance_percentage" class="form-control" type="number" min="1" max="100">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Maintenance Message</label>
                        <input v-model="form.maintenance_message" class="form-control" placeholder="Shown when maintenance mode is enabled">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input id="global_maintenance_mode" v-model="form.global_maintenance_mode" class="form-check-input" type="checkbox">
                            <label class="form-check-label" for="global_maintenance_mode">Enable global maintenance mode</label>
                        </div>
                    </div>
                    <div v-if="settings.branding_logo" class="col-12">
                        <div class="small text-secondary mb-2">Current logo</div>
                        <img :src="settings.branding_logo" alt="Brand logo" class="rounded-4 object-fit-contain border bg-white p-3" style="max-height: 120px;">
                    </div>
                </div>

                <div v-show="activeTab === 'dialogs'" class="row g-4">
                    <div class="col-12">
                        <div class="text-secondary">
                            Configure a separate dialog box for each app. Each one is fetched independently by the mobile apps.
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="glass-card p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                <div class="fw-semibold">User App Dialog</div>
                                <div class="form-check">
                                    <input id="user_dialog_enabled" v-model="form.user_dialog_enabled" class="form-check-input" type="checkbox">
                                    <label class="form-check-label" for="user_dialog_enabled">Enable</label>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input v-model="form.user_dialog_title" class="form-control" placeholder="Important update">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Image URL</label>
                                    <input v-model="form.user_dialog_image_url" class="form-control" placeholder="https://...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea v-model="form.user_dialog_message" class="form-control" rows="3" placeholder="Shown only inside the user app."></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input id="user_dialog_dismissible" v-model="form.user_dialog_dismissible" class="form-check-input" type="checkbox">
                                        <label class="form-check-label" for="user_dialog_dismissible">Dismissible</label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-medium mb-3">Primary Button</div>
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Text</label><input v-model="form.user_dialog_primary_button_text" class="form-control" placeholder="OK"></div>
                                            <div class="col-md-6"><label class="form-label">Action Type</label><select v-model="form.user_dialog_primary_button_action_type" class="form-select"><option value="dismiss">Dismiss</option><option value="route">Open App Route</option><option value="url">Open URL</option><option value="none">No Action</option></select></div>
                                            <div class="col-12"><label class="form-label">Action Value</label><input v-model="form.user_dialog_primary_button_action_value" class="form-control" placeholder="/notifications or https://example.com"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-medium mb-3">Secondary Button</div>
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Text</label><input v-model="form.user_dialog_secondary_button_text" class="form-control" placeholder="Later"></div>
                                            <div class="col-md-6"><label class="form-label">Action Type</label><select v-model="form.user_dialog_secondary_button_action_type" class="form-select"><option value="dismiss">Dismiss</option><option value="route">Open App Route</option><option value="url">Open URL</option><option value="none">No Action</option></select></div>
                                            <div class="col-12"><label class="form-label">Action Value</label><input v-model="form.user_dialog_secondary_button_action_value" class="form-control" placeholder="/profile or https://example.com"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="glass-card p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                <div class="fw-semibold">Partner App Dialog</div>
                                <div class="form-check">
                                    <input id="partner_dialog_enabled" v-model="form.partner_dialog_enabled" class="form-check-input" type="checkbox">
                                    <label class="form-check-label" for="partner_dialog_enabled">Enable</label>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input v-model="form.partner_dialog_title" class="form-control" placeholder="Partner notice">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Image URL</label>
                                    <input v-model="form.partner_dialog_image_url" class="form-control" placeholder="https://...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea v-model="form.partner_dialog_message" class="form-control" rows="3" placeholder="Shown only inside the partner app."></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input id="partner_dialog_dismissible" v-model="form.partner_dialog_dismissible" class="form-check-input" type="checkbox">
                                        <label class="form-check-label" for="partner_dialog_dismissible">Dismissible</label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-medium mb-3">Primary Button</div>
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Text</label><input v-model="form.partner_dialog_primary_button_text" class="form-control" placeholder="OK"></div>
                                            <div class="col-md-6"><label class="form-label">Action Type</label><select v-model="form.partner_dialog_primary_button_action_type" class="form-select"><option value="dismiss">Dismiss</option><option value="route">Open App Route</option><option value="url">Open URL</option><option value="none">No Action</option></select></div>
                                            <div class="col-12"><label class="form-label">Action Value</label><input v-model="form.partner_dialog_primary_button_action_value" class="form-control" placeholder="/notifications or https://example.com"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-medium mb-3">Secondary Button</div>
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Text</label><input v-model="form.partner_dialog_secondary_button_text" class="form-control" placeholder="Later"></div>
                                            <div class="col-md-6"><label class="form-label">Action Type</label><select v-model="form.partner_dialog_secondary_button_action_type" class="form-select"><option value="dismiss">Dismiss</option><option value="route">Open App Route</option><option value="url">Open URL</option><option value="none">No Action</option></select></div>
                                            <div class="col-12"><label class="form-label">Action Value</label><input v-model="form.partner_dialog_secondary_button_action_value" class="form-control" placeholder="/profile or https://example.com"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="glass-card p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                <div class="fw-semibold">Owner App Dialog</div>
                                <div class="form-check">
                                    <input id="owner_dialog_enabled" v-model="form.owner_dialog_enabled" class="form-check-input" type="checkbox">
                                    <label class="form-check-label" for="owner_dialog_enabled">Enable</label>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input v-model="form.owner_dialog_title" class="form-control" placeholder="Owner notice">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Image URL</label>
                                    <input v-model="form.owner_dialog_image_url" class="form-control" placeholder="https://...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea v-model="form.owner_dialog_message" class="form-control" rows="3" placeholder="Shown only inside the owner app."></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input id="owner_dialog_dismissible" v-model="form.owner_dialog_dismissible" class="form-check-input" type="checkbox">
                                        <label class="form-check-label" for="owner_dialog_dismissible">Dismissible</label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-medium mb-3">Primary Button</div>
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Text</label><input v-model="form.owner_dialog_primary_button_text" class="form-control" placeholder="OK"></div>
                                            <div class="col-md-6"><label class="form-label">Action Type</label><select v-model="form.owner_dialog_primary_button_action_type" class="form-select"><option value="dismiss">Dismiss</option><option value="route">Open App Route</option><option value="url">Open URL</option><option value="none">No Action</option></select></div>
                                            <div class="col-12"><label class="form-label">Action Value</label><input v-model="form.owner_dialog_primary_button_action_value" class="form-control" placeholder="/notifications or https://example.com"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-medium mb-3">Secondary Button</div>
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Text</label><input v-model="form.owner_dialog_secondary_button_text" class="form-control" placeholder="Later"></div>
                                            <div class="col-md-6"><label class="form-label">Action Type</label><select v-model="form.owner_dialog_secondary_button_action_type" class="form-select"><option value="dismiss">Dismiss</option><option value="route">Open App Route</option><option value="url">Open URL</option><option value="none">No Action</option></select></div>
                                            <div class="col-12"><label class="form-label">Action Value</label><input v-model="form.owner_dialog_secondary_button_action_value" class="form-control" placeholder="/profile or https://example.com"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'mobile'" class="row g-4">
                    <div class="col-12"><div class="fw-semibold">User App</div></div>
                    <div class="col-md-4"><label class="form-label">Latest Android Version</label><input v-model="form.user_android_latest_version" class="form-control"></div>
                    <div class="col-md-8"><label class="form-label">Play Store URL</label><input v-model="form.user_android_store_url" class="form-control"></div>
                    <div class="col-12"><div class="form-check"><input id="user_android_force_update" v-model="form.user_android_force_update" class="form-check-input" type="checkbox"><label class="form-check-label" for="user_android_force_update">Force update for User App</label></div></div>

                    <div class="col-12"><hr><div class="fw-semibold">Partner App</div></div>
                    <div class="col-md-4"><label class="form-label">Latest Android Version</label><input v-model="form.partner_android_latest_version" class="form-control"></div>
                    <div class="col-md-8"><label class="form-label">Play Store URL</label><input v-model="form.partner_android_store_url" class="form-control"></div>
                    <div class="col-12"><div class="form-check"><input id="partner_android_force_update" v-model="form.partner_android_force_update" class="form-check-input" type="checkbox"><label class="form-check-label" for="partner_android_force_update">Force update for Partner App</label></div></div>

                    <div class="col-12"><hr><div class="fw-semibold">Owner App</div></div>
                    <div class="col-md-4"><label class="form-label">Latest Android Version</label><input v-model="form.owner_android_latest_version" class="form-control"></div>
                    <div class="col-md-8"><label class="form-label">App Download URL</label><input v-model="form.owner_android_store_url" class="form-control"></div>
                    <div class="col-12"><div class="form-check"><input id="owner_android_force_update" v-model="form.owner_android_force_update" class="form-check-input" type="checkbox"><label class="form-check-label" for="owner_android_force_update">Force update for Owner App</label></div></div>
                </div>

                <div v-show="activeTab === 'mail'" class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Mailer</label>
                        <select v-model="form.mail_mailer" class="form-select">
                            <option value="smtp">SMTP</option>
                            <option value="log">Log</option>
                            <option value="sendmail">Sendmail</option>
                            <option value="array">Array</option>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">SMTP Host</label><input v-model="form.mail_host" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">SMTP Port</label><input v-model="form.mail_port" class="form-control" type="number"></div>
                    <div class="col-md-6"><label class="form-label">SMTP Username</label><input v-model="form.mail_username" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">SMTP Password</label><input v-model="form.mail_password" class="form-control" type="password"></div>
                    <div class="col-md-4">
                        <label class="form-label">Encryption</label>
                        <select v-model="form.mail_encryption" class="form-select">
                            <option value="">None</option>
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">From Address</label><input v-model="form.mail_from_address" class="form-control" type="email"></div>
                    <div class="col-md-4"><label class="form-label">From Name</label><input v-model="form.mail_from_name" class="form-control"></div>
                </div>

                <div v-show="activeTab === 'firebase'" class="row g-4">
                    <div class="col-md-6"><label class="form-label">Project ID</label><input v-model="form.firebase_project_id" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">API Key</label><input v-model="form.firebase_api_key" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">App ID</label><input v-model="form.firebase_app_id" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Messaging Sender ID</label><input v-model="form.firebase_messaging_sender_id" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Storage Bucket</label><input v-model="form.firebase_storage_bucket" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Database URL</label><input v-model="form.firebase_database_url" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Measurement ID</label><input v-model="form.firebase_measurement_id" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Web Push Key</label><input v-model="form.firebase_web_push_key" class="form-control"></div>
                    <div class="col-12">
                        <label class="form-label">Service Account JSON</label>
                        <textarea v-model="form.firebase_service_account_json" class="form-control" rows="6" placeholder="{ ...json credentials... }"></textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary" :disabled="form.processing">Save Settings</button>
            </div>
        </form>
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    partner: Object,
});

const rejectReason = ref('');

const kycImages = computed(() => {
    const d = props.partner.kyc_detail;
    if (!d) {
        return [];
    }
    return [
        { label: 'Aadhaar front', url: d.aadhar_front_url },
        { label: 'Aadhaar back', url: d.aadhar_back_url },
        { label: 'PAN card', url: d.pan_image_url },
        { label: 'Selfie', url: d.selfie_url },
    ];
});

function headline(value) {
    const safeValue = String(value ?? 'file');

    return safeValue.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function toggleStatus() {
    router.post(props.partner.status_url, {
        status: props.partner.status === 'active' ? 'inactive' : 'active',
    });
}

function verifyKyc() {
    if (!props.partner.verify_kyc_url) {
        return;
    }
    if (window.confirm('Verify this partner KYC? They will be eligible for new bookings.')) {
        router.post(props.partner.verify_kyc_url);
    }
}

function rejectKyc() {
    if (!props.partner.reject_kyc_url || !rejectReason.value.trim()) {
        return;
    }
    router.post(props.partner.reject_kyc_url, { rejection_reason: rejectReason.value });
}
</script>

<template>
    <AdminLayout title="Partner Details">
        <Head title="Partner Details" />
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
                <div>
                    <h2 class="h4 mb-1">{{ partner.name }}</h2>
                    <p class="text-secondary mb-0">Partner profile, KYC, assignments, and uploads</p>
                </div>
                <button class="btn btn-outline-secondary" type="button" @click="toggleStatus">
                    {{ partner.status === 'active' ? 'Deactivate Partner' : 'Activate Partner' }}
                </button>
            </div>
            <div class="row g-4">
                <div class="col-md-3"><strong>Name</strong><div>{{ partner.name }}</div></div>
                <div class="col-md-3"><strong>Phone</strong><div>{{ partner.phone }}</div></div>
                <div class="col-md-3"><strong>Email</strong><div>{{ partner.email || 'N/A' }}</div></div>
                <div class="col-md-3"><strong>Status</strong><div><StatusBadge :value="partner.status" /></div></div>
                <div class="col-md-6">
                    <strong>Service cities</strong>
                    <div v-if="partner.service_cities?.length">{{ partner.service_cities.join(', ') }}</div>
                    <div v-else-if="partner.city_name" class="text-secondary">Primary only: {{ partner.city_name }}</div>
                    <div v-else class="text-secondary">Not set</div>
                </div>
            </div>
        </div>

        <div v-if="partner.kyc_detail" class="glass-card p-4 mb-4">
            <h2 class="h5 mb-3">KYC verification</h2>
            <p class="mb-1"><strong>Status:</strong> <span class="text-capitalize">{{ partner.kyc_detail.status }}</span></p>
            <p v-if="partner.kyc_detail.submitted_at" class="small text-secondary mb-1">Submitted: {{ partner.kyc_detail.submitted_at }}</p>
            <p v-if="partner.kyc_detail.reviewed_at" class="small text-secondary mb-2">
                Reviewed: {{ partner.kyc_detail.reviewed_at }}
                <span v-if="partner.kyc_detail.reviewed_by_name"> — {{ partner.kyc_detail.reviewed_by_name }}</span>
            </p>
            <p v-if="partner.kyc_detail.rejection_reason" class="alert alert-warning py-2 small mb-3">{{ partner.kyc_detail.rejection_reason }}</p>
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <div class="small text-secondary mb-1">Aadhaar number</div>
                    <div class="font-monospace small">{{ partner.kyc_detail.aadhar_number }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small text-secondary mb-1">PAN</div>
                    <div class="font-monospace small">{{ partner.kyc_detail.pan_number }}</div>
                </div>
            </div>
            <div class="row g-3">
                <div v-for="img in kycImages" :key="img.label" class="col-6 col-md-3">
                    <div class="small text-secondary mb-1">{{ img.label }}</div>
                    <a :href="img.url" target="_blank" rel="noreferrer" class="d-block border rounded overflow-hidden bg-light">
                        <img :src="img.url" :alt="img.label" class="w-100 object-fit-cover" style="max-height: 180px;">
                    </a>
                </div>
            </div>
            <div v-if="partner.verify_kyc_url && partner.reject_kyc_url" class="mt-4 d-flex flex-wrap gap-3 align-items-start">
                <button type="button" class="btn btn-success" @click="verifyKyc">Verify KYC</button>
                <div class="d-flex flex-column gap-2">
                    <textarea v-model="rejectReason" class="form-control form-control-sm" rows="2" placeholder="Rejection reason (required to reject)" style="min-width: 260px;"></textarea>
                    <button type="button" class="btn btn-outline-danger btn-sm" @click="rejectKyc">Reject KYC</button>
                </div>
            </div>
        </div>
        <div v-else class="glass-card p-4 mb-4 text-secondary">
            <h2 class="h5 mb-2">KYC verification</h2>
            <p class="mb-0">This partner has not submitted KYC documents yet.</p>
        </div>

        <div class="glass-card p-4 mb-4">
            <h2 class="h5 mb-2">Ratings</h2>
            <p class="mb-3 text-secondary small">
                Average <strong>{{ partner.rating_average ?? '—' }}</strong> / 5
                <span v-if="partner.ratings_count">({{ partner.ratings_count }} reviews)</span>
            </p>
            <div v-if="partner.recent_ratings?.length" class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Booking</th><th>Plan</th><th>Customer</th><th>Stars</th><th>Review</th><th>Date</th></tr></thead>
                    <tbody>
                        <tr v-for="r in partner.recent_ratings" :key="r.id">
                            <td>#{{ r.booking_id }}</td>
                            <td>{{ r.plan_title || '—' }}</td>
                            <td>{{ r.customer_name || '—' }}</td>
                            <td>{{ r.rating }} / 5</td>
                            <td class="small">{{ r.review || '—' }}</td>
                            <td class="small text-secondary">{{ r.created_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="text-secondary mb-0">No ratings yet.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="table-card">
                    <div class="p-4 border-bottom"><h2 class="h5 mb-0">Assigned Bookings</h2></div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>#</th><th>User</th><th>Plan</th><th>Status</th></tr></thead>
                            <tbody>
                                <tr v-for="booking in partner.assigned_bookings" :key="booking.id">
                                    <td><Link :href="booking.show_url" class="text-decoration-none fw-semibold">#{{ booking.id }}</Link></td>
                                    <td>{{ booking.user_name || 'Unknown user' }}</td>
                                    <td>{{ booking.plan_name || 'No plan' }}</td>
                                    <td><StatusBadge :value="booking.status" /></td>
                                </tr>
                                <tr v-if="!partner.assigned_bookings?.length"><td colspan="4" class="text-center py-5 text-secondary">No assigned bookings.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="table-card">
                    <div class="p-4 border-bottom"><h2 class="h5 mb-0">Uploaded Results</h2></div>
                    <div class="p-4">
                        <div v-for="result in partner.booking_results" :key="result.id" class="border rounded-4 p-3 mb-3">
                            <div class="fw-semibold">{{ headline(result.file_type) }}</div>
                            <div class="small text-secondary mb-2">{{ result.notes || 'No notes' }}</div>
                            <a
                                v-if="result.file_url"
                                :href="result.file_url"
                                target="_blank"
                                rel="noreferrer"
                                class="small text-decoration-none"
                            >
                                View file
                            </a>
                            <div v-else class="small text-secondary">File unavailable</div>
                        </div>
                        <div v-if="!partner.booking_results?.length" class="text-secondary">No result uploads yet.</div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

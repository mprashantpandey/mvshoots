<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    booking: {
        type: Object,
        required: true,
    },
    partners: {
        type: Array,
        required: true,
    },
    statusOptions: {
        type: Array,
        required: true,
    },
});

const assignForm = useForm({
    partner_id: props.booking.partner_id ?? props.partners[0]?.id ?? '',
    remarks: '',
});

const statusForm = useForm({
    status: props.booking.status,
    remarks: '',
});

const hasResults = computed(() => props.booking.results?.length > 0);

function submitAssignment() {
    assignForm.post(props.booking.assign_url);
}

function submitStatus() {
    statusForm.post(props.booking.status_url);
}

function headline(value) {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}
</script>

<template>
    <AdminLayout title="Booking Details">
        <Head :title="`Booking #${booking.id}`" />

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <h2 class="h4 mb-1">Booking #{{ booking.id }}</h2>
                            <p class="text-secondary mb-0">{{ booking.plan_name || 'Plan TBD' }} • {{ booking.category_name || 'Category TBD' }}</p>
                        </div>
                        <StatusBadge :value="booking.status" />
                    </div>
                    <div class="row g-4 mt-1">
                        <div class="col-md-6">
                            <strong>Customer</strong>
                            <div>{{ booking.user_name || 'Unknown user' }}<br>{{ booking.user_phone || 'No phone' }}</div>
                        </div>
                        <div class="col-md-6">
                            <strong>Assigned Partner</strong>
                            <div>{{ booking.partner_name || 'Not assigned yet' }}</div>
                        </div>
                        <div class="col-md-6">
                            <strong>Shoot Date</strong>
                            <div>{{ booking.booking_date || 'TBD' }}</div>
                        </div>
                        <div class="col-md-6">
                            <strong>Shoot Time</strong>
                            <div>{{ booking.booking_time || 'TBD' }}</div>
                        </div>
                        <div class="col-12">
                            <strong>Address</strong>
                            <div>{{ booking.address }}</div>
                        </div>
                        <div class="col-12">
                            <strong>Notes</strong>
                            <div>{{ booking.notes || 'No notes provided.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="glass-card p-4 mb-4">
                    <h3 class="h6 mb-3">Assign Partner</h3>
                    <form @submit.prevent="submitAssignment">
                        <select v-model="assignForm.partner_id" class="form-select mb-3" required>
                            <option v-for="partner in partners" :key="partner.id" :value="partner.id">{{ partner.name }}</option>
                        </select>
                        <textarea v-model="assignForm.remarks" class="form-control mb-3" placeholder="Remarks"></textarea>
                        <button class="btn btn-primary w-100" type="submit" :disabled="assignForm.processing">Assign Partner</button>
                    </form>
                </div>
                <div class="glass-card p-4">
                    <h3 class="h6 mb-3">Update Status</h3>
                    <form @submit.prevent="submitStatus">
                        <select v-model="statusForm.status" class="form-select mb-3" required>
                            <option v-for="status in statusOptions" :key="status" :value="status">{{ headline(status) }}</option>
                        </select>
                        <textarea v-model="statusForm.remarks" class="form-control mb-3" placeholder="Remarks"></textarea>
                        <button class="btn btn-outline-primary w-100" type="submit" :disabled="statusForm.processing">Update Booking</button>
                    </form>
                </div>
            </div>
        </div>

        <div v-if="booking.partner_rating" class="row g-4 mb-4">
            <div class="col-12">
                <div class="glass-card p-4">
                    <h3 class="h6 mb-3">Customer rating</h3>
                    <div class="d-flex align-items-baseline flex-wrap gap-2 mb-2">
                        <span class="fs-4 fw-semibold">{{ booking.partner_rating.rating }} / 5</span>
                        <span class="text-secondary small">from {{ booking.partner_rating.customer_name || 'Customer' }}</span>
                    </div>
                    <p v-if="booking.partner_rating.review" class="mb-0">{{ booking.partner_rating.review }}</p>
                    <p v-else class="text-secondary small mb-0">No written review.</p>
                    <p class="small text-secondary mt-2 mb-0">{{ booking.partner_rating.created_at }}</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="table-card h-100">
                    <div class="p-4 border-bottom"><h3 class="h6 mb-0">Payment Summary</h3></div>
                    <div class="p-4">
                        <div class="d-flex justify-content-between mb-2"><span>Total Amount</span><strong>₹{{ booking.total_amount }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Advance</span><strong>₹{{ booking.advance_amount }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Final</span><strong>₹{{ booking.final_amount }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Advance Paid</span><StatusBadge :value="booking.advance_paid ? 'paid' : 'pending'" /></div>
                        <div class="d-flex justify-content-between mb-3"><span>Final Paid</span><StatusBadge :value="booking.final_paid ? 'paid' : 'pending'" /></div>
                        <div v-if="booking.payments?.length" class="d-grid gap-2">
                            <Link v-for="payment in booking.payments" :key="payment.id" class="btn btn-light text-start" :href="payment.show_url">
                                {{ headline(payment.type) }} • ₹{{ payment.amount }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="table-card h-100">
                    <div class="p-4 border-bottom"><h3 class="h6 mb-0">Status Timeline</h3></div>
                    <div class="p-4">
                        <div v-for="log in booking.status_logs" :key="log.id" class="border-start border-3 border-primary ps-3 pb-3 mb-3">
                            <div class="fw-semibold">{{ headline(log.status) }}</div>
                            <div class="small text-secondary">{{ log.remarks || 'No remarks' }}</div>
                            <div class="small text-secondary mt-1">{{ log.created_at }}</div>
                        </div>
                        <div v-if="!booking.status_logs?.length" class="text-secondary">No status logs yet.</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="table-card h-100">
                    <div class="p-4 border-bottom"><h3 class="h6 mb-0">Uploaded Results</h3></div>
                    <div class="p-4">
                        <div v-for="result in booking.results" :key="result.id" class="border rounded-4 p-3 mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <strong>{{ headline(result.file_type) }}</strong>
                                <span class="small text-secondary">{{ result.partner_name || 'Partner' }}</span>
                            </div>
                            <div class="small text-secondary mb-2">{{ result.notes || 'No notes' }}</div>
                            <a :href="result.file_url" target="_blank" rel="noreferrer" class="small text-decoration-none">View file</a>
                        </div>
                        <div v-if="!hasResults" class="text-secondary">No results uploaded yet.</div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

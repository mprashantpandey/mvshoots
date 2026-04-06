<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

defineProps({
    payment: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AdminLayout title="Payment Details">
        <Head title="Payment Details" />

        <div class="glass-card p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <strong>Booking</strong>
                    <div>#{{ payment.booking_id }}</div>
                </div>
                <div class="col-md-4">
                    <strong>User</strong>
                    <div>{{ payment.user_name || 'Unknown user' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Partner</strong>
                    <div>{{ payment.partner_name || 'Unassigned' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Plan</strong>
                    <div>{{ payment.plan_name || 'No plan' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Type</strong>
                    <div><StatusBadge :value="payment.payment_type" /></div>
                </div>
                <div class="col-md-4">
                    <strong>Status</strong>
                    <div><StatusBadge :value="payment.payment_status" /></div>
                </div>
                <div class="col-md-4">
                    <strong>Amount</strong>
                    <div>₹{{ payment.amount }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Reference</strong>
                    <div>{{ payment.payment_reference || 'N/A' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Paid At</strong>
                    <div>{{ payment.paid_at || 'Pending' }}</div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

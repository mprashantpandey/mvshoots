<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    partners: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search ?? '',
});

function submitFilters() {
    router.get('/admin/partners/kyc/pending', filters, {
        preserveState: true,
        replace: true,
    });
}

function verifyKyc(partner) {
    if (!partner.verify_kyc_url) {
        return;
    }
    if (window.confirm(`Verify KYC for ${partner.name}? They can then be assigned to bookings.`)) {
        router.post(partner.verify_kyc_url);
    }
}
</script>

<template>
    <AdminLayout title="Partner KYC" subtitle="Partners awaiting document review">
        <Head title="Partner KYC — pending review" />

        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h2 class="h5 mb-1">Pending KYC</h2>
                    <p class="text-secondary mb-0">
                        Assigning bookings to a partner requires
                        <strong>verified</strong> KYC. Review documents and verify or reject from the partner detail page.
                    </p>
                </div>
                <Link href="/admin/partners" class="btn btn-outline-secondary">All partners</Link>
            </div>
            <form class="filter-grid mt-4" @submit.prevent="submitFilters">
                <input
                    v-model="filters.search"
                    class="form-control"
                    placeholder="Search name, phone, email"
                >
                <button class="btn btn-outline-primary" type="submit">Search</button>
            </form>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Submitted</th>
                            <th>KYC</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="partner in partners.data" :key="partner.id">
                            <td class="fw-semibold">{{ partner.name }}</td>
                            <td>{{ partner.phone }}</td>
                            <td>{{ partner.city_name || '—' }}</td>
                            <td class="text-secondary small">{{ partner.kyc_submitted_at || '—' }}</td>
                            <td><StatusBadge :value="partner.kyc_status" /></td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap gap-2 justify-content-end">
                                    <Link :href="partner.show_url" class="btn btn-sm btn-primary">Review</Link>
                                    <button
                                        v-if="partner.verify_kyc_url"
                                        type="button"
                                        class="btn btn-sm btn-success"
                                        @click="verifyKyc(partner)"
                                    >
                                        Verify
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!partners.data.length">
                            <td colspan="6" class="text-center text-secondary py-5">
                                No pending KYC submissions.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <PaginationLinks v-if="partners.links?.length > 3" :links="partners.links" />
        </div>
    </AdminLayout>
</template>

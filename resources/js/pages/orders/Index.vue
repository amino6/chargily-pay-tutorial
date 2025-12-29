<template>
    <Head title="Orders" />

    <AppLayout>
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <h1 class="mb-4 text-2xl">Orders</h1>
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="border-b p-2 text-left">Checkout id</th>
                        <th class="border-b p-2 text-left">Amount</th>
                        <th class="border-b p-2 text-left">Status</th>
                        <th class="border-b p-2 text-left">Paid at</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="order in orders" :key="order.id">
                        <td class="border-b p-2">{{ order.checkout_id }}</td>
                        <td class="border-b p-2">{{ order.amount }}</td>
                        <td class="border-b p-2">{{ order.status }}</td>
                        <td class="border-b p-2">
                            {{ fmtDate(order.paid_at) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    orders: Object,
});

function fmtDate(s) {
    if (!s) return '-';
    const d = new Date(s);
    return isNaN(d) ? s : d.toLocaleString();
}
</script>

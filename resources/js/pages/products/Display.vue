<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12"
    >
        <div class="flex h-64 w-64 items-center justify-center" v-if="loading">
            <div
                class="h-12 w-12 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent"
            ></div>
        </div>

        <div
            class="w-full max-w-lg space-y-5 rounded-lg bg-white p-6 shadow"
            v-else
        >
            <div>
                <h1 class="text-xl font-medium text-gray-800">
                    {{ product.name }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ product.description }}
                </p>
            </div>

            <div class="text-2xl font-semibold text-gray-900">
                {{ formatCurrency(product.amount) }}
            </div>

            <form @submit.prevent="checkout" class="space-y-4">
                <div v-if="form.errors.default" class="mb-4 text-red-600">
                    {{ form.errors.default }}
                </div>
                <p v-if="form.errors.amount" class="mt-1 text-xs text-red-600">
                    {{ form.errors.amount }}
                </p>

                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full cursor-pointer rounded bg-indigo-600 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span>Pay Now</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const product = {
    name: 'Product Name',
    description: 'Product Description',
    amount: 3000,
};

const loading = ref(false);
const form = useForm({
    amount: product.amount,
    product_name: product.name,
});

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-DZ', {
        style: 'currency',
        currency: 'DZD',
    }).format(amount);
}

function checkout() {
    loading.value = true;
    form.post('/checkout', {
        onError() {
            loading.value = false;
        },
        preserveState: true,
    });
}
</script>

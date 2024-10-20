<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Container from '@/Components/Container.vue';
import ResponsiveCard from '@/Components/ResponsiveCard.vue';
import CreatePurchaseOrder from './CreatePurchaseOrder.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Create Purchase Order" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl leading-tight">Create Purchase Order</h2>
        </template>
        <Container :spaced-mobile="false">
            <div class="py-12">
                <ResponsiveCard>
                    <CreatePurchaseOrder />
                </ResponsiveCard>
            </div>
        </Container>
    </AuthenticatedLayout>
</template>

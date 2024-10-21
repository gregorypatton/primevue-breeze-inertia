<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Container from '@/Components/Container.vue';
import ResponsiveCard from '@/Components/ResponsiveCard.vue';
import PurchaseOrderForm from './PurchaseOrderForm.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Purchase Order Builder" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl leading-tight">Purchase Order Builder</h2>
        </template>
        <Container :spaced-mobile="false">
            <div class="py-12">
                <ResponsiveCard>
                    <PurchaseOrderForm />
                </ResponsiveCard>
            </div>
        </Container>
    </AuthenticatedLayout>
</template>

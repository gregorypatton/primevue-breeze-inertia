<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import Container from '@/Components/Container.vue';
    import ResponsiveCard from '@/Components/ResponsiveCard.vue';
    import CreatePurchaseOrder from './PurchaseOrders/CreatePurchaseOrder.vue';
</script>

<template>

    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl leading-tight">Create Purchase Order</h2>
            <h4 class="text-md leading-tight">by Part</h4>
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
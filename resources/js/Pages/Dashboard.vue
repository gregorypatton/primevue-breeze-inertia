<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Container from '@/Components/Container.vue';
import UploadAmazonFiles from '@/Pages/Imports/Partials/UploadAmazonFiles.vue';
import ResponsiveCard from '@/Components/ResponsiveCard.vue';
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl leading-tight">Dashboard</h2>
        </template>
        <Container :spaced-mobile="false">
            <div class="py-12">
                <ResponsiveCard>
                    <UploadAmazonFiles/>
                </ResponsiveCard>
            </div>
        </Container>
    </AuthenticatedLayout>
</template>

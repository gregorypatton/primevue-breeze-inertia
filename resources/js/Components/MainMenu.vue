<template>
    <div class="card flex justify-center">
        <Drawer v-model:visible="visible">
            <template #container="{ closeCallback }">
                <div class="flex flex-col h-full">
                    <div class="flex items-center justify-between px-6 pt-4 shrink-0">
                        <span class="inline-flex items-center gap-2">
                            <ApplicationLogo class="w-10 h-10" />
                            <span class="font-semibold text-2xl text-primary">Patton IMS</span>
                        </span>
                        <span>
                            <Button type="button" @click="closeCallback" icon="pi pi-times" rounded outlined></Button>
                        </span>
                    </div>
                    <div class="overflow-y-auto">
                        <ul class="list-none p-4 m-0">
                            <li v-for="item in menuItems" :key="item.label">
                                <div
                                    v-ripple
                                    v-styleclass="{
                                        selector: '@next',
                                        enterFromClass: 'hidden',
                                        enterActiveClass: 'animate-slidedown',
                                        leaveToClass: 'hidden',
                                        leaveActiveClass: 'animate-slideup'
                                    }"
                                    class="p-4 flex items-center justify-between text-surface-500 dark:text-surface-400 cursor-pointer p-ripple"
                                >
                                    <span class="font-medium">{{ item.label }}</span>
                                    <i class="pi pi-chevron-down"></i>
                                </div>
                                <ul class="list-none p-0 m-0 overflow-hidden">
                                    <li v-for="subItem in item.items" :key="subItem.label">
                                        <Link
                                            :href="route(subItem.route)"
                                            v-ripple
                                            class="flex items-center cursor-pointer p-4 rounded text-surface-700 hover:bg-surface-100 dark:text-surface-0 dark:hover:bg-surface-800 duration-150 transition-colors p-ripple"
                                            :class="{ 'bg-primary-100 dark:bg-primary-900': $page.url.startsWith(route(subItem.route)) }"
                                        >
                                            <i :class="subItem.icon" class="mr-2"></i>
                                            <span class="font-medium">{{ subItem.label }}</span>
                                        </Link>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-auto">
                        <hr class="mb-4 mx-4 border-t border-0 border-surface-200 dark:border-surface-700" />
                        <a v-ripple class="m-4 flex items-center cursor-pointer p-4 gap-2 rounded text-surface-700 hover:bg-surface-100 dark:text-surface-0 dark:hover:bg-surface-800 duration-150 transition-colors p-ripple">
                            <Avatar image="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png" shape="circle" />
                            <span class="font-bold">{{ $page.props.auth.user.name }}</span>
                        </a>
                    </div>
                </div>
            </template>
        </Drawer>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Drawer from 'primevue/drawer';
import Avatar from 'primevue/avatar';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    visible: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['update:visible']);

const visible = computed({
    get: () => props.visible,
    set: (value) => emit('update:visible', value)
});

const menuItems = [
    {
        label: 'Dashboard',
        items: [
            { label: 'Dashboard', icon: 'pi pi-home', route: 'dashboard' }
        ]
    },
    {
        label: 'Purchasing',
        items: [
            { label: 'Create Purchase Order', icon: 'pi pi-plus', route: 'purchase-orders.create' },
            { label: 'Receive Purchase Order', icon: 'pi pi-inbox', route: 'purchase-orders.receive' },
            { label: 'View Purchase Orders', icon: 'pi pi-list', route: 'purchase-orders.index' }
        ]
    },
    {
        label: 'Production',
        items: [
            { label: 'Create Work Order', icon: 'pi pi-plus', route: 'work-orders.create' },
            { label: 'Modify Work Order', icon: 'pi pi-pencil', route: 'work-orders.modify' }
        ]
    }
];
</script>

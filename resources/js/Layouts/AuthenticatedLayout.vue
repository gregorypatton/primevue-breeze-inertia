<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import Button from 'primevue/button';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Container from '@/Components/Container.vue';
import LinksMenu from '@/Components/LinksMenu.vue';
import LinksMenuBar from '@/Components/LinksMenuBar.vue';
import ToggleThemeButton from '@/Components/ToggleThemeButton.vue';
import MainMenu from '@/Components/MainMenu.vue';

const currentRoute = route().current();
const logoutForm = useForm({});
function logout() {
    logoutForm.post(route('logout'));
}

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

// Top-level menu items for the top bar
const topMenuItems = computed(() =>
    menuItems.map(item => ({
        label: item.label,
        route: item.items[0].route,
        active: currentRoute.startsWith(item.label.toLowerCase()),
    }))
);

// User menu (desktop)
const userMenu = ref(null);
const userMenuItems = [
    {
        label: 'Profile',
        route: route('profile.edit'),
        icon: 'pi pi-fw pi-user',
    },
    {
        label: 'Log Out',
        icon: 'pi pi-fw pi-sign-out',
        command: () => {
            logout();
        },
    },
];
const toggleUserMenu = (event) => {
    userMenu.value.toggle(event);
};

// Main menu state
const mainMenuOpen = ref(false);
const toggleMainMenu = () => {
    mainMenuOpen.value = !mainMenuOpen.value;
};
</script>

<template>
    <div>
        <div class="min-h-screen">
            <nav
                class="bg-surface-0 dark:bg-surface-900 border-b"
                :class="
                    $slots.header
                        ? 'border-surface-100 dark:border-surface-800'
                        : 'border-surface-0 dark:border-surface-900 shadow'
                "
            >
                <!-- Primary Navigation Menu -->
                <Container>
                    <LinksMenuBar
                        :model="topMenuItems"
                        :pt="{
                            root: {
                                class: 'px-0 py-3 border-0 rounded-none',
                            },
                            button: {
                                class: 'hidden',
                            },
                        }"
                    >
                        <template #start>
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center mr-5">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-10 w-auto fill-current text-surface-900 dark:text-surface-0"
                                    />
                                </Link>
                            </div>

                            <!-- Main Menu Toggle Button -->
                            <Button
                                icon="pi pi-bars"
                                @click="toggleMainMenu"
                                class="p-button-text"
                            />
                        </template>
                        <template #end>
                            <div class="flex items-center md:ms-6">
                                <ToggleThemeButton
                                    text
                                    severity="secondary"
                                    rounded
                                />
                                <!-- User Dropdown Menu -->
                                <div class="ms-3 relative">
                                    <LinksMenu
                                        :model="userMenuItems"
                                        popup
                                        ref="userMenu"
                                        class="shadow"
                                    />
                                    <Button
                                        text
                                        size="small"
                                        severity="secondary"
                                        @click="toggleUserMenu($event)"
                                    >
                                        <span class="text-base">
                                            {{ $page.props.auth.user.name }}
                                        </span>
                                        <i class="pi pi-angle-down ml-1"></i>
                                    </Button>
                                </div>
                            </div>
                        </template>
                    </LinksMenuBar>
                </Container>
            </nav>

            <!-- Main Menu -->
            <MainMenu v-model:visible="mainMenuOpen" />

            <!-- Page Heading -->
            <header
                class="bg-surface-0 dark:bg-surface-900 shadow"
                v-if="$slots.header"
            >
                <Container>
                    <div class="py-6">
                        <slot name="header" />
                    </div>
                </Container>
            </header>

            <!-- Page Content -->
            <Toast />
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

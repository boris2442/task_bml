<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Folder,
    LayoutGrid,
    LogIn,
    LogOut,
    History,
    CheckCircle,
    Users,
} from 'lucide-vue-next';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';
import { dashboard } from '@/routes';
import { computed } from 'vue';
import approbations from '@/routes/approbations';
import presence from '@/routes/presence';
import dashboardFrontend from '@/routes/employe';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Navigation principale selon le rôle
const mainNavItems = computed<NavItem[]>(() => {
    // Items communs à tous
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard().url,
            icon: LayoutGrid,
        },
    ];

    // Items pour les employés
    if (user.value?.role === 'employe') {
        items.push(
            {
                title: 'Signaler arrivée',
                href: presence.arrivee().url,
                icon: LogIn,
            },
            {
                title: 'Signaler départ',
                href: presence.depart().url,
                icon: LogOut,
            },
            {
                title: 'Mon historique',
                href: presence.historique().url,
                icon: History,
            },
        );
    }

    // Items pour les admins
    if (user.value?.role === 'admin') {
        items.push(
            {
                title: 'Mon Dashboard',
                href: dashboardFrontend.dashboard().url,
                icon: LayoutGrid,
            },
            {
                title: 'Approbations',
                href: '/admin/approbations',
                icon: CheckCircle,
            },
        );
        // Tu peux ajouter d'autres items admin ici
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

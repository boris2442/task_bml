<script setup lang="ts">
// import { Link } from '@inertiajs/vue3';
import { Link, usePage } from '@inertiajs/vue3';
// import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import {
    BookOpen,
    Folder,
    LayoutGrid,
    LogIn, // Pour arrivée
    LogOut, // Pour départ
    History, // Pour historique
    CheckCircle, // Pour approbations
    Users, // Pour gestion employés (optionnel)
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
// stocker les data  de utilisateurdans une variable user
//import { useAuth } from '@/composables/useAuth';
import presence from '@/routes/presence';
import dashboardFrontend from '@/routes/employe';
// const mainNavItems: NavItem[] = [
//     {
//         title: 'Dashboard',
//         href: dashboard(),
//         icon: LayoutGrid,
//     },

// ];
const page = usePage();
const user = computed(() => page.props.auth?.user);
// Navigation principale selon le rôle
const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard().url,
            icon: LayoutGrid,
        },

        // Si c'est un employé
        // if (user.role === 'employe') {
        // items.push(
        // ...(user.value?.role === 'employe'
        //     ? [
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

        {
            title: 'Mon Dashboard',
            href: dashboardFrontend.dashboard().url,
            icon: LayoutGrid,
        },

        //   ]
        // : []),
        // );
        // }

        // Si c'est un admin
        // if (user.role === 'admin') {
        // items.push(
        // {
        //     ...(user.value?.role === 'admin'
        //        ? [
        //     title: 'Approbations',
        //     href: admin().approbations.url,
        //     icon: CheckCircle,
        // },
        // {
        //     title: 'Employés',
        //     href: admin().employes.url,
        //     icon: Users,
        // },
        //     : []),
        // );
        // }
    ];
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

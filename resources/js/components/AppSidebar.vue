<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Archive, BookOpen, LayoutGrid, Package, ShoppingCart, Users } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';


const page = usePage();
const rootProps = page.props;

const role = rootProps.auth.user?.role;
const homeHref = role === 2 || role === 'admin' ? '/dashboard' : '/sales';
const mainNavItems: NavItem[] = [];
if (role === 'admin' || role === 2) {
        mainNavItems.push(
            {
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutGrid,
            } as NavItem,
            {
                title: 'Users',
                href: '/users',
                icon: Users,
            } as NavItem,
            {
                title: 'Products',
                href: '/products',
                icon: Package,
            } as NavItem,
            {
                title: 'Sales Records',
                href: '/sales',
                icon: ShoppingCart,
            } as NavItem,
            {
                title: 'Archive',
                href: '/archive',
                icon: Archive,
            } as NavItem,
        );
    } else if (role === 0 || role === 'guest') {
        mainNavItems.push(
            {
                title: 'Enter Sales',
                href: '/sales',
                icon: ShoppingCart,
            } as NavItem,
        );
    } else {
        mainNavItems.push(
            {
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutGrid,
            } as NavItem,
            {
                title: 'Sales Records',
                href: '/sales',
                icon: ShoppingCart,
            } as NavItem,
        );
    }
const footerNavItems: NavItem[] = [
    {
        title: 'Help',
        href: 'https://laravel.com/docs/starter-kits',
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
                        <Link :href="homeHref">
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

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, Building2, FolderGit2, LayoutGrid, Users, Building, Package } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
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
import { dashboard } from '@/routes';
import DeptController from '@/actions/App/Http/Controllers/DeptController';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: '所属マスタ',
        href: DeptController.index.url(),
        icon: Building2,
    },
    {
        title: '社員マスタ',
        href: EmployeeController.index.url(),
        icon: Users,
    },
    {
        title: '得意先マスタ',
        href: CustomerController.index.url(),
        icon: Building,
    },
    {
        title: '商品マスタ',
        href: ProductController.index.url(),
        icon: Package,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
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

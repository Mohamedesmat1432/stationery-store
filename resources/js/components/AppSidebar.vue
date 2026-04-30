<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, FolderGit2, LayoutGrid, Package, ShoppingCart, Users, Settings, Shield, ShieldCheck, UserCircle } from 'lucide-vue-next';
import { computed } from 'vue';
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
import type { NavItem } from '@/types';
import type { Auth } from '@/types/auth';

const page = usePage();
const permissions = computed<string[]>(() => (page.props.auth as Auth).permissions || []);
const hasPermission = (permission: string) => permissions.value.includes(permission);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (hasPermission('view_products')) {
        items.push({
            title: 'Products',
            href: '/admin/products',
            icon: Package,
        });
    }

    if (hasPermission('view_orders')) {
        items.push({
            title: 'Orders',
            href: '/admin/orders',
            icon: ShoppingCart,
        });
    }

    if (hasPermission('view_users') || hasPermission('view_roles')) {
        const accessControlItems: NavItem[] = [];
        
        if (hasPermission('view_users')) {
            accessControlItems.push({
                title: 'Users',
                href: '/admin/users',
                icon: Users,
            });
        }
        
        if (hasPermission('view_roles')) {
            accessControlItems.push({
                title: 'Roles & Permissions',
                href: '/admin/roles',
                icon: ShieldCheck,
            });
        }

        items.push({
            title: 'Access Control',
            href: '#',
            icon: Shield,
            items: accessControlItems,
        });
    }

    if (hasPermission('view_customers') || hasPermission('view_customer_groups')) {
        const crmItems: NavItem[] = [];

        if (hasPermission('view_customers')) {
            crmItems.push({
                title: 'Customers',
                href: '/admin/customers',
                icon: UserCircle,
            });
        }

        if (hasPermission('view_customer_groups')) {
            crmItems.push({
                title: 'Customer Groups',
                href: '/admin/customer-groups',
                icon: Users,
            });
        }

        items.push({
            title: 'CRM',
            href: '#',
            icon: UserCircle,
            items: crmItems,
        });
    }

    if (hasPermission('manage_settings')) {
        items.push({
            title: 'System Settings',
            href: '/admin/settings',
            icon: Settings,
        });
    }

    return items;
});

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
    <Sidebar collapsible="icon" variant="inset" :side="page.props.locale === 'ar' ? 'right' : 'left'">
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

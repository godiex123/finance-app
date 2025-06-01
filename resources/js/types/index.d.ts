import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}
export type BreadcrumbItemType = BreadcrumbItem;

export interface Category {
    id: number;
    name: string;
    type: string;
    deleted_at: string | null;
}

export interface Year {
    id: number;
}

export interface Month {
    id: number;
    name: string;
}

export interface FixedIncome {
    id: number;
    category_id: number;
    amount: number;
    name: string;
    comment: string;
}

export interface FixedExpense {
    id: number;
    category_id: number;
    amount: number;
    name: string;
    is_essential: boolean;
    comment: string;
}

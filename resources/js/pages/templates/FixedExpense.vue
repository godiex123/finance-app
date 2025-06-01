<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, Category, FixedExpense } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

import HeadingSmall from '@/components/HeadingSmall.vue';
const page = usePage();
const editing = ref(false);
const isAlertOpen = ref(false);
const fixedExpenseId = ref<number | null>(null);
const fixedExpenseToDelete = ref(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Configurar mis gastos fijos',
        href: '/fixed-expense',
    },
];

defineProps<{
    categories: Category[],
    fixedExpenses: FixedExpense[],
}>();

const form = useForm({
    name: null,
    amount: null,
    category_id: null,
    is_essential: false,
    comment: null,
});

const submit = () => {
    if (editing.value && fixedExpenseId.value !== null) {
        form.put(route('fixed-expense.update', fixedExpenseId.value), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                editing.value = false
                fixedExpenseId.value = null
            },
            onFinish: () => {
                if (page.props.flash.success) {
                    toast.success(page.props.flash.success)
                }
            },
            onError: (errors) => {
                if (errors.error) toast.error(errors.error);
            },
        });
    } else {
        form.post(route('fixed-expense.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset()
            },
            onFinish: () => {
                if (page.props.flash.success) {
                    toast.success(page.props.flash.success)
                }
            },
            onError: (errors) => {
                if (errors.error) toast.error(errors.error);
            },
        });
    }
};

const edit = (fixedExpense: {id: number; name: string; amount: number; category_id: number; is_essential: boolean; comment: string|null;}) => {
    form.name = fixedExpense.name
    form.amount = fixedExpense.amount
    form.category_id = fixedExpense.category_id
    form.is_essential = fixedExpense.is_essential
    form.comment = fixedExpense.comment
    fixedExpenseId.value = fixedExpense.id
    editing.value = true
}

const openAlertDialog = (fixedExpense) => {
    fixedExpenseToDelete.value = fixedExpense
    isAlertOpen.value = true
}

const confirmDelete = () => {
    form.delete(route('fixed-expense.destroy', fixedExpenseToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isAlertOpen.value = false
            fixedExpenseToDelete.value = null
        },
        onFinish: () => {
            if (page.props.flash.success) {
                toast.success(page.props.flash.success)
            }
        }
    });
}
</script>

<template>
    <Head title="Ingresos Fijos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">

                    <h3 class="scroll-m-20 text-xl font-semibold tracking-tight mb-3">Nuevo gasto fijo</h3>
                    <hr class="mb-5">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="name">Nombre</Label>
                            <Input id="name" class="mt-1 block w-full" required autocomplete="name" placeholder="Ej: Arriendo Dpto" v-model="form.name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="amount">Monto</Label>
                            <Input type="number" step="0.2" id="amount" class="mt-1 block w-full" required autocomplete="amount" placeholder="Ej: 480000" v-model="form.amount" />
                            <InputError class="mt-2" :message="form.errors.amount" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="category">Categoría</Label>
                            <Select id="category" v-model="form.category_id" required>
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Seleccione" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="form.errors.category_id" />
                        </div>

                        <div class="flex items-center gap-2">
                            <Switch id="is_essential" v-model="form.is_essential" />
                            <Label for="is_essential">¿Es un gasto esencial?</Label>
                            <InputError class="mt-2" :message="form.errors.is_essential" />
                        </div>

                        <div class="grid w-full gap-1.5">
                            <Label for="comment">Observación</Label>
                            <Textarea id="comment" placeholder="Escribe alguna observación del gasto aquí." v-model="form.comment" />
                            <InputError class="mt-2" :message="form.errors.comment" />
                            <p class="text-sm text-muted-foreground">
                                Este campo es opcional.
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button v-if="editing"  :disabled="form.processing" class="bg-teal-500 hover:bg-teal-600">Modificar</Button>
                            <Button v-else :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600">Crear</Button>
                        </div>
                    </form>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <h3 class="scroll-m-20 text-xl font-semibold tracking-tight mb-3">Gastos informados</h3>
                    <hr class="mb-5">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="text-center">Nombre</TableHead>
                                <TableHead class="text-center">Monto</TableHead>
                                <TableHead class="text-center">Categoría</TableHead>
                                <TableHead class="text-center">Observación</TableHead>
                                <TableHead class="text-center"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="fixedExpense in fixedExpenses" :key="fixedExpense.id">
                                <TableCell class="text-center">
                                    {{ fixedExpense.name }} &nbsp;
                                    <Badge v-if="fixedExpense.is_essential" class="bg-green-600">Esencial</Badge>
                                </TableCell>
                                <TableCell class="text-center">
                                    {{ fixedExpense.amount }}
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge class="bg-blue-500">{{ fixedExpense.category.name }}</Badge>
                                </TableCell>
                                <TableCell class="text-center">
                                    {{ fixedExpense.comment }}
                                </TableCell>
                                <TableCell class="text-center">
                                    <Button variant="link" @click="edit(fixedExpense)">Editar</Button>
                                    <Button variant="link" class="text-destructive" @click="openAlertDialog(fixedExpense)">Eliminar</Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <AlertDialog v-model:open="isAlertOpen">
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>¿Está absolutamente seguro?</AlertDialogTitle>
                                <AlertDialogDescription>
                                    <p>Está por eliminar el siguiente gasto fijo:</p><br><h4 class="scroll-m-20 text-xl font-semibold tracking-tight">{{ fixedExpenseToDelete.name }}</h4>.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>No, mantener</AlertDialogCancel>
                                <AlertDialogAction @click="confirmDelete" class="bg-red-500 hover:bg-red-700">Si, eliminar</AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>

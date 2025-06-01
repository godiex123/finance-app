<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { type Category } from '@/types';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
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

defineProps<{
    categories: Category[],
}>();

const form = useForm({
    name: null,
    type: null,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mantenedor de categorías',
        href: '/settings/category',
    },
];

const page = usePage();
const editing = ref(false);
const categoryId = ref<number | null>(null);
const isAlertOpen = ref(false);
const categoryToDelete = ref(null)

const submit = () => {
    if (editing.value && categoryId.value !== null) {
        form.put(route('category.update', categoryId.value), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                editing.value = false
                categoryId.value = null
            },
            onFinish: () => {
                if (page.props.flash.success) {
                    toast.success(page.props.flash.success)
                }
            },
            onError: (errors) => {
                if (errors.msg) toast.error(errors.msg);
            },
        });
    } else {
        form.post(route('category.store'), {
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
                if (errors.msg) toast.error(errors.msg);
            },
        });
    }
};

const confirmDelete = () => {
    form.delete(route('category.destroy', categoryToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isAlertOpen.value = false
            categoryToDelete.value = null
        },
        onFinish: () => {
            if (page.props.flash.success) {
                toast.success(page.props.flash.success)
            }
        }
    });
}

const edit = (category: {id: number; name: string; type: string}) => {
    form.name = category.name
    form.type = category.type
    categoryId.value = category.id
    editing.value = true
}

const openAlertDialog = (category) => {
    categoryToDelete.value = category
    isAlertOpen.value = true
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Mantenedor de Categorías" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Mantenedor de Categorías" description="Gestiona las categorías de ingreso y gastos del sistema" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nombre</Label>
                        <Input id="name" class="mt-1 block w-full" required autocomplete="name" placeholder="Nombre de la categoría" v-model="form.name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="type">Tipo</Label>
                        <Select id="type" v-model="form.type" required>
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Seleccione un tipo" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Tipos</SelectLabel>
                                    <SelectItem value="income">
                                        Ingreso
                                    </SelectItem>
                                    <SelectItem value="expense">
                                        Gasto
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError class="mt-2" :message="form.errors.type" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button v-if="editing"  :disabled="form.processing" class="bg-teal-500 hover:bg-teal-600">Modificar</Button>
                        <Button v-else :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600">Crear</Button>
                    </div>
                </form>
            </div>

            <div class="flex flex-col space-y-6">
                <h3 class="scroll-m-20 text-2xl font-semibold tracking-tight">Listado de categorías</h3>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="text-center">Nombre</TableHead>
                            <TableHead class="text-center">Tipo</TableHead>
                            <TableHead class="text-center"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="category in categories" :key="category.id">
                            <TableCell class="text-center">
                                {{ category.name }}
                            </TableCell>
                            <TableCell class="text-center">
                                {{ category.type === 'income' ? 'Ingreso' : 'Gasto' }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Button variant="link" @click="edit(category)">Editar</Button>
                                <Button variant="link" class="text-destructive" @click="openAlertDialog(category)">Eliminar</Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <AlertDialog v-model:open="isAlertOpen">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>¿Está absolutamente seguro?</AlertDialogTitle>
                            <AlertDialogDescription>
                                <p>Está por eliminar/deshabilitar la siguiente categoría:</p><br><h4 class="scroll-m-20 text-xl font-semibold tracking-tight">{{ categoryToDelete.name }}</h4>.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>No, mantener</AlertDialogCancel>
                            <AlertDialogAction @click="confirmDelete" class="bg-red-500 hover:bg-red-700">Si, eliminar</AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>

            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>

</style>

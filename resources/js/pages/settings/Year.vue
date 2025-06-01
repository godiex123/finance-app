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
import { type Year } from '@/types';
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
    years: Year[],
}>();

const form = useForm({
    id: null,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mantenedor de Años',
        href: '/settings/year',
    },
];

const page = usePage();
const isAlertOpen = ref(false);
const yearToDelete = ref(null)

const submit = () => {
    form.post(route('year.store'), {
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
};

const confirmDelete = () => {
    form.delete(route('year.destroy', yearToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isAlertOpen.value = false
            yearToDelete.value = null
        },
        onFinish: () => {
            if (page.props.flash.success) {
                toast.success(page.props.flash.success)
            }
        }
    });
}

const openAlertDialog = (year) => {
    yearToDelete.value = year
    isAlertOpen.value = true
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Mantenedor de Años" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Mantenedor de Años" description="Gestiona los años disponibles en el sistema" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="id">Año</Label>
                        <Input type="number" id="id" class="mt-1 block w-full" required autocomplete="id" placeholder="Ej. 2025" v-model="form.id" />
                        <InputError class="mt-2" :message="form.errors.id" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600">Crear</Button>
                    </div>
                </form>
            </div>

            <div class="flex flex-col space-y-6">
                <h3 class="scroll-m-20 text-2xl font-semibold tracking-tight">Listado de categorías</h3>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="text-center">Año</TableHead>
                            <TableHead class="text-center"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="year in years" :key="year.id">
                            <TableCell class="text-center">
                                {{ year.id }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Button variant="link" class="text-destructive" @click="openAlertDialog(year)">Eliminar</Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <AlertDialog v-model:open="isAlertOpen">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>¿Está absolutamente seguro?</AlertDialogTitle>
                            <AlertDialogDescription>
                                <p>Está por eliminar el siguiente año:</p><br><h4 class="scroll-m-20 text-xl font-semibold tracking-tight">{{ yearToDelete.id }}</h4>.
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

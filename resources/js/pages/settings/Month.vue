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
import { type Month } from '@/types';
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
    months: Month[],
}>();

const form = useForm({
    id: null,
    name: null,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mantenedor de Meses',
        href: '/settings/month',
    },
];

const page = usePage();
const editing = ref(false);
const monthId = ref<number | null>(null);
const monthToDelete = ref(null);
const isAlertOpen = ref(false);

const submit = () => {
    if (editing.value && monthId.value !== null) {
        form.put(route('month.update', monthId.value), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                editing.value = false
                monthId.value = null
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
        form.post(route('month.store'), {
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

const confirmDelete = () => {
    form.delete(route('month.destroy', monthToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isAlertOpen.value = false
            monthToDelete.value = null
        },
        onFinish: () => {
            if (page.props.flash.success) {
                toast.success(page.props.flash.success)
            }
        }
    });
}

const edit = (month: {id: number; name: string}) => {
    form.id = month.id
    form.name = month.name
    monthId.value = month.id
    editing.value = true
}

const openAlertDialog = (year) => {
    monthToDelete.value = year
    isAlertOpen.value = true
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Mantenedor de Meses" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Mantenedor de Meses" description="Gestiona los meses disponibles en el sistema" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="id">Número del mes</Label>
                        <Input type="number" id="id" class="mt-1 block w-full" required autocomplete="id" placeholder="Ej. 1" v-model="form.id" />
                        <InputError class="mt-2" :message="form.errors.id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Nombre del mes</Label>
                        <Input id="name" class="mt-1 block w-full" required autocomplete="name" placeholder="Ej. Enero" v-model="form.name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button v-if="editing"  :disabled="form.processing" class="bg-teal-500 hover:bg-teal-600">Modificar</Button>
                        <Button v-else :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600">Crear</Button>
                    </div>
                </form>
            </div>

            <div class="flex flex-col space-y-6">
                <h3 class="scroll-m-20 text-2xl font-semibold tracking-tight">Listado de Meses</h3>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="text-center">Número</TableHead>
                            <TableHead class="text-center">Nombre</TableHead>
                            <TableHead class="text-center"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="month in months" :key="month.id">
                            <TableCell class="text-center">
                                {{ month.id }}
                            </TableCell>
                            <TableCell class="text-center">
                                {{ month.name }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Button variant="link" @click="edit(month)">Editar</Button>
                                <Button variant="link" class="text-destructive" @click="openAlertDialog(month)">Eliminar</Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <AlertDialog v-model:open="isAlertOpen">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>¿Está absolutamente seguro?</AlertDialogTitle>
                            <AlertDialogDescription>
                                <p>Está por eliminar el siguiente mes:</p><br><h4 class="scroll-m-20 text-xl font-semibold tracking-tight">{{ monthToDelete.id }} - {{ monthToDelete.name }}</h4>.
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

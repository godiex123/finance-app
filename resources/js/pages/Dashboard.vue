<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'vue-sonner';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import BudgetTimeLine  from '@/components/Budget/BudgetTimeLine.vue';
import { DollarSign } from 'lucide-vue-next';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';


const props = defineProps<{
    budgetId: number,
    showBudgetButton: boolean,
    year: number,
    months: [],
    budgetedIncomes: [],
    totalBudgetedIncomes: number,
    budgetedExpenses: [],
    totalBudgetedExpenses: number,
    incomeCategories: [],
    expenseCategories: [],
    transactions: [],
    totalIncomeTransactions: number,
    totalExpenseTransactions: number,
}>();
const selectedMonth = ref('')
const loading = ref(false)
const showModal = ref(false)
const page = usePage();
const openModalIncome = ref(false)
const openModalExpense = ref(false)
const type = ref('');
const budgeteds = ref([]);
const typeActive = ref('expense');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

function generar() {
    loading.value = true
    router.post('/budget', { year: props.year }, {
        onSuccess: () => {
            loading.value = false

        },
        onFinish: () => {
            if (page.props.flash.success) {
                toast.success(page.props.flash.success)
            }
        },
        onError: () => {
            loading.value = false
            if (errors.error) toast.error(errors.error);
        }
    })
}
const estimado = 5000000
const real = 4300000
const diferencia = real - estimado


const loadBudget = (id) => {
    console.log();
    router.get('/budget', { bid: id }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
        }
    })
}

const form = useForm({
    budget_id: props.budgetId,
    name: null,
    amount: null,
    category_id: null,
    comment: null,
});

const submitIncome = () => {
    form.post('/budget/storeVariableIncome', {
        preserveState: true,
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

const submitExpense = () => {
    form.post('/budget/storeVariableExpense', {
        preserveState: true,
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

watch(type, (newType) => {
    if (!newType) return;

    router.get(
        '/transaction/getType',
        { type: newType, budgetId: props.budgetId },
        {
            preserveState: true,
            only: [],
            onSuccess: (page) => {
                budgeteds.value = page.props?.budgeteds || [];
            }
        }
    );
});

const formTransaction = useForm({
    budgeted_income_id: null,
    budgeted_expense_id: null,
    amount: null,
    description: null,
});

const submitTransaction = () => {
    formTransaction.post('/transaction/store', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            formTransaction.reset()
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

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-1 rounded-xl p-4">

            <BudgetTimeLine class="mx-auto" :months="months" @selectedMonth="id => loadBudget(id)" />

            <div class="grid auto-rows-min gap-1 md:grid-cols-2">
                <div class="relative min-h-[80vh]">
                    <div class="flex flex-col h-full gap-3 p-2">
                        <!-- Bloque de abajo -->
                        <div class="relative p-4">
                            <div class="rounded-xl border bg-card text-card-foreground shadow">
                                <div class="gap-y-1.5 p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                                    <h3 class="tracking-tight text-lg font-normal"> Saldo Actual </h3>
                                </div>
                                <div class="p-6 pt-0">
                                    <div :class="(totalIncomeTransactions - totalExpenseTransactions) < 0 ? 'text-5xl font-bold text-red-500' : 'text-5xl font-bold text-green-500'">
                                        ${{ (totalIncomeTransactions - totalExpenseTransactions).toLocaleString() }}
                                    </div>
                                    <!--<p class="text-xs text-muted-foreground"> +20.1% from last month </p>-->
                                </div>
                            </div>
                        </div>

                        <!-- Bloque de arriba -->
                        <div class="relative flex-1  p-4 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                            <h4 class="scroll-m-20 text-xl font-semibold tracking-tight text-orange-500 mb-3">
                                Últimas transacciones realizadas
                            </h4>
                            <hr class="mb-5">
                            <table class="min-w-full table-auto mt-4">
                                <thead>
                                <tr class="text-left text-sm text-gray-500 dark:text-gray-400">
                                    <th class="pb-2">Item</th>
                                    <th class="pb-2">Tipo</th>
                                    <th class="pb-2">Monto</th>
                                    <th class="pb-2">Descripción</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="transaction in transactions" :key="transaction.id">
                                    <td class="py-2 text-gray-800 dark:text-gray-200">
                                        {{ transaction.budgeted_income
                                        ? transaction.budgeted_income.name
                                        : transaction.budgeted_expense.name  }}
                                    </td>
                                    <td class="py-2 text-gray-800 dark:text-gray-200">
                                        {{ transaction.budgeted_income
                                        ? 'Ingreso'
                                        : 'Gasto'  }}
                                    </td>
                                    <td class="py-2 text-gray-800 dark:text-gray-200">${{ transaction.amount.toLocaleString() }}</td>
                                    <td class="py-2 text-gray-800 dark:text-gray-200">{{ transaction.description }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="relative min-h-[80vh]">
                    <div class="flex flex-col h-full gap-3 p-2">
                        <!-- Bloque de arriba -->
                        <div class="relative h-auto flex-1  p-4 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                            <div class="">
                                <div class="overflow-x-auto">
                                    <div class="flex justify-between">
                                        <h4 class="scroll-m-20 text-xl font-semibold tracking-tight text-orange-500">
                                            Ingresos
                                        </h4>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button v-if="!showBudgetButton" variant="link" class="text-green-600">
                                                    <DollarSign /> Agregar
                                                </Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>

                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Agregar ingreso variable</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        <hr class="mb-5">
                                                        <form @submit.prevent="submitIncome" class="space-y-6">
                                                            <div class="grid gap-2">
                                                                <Input id="id" type="hidden" v-model="form.budget_id" />
                                                                <Label for="name">Nombre</Label>
                                                                <Input id="name" class="mt-1 block w-full" required autocomplete="name" placeholder="Ej: Mi sueldo" v-model="form.name" />
                                                                <InputError class="mt-2" :message="form.errors.name" />
                                                            </div>

                                                            <div class="grid gap-2">
                                                                <Label for="amount">Monto</Label>
                                                                <Input type="number" step="0.2" id="amount" class="mt-1 block w-full" required autocomplete="amount" placeholder="Ej: 500000" v-model="form.amount" />
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
                                                                            <SelectItem v-for="incomeCategory in incomeCategories" :key="incomeCategory.id" :value="incomeCategory.id">
                                                                                {{ incomeCategory.name }}
                                                                            </SelectItem>
                                                                        </SelectGroup>
                                                                    </SelectContent>
                                                                </Select>
                                                                <InputError class="mt-2" :message="form.errors.category_id" />
                                                            </div>

                                                            <div class="grid w-full gap-1.5">
                                                                <Label for="comment">Observación</Label>
                                                                <Textarea id="comment" placeholder="Escribe alguna observación del ingreso aquí." v-model="form.comment" />
                                                                <InputError class="mt-2" :message="form.errors.comment" />
                                                                <p class="text-sm text-muted-foreground">
                                                                    Este campo es opcional.
                                                                </p>
                                                            </div>

                                                            <div class="flex items-center gap-4">
                                                                <Button :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600">Crear</Button>
                                                                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                                            </div>
                                                        </form>
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                    <hr class="mb-3">
                                    <div class="flex justify-between md:grid-cols-3 gap-4 mb-5">
                                        <div>
                                            <p class="text-sm text-gray-500">Previsto</p>
                                            <p class="text-4xl font-bold text-gray-800 dark:text-white">${{ totalBudgetedIncomes.toLocaleString() }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Real</p>
                                            <p class="text-4xl font-bold text-gray-800 dark:text-white">${{ totalIncomeTransactions.toLocaleString() }}</p>
                                        </div>
                                    </div>
                                    <table class="min-w-full table-auto mt-4">
                                        <thead>
                                        <tr class="text-left text-sm text-gray-500 dark:text-gray-400">
                                            <th class="pb-2"></th>
                                            <th class="pb-2">Previsto</th>
                                            <th class="pb-2">Real</th>
                                            <th class="pb-2">Difer.</th>
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        <tr v-for="budgetedIncome in budgetedIncomes" :key="budgetedIncome.id">
                                            <td class="py-2 text-gray-800 dark:text-gray-200">
                                                {{ budgetedIncome.name }}
                                            </td>
                                            <td class="py-2 text-gray-800 dark:text-gray-200">
                                                ${{ budgetedIncome.amount.toLocaleString() }}
                                            </td>
                                            <td class="py-2 text-gray-800 dark:text-gray-200">
                                                ${{ (budgetedIncome.real_amount ?? 0).toLocaleString() }}
                                            </td>
                                            <td :class="(budgetedIncome.amount - (budgetedIncome.real_amount ?? 0)) < 0 ? 'py-2 text-red-500 dark:text-red-200' : 'py-2 text-green-500 dark:text-green-200'">
                                                ${{ (budgetedIncome.amount - (budgetedIncome.real_amount ?? 0)).toLocaleString() }}
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <!-- Bloque de abajo -->
                        <div class="relative flex-1 p-4 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                            <div class="overflow-x-auto">
                                <div class="flex justify-between">
                                    <h4 class="scroll-m-20 text-xl font-semibold tracking-tight text-orange-500">
                                        Gastos
                                    </h4>
                                    <AlertDialog>
                                        <AlertDialogTrigger as-child>
                                            <Button v-if="!showBudgetButton" variant="link" class="text-destructive">
                                                <DollarSign /> Agregar
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Agregar gasto variable</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    <hr class="mb-5">
                                                    <form @submit.prevent="submitExpense" class="space-y-6">
                                                        <div class="grid gap-2">
                                                            <Input id="id" type="hidden" v-model="form.budget_id" />
                                                            <Label for="name">Nombre</Label>
                                                            <Input id="name" class="mt-1 block w-full" required autocomplete="name" placeholder="Ej: Mi sueldo" v-model="form.name" />
                                                            <InputError class="mt-2" :message="form.errors.name" />
                                                        </div>

                                                        <div class="grid gap-2">
                                                            <Label for="amount">Monto</Label>
                                                            <Input type="number" step="0.2" id="amount" class="mt-1 block w-full" required autocomplete="amount" placeholder="Ej: 500000" v-model="form.amount" />
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
                                                                        <SelectItem v-for="expenseCategory in expenseCategories" :key="expenseCategory.id" :value="expenseCategory.id">
                                                                            {{ expenseCategory.name }}
                                                                        </SelectItem>
                                                                    </SelectGroup>
                                                                </SelectContent>
                                                            </Select>
                                                            <InputError class="mt-2" :message="form.errors.category_id" />
                                                        </div>

                                                        <div class="grid w-full gap-1.5">
                                                            <Label for="comment">Observación</Label>
                                                            <Textarea id="comment" placeholder="Escribe alguna observación del ingreso aquí." v-model="form.comment" />
                                                            <InputError class="mt-2" :message="form.errors.comment" />
                                                            <p class="text-sm text-muted-foreground">
                                                                Este campo es opcional.
                                                            </p>
                                                        </div>

                                                        <div class="flex items-center gap-4">
                                                            <Button :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600">Crear</Button>
                                                            <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                                        </div>
                                                    </form>
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                                <hr class="mb-3">
                                <div class="flex justify-between md:grid-cols-3 gap-4 mb-5">
                                    <div>
                                        <p class="text-sm text-gray-500">Previsto</p>
                                        <p class="text-4xl font-bold text-gray-800 dark:text-white">${{ (totalBudgetedExpenses ?? 0).toLocaleString() }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Real</p>
                                        <p class="text-4xl font-bold text-gray-800 dark:text-white">${{ (totalExpenseTransactions ?? 0).toLocaleString() }}</p>
                                    </div>
                                </div>
                                <table class="min-w-full table-auto mt-4">
                                    <thead>
                                    <tr class="text-left text-sm text-gray-500 dark:text-gray-400">
                                        <th class="pb-2"></th>
                                        <th class="pb-2">Previsto</th>
                                        <th class="pb-2">Real</th>
                                        <th class="pb-2">Difer.</th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="budgetedExpense in budgetedExpenses" :key="budgetedExpense.id">
                                        <td class="py-2 text-gray-800 dark:text-gray-200">
                                            {{ budgetedExpense.name }}
                                        </td>
                                        <td class="py-2 text-gray-800 dark:text-gray-200">
                                            ${{ budgetedExpense.amount.toLocaleString() }}
                                        </td>
                                        <td class="py-2 text-gray-800 dark:text-gray-200">
                                            ${{ (budgetedExpense.real_amount ?? 0).toLocaleString() }}
                                        </td>
                                        <td :class="(budgetedExpense.amount - (budgetedExpense.real_amount ?? 0)) < 0 ? 'py-2 text-red-500 dark:text-red-200' : 'py-2 text-green-500 dark:text-green-200'">
                                            ${{ (budgetedExpense.amount - (budgetedExpense.real_amount ?? 0)).toLocaleString() }}
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <AlertDialog>
            <AlertDialogTrigger as-child>
                <Button v-if="showBudgetButton" variant="outline" class="fixed bottom-6 right-6 bg-blue-600 text-white rounded-full  h-14 flex items-center justify-center shadow-lg hover:bg-blue-700 transition">
                    Generar presupuesto para el {{ year }}
                </Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Deseas generar el presupuesto base para {{ year }}?</AlertDialogTitle>
                    <AlertDialogDescription>
                        <div v-if="loading" class="text-center text-gray-700">
                            <span class="animate-pulse">Generando presupuesto {{ year }}...</span>
                        </div>
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction  @click="generar" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Sí, generar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>


        <AlertDialog>
            <AlertDialogTrigger as-child>
                <Button v-if="!showBudgetButton" variant="outline" class="fixed bottom-6 right-6 bg-blue-600 text-white rounded-full  h-14 flex items-center justify-center shadow-lg hover:bg-blue-700 transition">
                    Ingresar transacción
                </Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Deseas generar el presupuesto base para {{ year }}?</AlertDialogTitle>
                    <AlertDialogDescription>
                        <hr class="mb-5">
                        <form @submit.prevent="submitTransaction" class="space-y-6">
                            <div class="grid gap-2">
                                <RadioGroup v-model="typeActive">
                                    <div class="flex items-center space-x-2">
                                        <RadioGroupItem id="type" value="expense"  />
                                        <Label for="expense">Gasto</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <RadioGroupItem id="type" value="income" />
                                        <Label for="income">Ingreso</Label>
                                    </div>
                                </RadioGroup>
                            </div>

                            <div class="grid gap-2" v-if="typeActive === 'income'">
                                <Label for="category">Item de ingreso</Label>
                                <Select id="category" v-model="formTransaction.budgeted_income_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Seleccione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="budgetedIncome in budgetedIncomes" :key="budgetedIncome.id" :value="budgetedIncome.id">
                                                {{ budgetedIncome.name }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError class="mt-2" :message="formTransaction.errors.budgeted_income_id" />
                            </div>

                            <div class="grid gap-2" v-else>
                                <Label for="category">Item de ingreso</Label>
                                <Select id="category" v-model="formTransaction.budgeted_expense_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Seleccione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="budgetedExpense in budgetedExpenses" :key="budgetedExpense.id" :value="budgetedExpense.id">
                                                {{ budgetedExpense.name }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError class="mt-2" :message="formTransaction.errors.budgeted_expense_id" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="amount">Monto</Label>
                                <Input type="number" step="0.2" id="amount" class="mt-1 block w-full" required autocomplete="amount" placeholder="Ej: 500000" v-model="formTransaction.amount" />
                                <InputError class="mt-2" :message="formTransaction.errors.amount" />
                            </div>

                            <div class="grid w-full gap-1.5">
                                <Label for="comment">Descripción</Label>
                                <Textarea id="comment" placeholder="Escribe alguna observación del ingreso aquí." v-model="formTransaction.description" />
                                <InputError class="mt-2" :message="formTransaction.errors.description" />
                                <p class="text-sm text-muted-foreground">
                                    Este campo es opcional.
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <Button :disabled="formTransaction.processing" class="bg-blue-500 hover:bg-blue-600">Crear</Button>
                                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                            </div>
                        </form>
                    </AlertDialogDescription>
                </AlertDialogHeader>
            </AlertDialogContent>
        </AlertDialog>

    </AppLayout>
</template>

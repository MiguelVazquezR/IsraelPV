<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
import CancelButton from "@/Components/MyComponents/CancelButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import Back from "@/Components/MyComponents/Back.vue";
import SaleDesktopDetail from '@/Components/MyComponents/Client/SaleDesktopDetail.vue';
import SaleMobileDetail from '@/Components/MyComponents/Client/SaleMobileDetail.vue';
import InputError from "@/Components/InputError.vue";
import { ElNotification } from 'element-plus';

const props = defineProps({
    client: Object,
    clients: Array,
});

const paymentModal = ref(false);
const debtModal = ref(false);
const activeTab = ref('1');
const currentClient = ref(props.client.id);

// Inicializar fecha al día de hoy en formato YYYY-MM-DD (necesario para input type date)
const today = new Date().toISOString().split('T')[0];

const paymentForm = useForm({
    client_id: props.client.id,
    amount: null,
    notes: null,
    date: today, // SE PONE LA FECHA DE HOY POR DEFECTO
});

const debtForm = useForm({
    client_id: props.client.id,
    has_credit: true,
    total: null,
});

// Computados
const reversedSales = computed(() => {
    return [...props.client.sales].reverse(); // Copia del array para no mutar el prop
});

// Métodos
const storePayment = () => {
    // Doble check de seguridad en front
    if (paymentForm.processing) return;

    paymentForm.post(route('clients.store-payment'), {
        onSuccess: () => {
            ElNotification({
                title: "Correcto",
                message: "Se ha registrado el abono",
                type: "success",
            });
            paymentModal.value = false;
            
            // Imprimir ticket
            const payment = encodeURIComponent(JSON.stringify({ payment: paymentForm.amount }));
            window.open(`${route('sales.print-payment-ticket', paymentForm.client_id)}?payment=${payment}`, '_blank');
            
            paymentForm.reset();
            paymentForm.date = today; // Restaurar fecha hoy
        },
        onError: (err) => {
            console.log(err);
            // Si el backend devuelve 429 (bloqueo), Inertia lo maneja, pero podemos notificar
             ElNotification({
                title: "Error",
                message: "Hubo un error al procesar el pago. Verifique no duplicar.",
                type: "error",
            });
        }
    });
};

const storeDebt = () => {
    if (debtForm.processing) return;

    debtForm.post(route('clients.store-debt'), {
        onSuccess: () => {
             ElNotification({
                title: "Correcto",
                message: "Se ha registrado la deuda",
                type: "success",
            });
            debtModal.value = false;
            debtForm.reset();
        }
    });
};

// Navegación rápida entre clientes
const handleClientChange = (id) => {
    if(id) router.visit(route('clients.show', id));
};

</script>

<template>
    <AppLayout :title="client.name">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            
            <!-- Header Navegación -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <Back />
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Detalles del Cliente</h1>
                        <p class="text-sm text-gray-500">Gestión de cuenta y movimientos</p>
                    </div>
                </div>

                <!-- Buscador Rápido de Clientes -->
                <div class="w-full md:w-64">
                    <el-select 
                        v-model="currentClient" 
                        filterable 
                        placeholder="Ir a otro cliente"
                        @change="handleClientChange"
                        class="w-full"
                    >
                        <el-option 
                            v-for="c in clients" 
                            :key="c.id" 
                            :label="c.name" 
                            :value="c.id" 
                        />
                    </el-select>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Columna Izquierda: Tarjeta de Info y Saldos -->
                <div class="space-y-6">
                    <!-- Tarjeta Principal Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xl">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900 leading-tight">{{ client.name }}</h2>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">ID: {{ String(client.id).padStart(3, '0') }}</span>
                                </div>
                            </div>

                            <div class="space-y-3 text-sm">
                                <div class="flex items-start gap-3 text-gray-600">
                                    <i class="fa-solid fa-location-dot mt-1 w-4 text-center text-gray-400"></i>
                                    <span>{{ client.address ?? 'Sin dirección registrada' }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-gray-600">
                                    <i class="fa-solid fa-phone w-4 text-center text-gray-400"></i>
                                    <span>{{ client.phone }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-gray-600">
                                    <i class="fa-solid fa-file-invoice w-4 text-center text-gray-400"></i>
                                    <span>{{ client.rfc ?? 'Sin RFC' }}</span>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">Deuda Total</p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-2xl font-bold" :class="client.debt > 0 ? 'text-red-600' : 'text-green-600'">
                                        ${{ client.debt?.toLocaleString('en-US', {minimumFractionDigits: 2}) }}
                                    </span>
                                    <span class="text-xs text-gray-400">MXN</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mt-6">
                                <button 
                                    @click="debtModal = true"
                                    class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-200 hover:border-red-200 hover:bg-red-50 text-gray-600 hover:text-red-600 transition-all group"
                                >
                                    <i class="fa-solid fa-circle-plus text-lg mb-1 group-hover:scale-110 transition-transform"></i>
                                    <span class="text-xs font-medium">Agregar Deuda</span>
                                </button>
                                <button 
                                    @click="paymentModal = true"
                                    class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-200 hover:border-green-200 hover:bg-green-50 text-gray-600 hover:text-green-600 transition-all group"
                                >
                                    <i class="fa-solid fa-hand-holding-dollar text-lg mb-1 group-hover:scale-110 transition-transform"></i>
                                    <span class="text-xs font-medium">Abonar</span>
                                </button>
                            </div>

                            <div class="mt-3">
                                <button 
                                    @click="$inertia.get(route('clients.edit', client.id))"
                                    class="w-full py-2 flex items-center justify-center gap-2 text-sm text-gray-500 hover:text-primary transition-colors border border-transparent hover:border-gray-100 rounded-lg"
                                >
                                    <i class="fa-solid fa-pencil text-xs"></i> Editar datos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Historial de Ventas -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 min-h-[500px] flex flex-col">
                        <!-- Custom Tabs Header -->
                        <div class="flex border-b border-gray-100">
                            <button 
                                class="px-6 py-4 text-sm font-medium border-b-2 transition-colors focus:outline-none"
                                :class="activeTab === '1' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                @click="activeTab = '1'"
                            >
                                Últimos Movimientos
                            </button>
                            <!-- Placeholder para tab futura -->
                            <!-- <button class="px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-400 cursor-not-allowed">Historial Completo</button> -->
                        </div>

                        <div class="p-4 flex-1">
                            <div v-if="activeTab === '1'">
                                <div v-if="reversedSales.length > 0" class="space-y-3">
                                    <!-- Versión Móvil: Pasamos el objeto COMPLETO 'item' -->
                                    <SaleMobileDetail 
                                        v-for="item in reversedSales" 
                                        :key="item.id" 
                                        :sale="item" 
                                        class="md:hidden" 
                                    />
                                    
                                    <!-- Versión Desktop: Pasamos el array COMPLETO 'reversedSales' -->
                                    <div class="hidden md:block overflow-x-auto">
                                        <SaleDesktopDetail :sales="reversedSales" />
                                    </div>
                                </div>
                                <el-empty v-else description="No hay movimientos registrados recientes" :image-size="100" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL ABONOS -->
        <Modal :show="paymentModal" @close="paymentModal = false">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Registrar Abono</h2>
                    <button @click="paymentModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="storePayment" class="space-y-5">
                    
                    <!-- Resumen Visual del Cambio -->
                    <div class="bg-green-50 rounded-lg p-4 flex items-center justify-between border border-green-100">
                        <div>
                            <p class="text-xs uppercase text-green-600 font-bold mb-1">Deuda Actual</p>
                            <p class="text-lg font-bold text-green-800">${{ client.debt?.toLocaleString('en-US', {minimumFractionDigits: 2}) }}</p>
                        </div>
                        <i class="fa-solid fa-arrow-right text-green-300"></i>
                        <div class="text-right">
                            <p class="text-xs uppercase text-green-600 font-bold mb-1">Nueva Deuda</p>
                            <p class="text-lg font-bold text-green-800" :class="{'text-red-500': (client.debt - paymentForm.amount) < 0}">
                                ${{ (client.debt - (paymentForm.amount || 0)).toLocaleString('en-US', {minimumFractionDigits: 2}) }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Fecha *" class="mb-1" />
                            <input 
                                type="date" 
                                v-model="paymentForm.date" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm py-2"
                            >
                            <InputError :message="paymentForm.errors.date" />
                        </div>
                        <div>
                            <InputLabel value="Monto *" class="mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input 
                                    v-model="paymentForm.amount" 
                                    type="number" 
                                    step="0.01"
                                    class="w-full pl-7 border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm py-2" 
                                    placeholder="0.00"
                                >
                            </div>
                            <InputError :message="paymentForm.errors.amount" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Notas" class="mb-1" />
                        <textarea 
                            v-model="paymentForm.notes" 
                            rows="3" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm"
                            placeholder="Referencia o detalles..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <CancelButton @click="paymentModal = false">Cancelar</CancelButton>
                        <PrimaryButton 
                            :disabled="!paymentForm.amount || paymentForm.amount <= 0 || paymentForm.processing"
                            :class="{'opacity-50 cursor-not-allowed': paymentForm.processing}"
                        >
                            <i v-if="paymentForm.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                            {{ paymentForm.processing ? 'Procesando...' : 'Registrar Pago' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- MODAL DEUDA -->
        <Modal :show="debtModal" @close="debtModal = false">
             <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Agregar Deuda Anterior</h2>
                    <button @click="debtModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="storeDebt" class="space-y-5">
                    <div class="bg-red-50 rounded-lg p-4 text-sm text-red-700 border border-red-100">
                        <i class="fa-solid fa-circle-info mr-2"></i>
                        Esto incrementará el saldo deudor del cliente sin generar una venta detallada.
                    </div>

                    <div>
                         <InputLabel value="Monto a agregar *" class="mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input 
                                    v-model="debtForm.total" 
                                    type="number" 
                                    step="0.01"
                                    class="w-full pl-7 border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm py-2" 
                                    placeholder="0.00"
                                >
                            </div>
                         <InputError :message="debtForm.errors.total" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <CancelButton @click="debtModal = false">Cancelar</CancelButton>
                        <PrimaryButton :disabled="!debtForm.total || debtForm.processing">
                             {{ debtForm.processing ? 'Guardando...' : 'Agregar Deuda' }}
                        </PrimaryButton>
                    </div>
                </form>
             </div>
        </Modal>

    </AppLayout>
</template>
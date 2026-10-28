<template>
    <div v-if="global_payments.length" class="w-full mx-auto text-sm overflow-auto">
        <!-- Encabezados de tabla estilo moderno -->
        <div class="hidden md:flex items-center uppercase tracking-wide text-xs text-gray-500 font-bold border-b border-gray-100 pb-3 mb-2 px-4">
            <div class="w-[8%]">Folio</div>
            <div class="w-[17%]">Cliente</div>
            <div class="w-[15%]">Fecha</div>
            <div class="w-[15%] text-right pr-4">Saldo Ant.</div>
            <div class="w-[15%] text-right pr-4">Abono</div>
            <div class="w-[15%] text-right pr-4">Saldo Nuevo</div>
            <div class="w-[15%] text-center">Acciones</div>
        </div>

        <!-- Lista de registros -->
        <div class="space-y-2">
            <div v-for="item in global_payments" :key="item.id" 
                class="group bg-white hover:bg-gray-50 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-4 flex flex-col md:flex-row md:items-center">
                
                <!-- Folio -->
                <div class="md:w-[8%] flex items-center mb-2 md:mb-0">
                    <span class="md:hidden font-bold text-gray-500 mr-2">Folio:</span>
                    <span class="font-mono text-gray-600 bg-gray-100 px-2 py-1 rounded text-xs">
                        #{{ String(item.id).padStart(4, '0') }}
                    </span>
                </div>

                <!-- Cliente -->
                <div class="md:w-[17%] mb-2 md:mb-0">
                    <span @click.stop="$inertia.get(route('clients.show', item.client?.id))" 
                          class="font-medium text-gray-800 hover:text-primary cursor-pointer transition-colors truncate block" :title="item.client?.name">
                        {{ item.client?.name ?? 'Cliente Desconocido' }}
                    </span>
                </div>

                <!-- Fecha -->
                <div class="md:w-[15%] text-gray-500 text-xs mb-2 md:mb-0 flex items-center">
                    <i class="fa-regular fa-calendar mr-2 md:hidden"></i>
                    {{ formatDate(item.date) }}
                </div>

                <!-- Saldo Anterior -->
                <div class="md:w-[15%] text-right pr-4 mb-2 md:mb-0 flex justify-between md:block">
                    <span class="md:hidden font-bold text-gray-500">Saldo Anterior:</span>
                    <div class="flex flex-col items-end">
                        <span class="text-gray-500 font-semibold">
                            ${{ formatMoney(item.initial_amount) }}
                        </span>
                        <span class="text-[10px] text-gray-400 uppercase tracking-wider hidden md:block">Previo</span>
                    </div>
                </div>

                <!-- Abono (Resaltado) -->
                <div class="md:w-[15%] text-right pr-4 mb-2 md:mb-0 flex justify-between md:block bg-green-50 md:bg-transparent p-2 md:p-0 rounded-lg">
                    <span class="md:hidden font-semibold text-green-700">Abono:</span>
                    <div class="flex flex-col items-end">
                        <span class="text-emerald-600 font-bold text-base">
                            + ${{ formatMoney(item.amount) }}
                        </span>
                        <span class="text-[10px] text-green-600 font-medium uppercase tracking-wider hidden md:block">Abonado</span>
                    </div>
                </div>

                <!-- Saldo (Claro y visible) -->
                <div class="md:w-[15%] text-right pr-4 mb-2 md:mb-0 flex justify-between md:block">
                    <span class="md:hidden font-bold text-gray-500">Saldo Restante:</span>
                    <div class="flex flex-col items-end">
                        <span class="text-gray-900 font-bold">
                            ${{ formatMoney(item.initial_amount - item.amount) }}
                        </span>
                        <span class="text-[10px] text-gray-400 uppercase tracking-wider hidden md:block">Restante</span>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="md:w-[15%] flex justify-end md:justify-center items-center mt-2 md:mt-0 border-t md:border-t-0 pt-2 md:pt-0">
                    <el-popconfirm 
                        confirm-button-text="Sí" 
                        cancel-button-text="No" 
                        width="200"
                        icon-color="#DC2626" 
                        title="¿Eliminar este abono?" 
                        @confirm="deleteItem(item.id)"
                    >
                        <template #reference>
                            <button class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-colors tooltip-btn" title="Eliminar">
                                <i class="fa-regular fa-trash-can text-lg"></i>
                            </button>
                        </template>
                    </el-popconfirm>
                </div>
            </div>
        </div>
    </div>
    <el-empty v-else description="No hay abonos registrados" />
</template>

<script>
import { ElNotification } from 'element-plus'
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import axios from 'axios';

export default {
    props: {
        global_payments: Object
    },
    methods: {
        formatDate(date) {
            if (!date) return '-';
            return format(new Date(date), 'd MMM yyyy', { locale: es });
        },
        formatMoney(amount) {
            return amount?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        async deleteItem(globalPaymentId) {
            try {
                const response = await axios.delete(route('global-payments.destroy', globalPaymentId));
                if (response.status == 200) {
                    const indexToDelete = this.global_payments.findIndex(item => item.id == globalPaymentId);
                    this.global_payments.splice(indexToDelete, 1);

                    ElNotification({
                        title: 'Eliminado',
                        message: 'El abono ha sido eliminado correctamente',
                        type: 'success',
                    });
                }
            } catch (error) {
                console.log(error);
                ElNotification({
                    title: 'Error',
                    message: 'No se pudo eliminar. Inténte más tarde',
                    type: 'error',
                });
            }
        },
    }
}
</script>
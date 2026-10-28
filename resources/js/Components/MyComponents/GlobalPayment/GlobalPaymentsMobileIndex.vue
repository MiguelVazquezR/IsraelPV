<template>
    <div class="bg-white rounded-[16px] shadow-sm border border-gray-100 p-4 mb-3 relative overflow-hidden">
        <!-- Decoración lateral visual -->
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary h-full"></div>

        <!-- Encabezado Tarjeta -->
        <div class="flex justify-between items-start mb-3 pl-2">
            <div>
                <span class="text-[12px] uppercase text-gray-400 tracking-wider font-bold">Folio #{{ String(payment.id).padStart(4, '0') }}</span>
                <h3 @click.stop="$inertia.get(route('clients.show', payment.client?.id))" 
                    class="text-primary font-bold text-base cursor-pointer hover:underline truncate max-w-[200px]">
                    {{ payment.client?.name ?? 'Cliente Desconocido' }}
                </h3>
                <p class="text-xs text-gray-400 capitalize">{{ formatDate(payment.date) }}</p>
            </div>
            
            <!-- Botón Eliminar -->
             <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#D37E2D"
                title="¿Eliminar?" @confirm="deleteItem(payment.id)">
                <template #reference>
                    <button class="bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-full p-2 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </button>
                </template>
            </el-popconfirm>
        </div>

        <div class="border-t border-gray-100 my-2"></div>

        <!-- Sección de Dineros (Diseño Claro con 3 columnas) -->
        <div class="grid grid-cols-3 gap-2 pl-2 mt-3">
            <!-- Saldo Anterior -->
            <div class="bg-gray-50 rounded-lg p-2 text-center flex flex-col justify-center">
                <p class="text-[9px] text-gray-400 uppercase font-bold tracking-wider mb-1">Anterior</p>
                <p class="text-gray-500 font-bold text-sm">
                    ${{ formatMoney(payment.initial_amount) }}
                </p>
            </div>

            <!-- Abono -->
            <div class="bg-green-50 rounded-lg p-2 text-center flex flex-col justify-center border border-green-100 shadow-sm relative z-10 scale-105">
                <p class="text-[9px] text-green-600 uppercase font-bold tracking-wider mb-1">Abonado</p>
                <p class="text-emerald-600 font-black text-sm">
                    +${{ formatMoney(payment.amount) }}
                </p>
            </div>

            <!-- Saldo Restante -->
            <div class="bg-gray-100 rounded-lg p-2 text-center flex flex-col justify-center">
                <p class="text-[9px] text-gray-500 uppercase font-bold tracking-wider mb-1">Restante</p>
                <p class="text-gray-800 font-bold text-sm">
                    ${{ formatMoney(payment.initial_amount - payment.amount) }}
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from "@inertiajs/vue3";
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import axios from 'axios';
import { ElNotification } from 'element-plus';

export default {
    props: {
        payment: Object, 
    },
    components: {
        Link,
    },
    methods: {
        formatDate(date) {
            if (!date) return '-';
            return format(new Date(date), 'd MMM yyyy', { locale: es });
        },
        formatMoney(amount) {
             return amount?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        async deleteItem(id) {
            try {
                const response = await axios.delete(route('global-payments.destroy', id));
                if (response.status == 200) {
                    ElNotification({
                        title: 'Correcto',
                        message: 'Se ha eliminado el abono',
                        type: 'success',
                    });
                    location.reload(); 
                }
            } catch (error) {
                console.log(error);
                ElNotification({
                    title: 'Error',
                    message: 'No se pudo eliminar el abono.',
                    type: 'error',
                });
            }
        },
    }
}
</script>
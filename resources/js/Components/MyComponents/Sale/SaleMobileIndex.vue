<template>
    <div class="bg-white rounded-2xl p-4 mb-4 shadow-sm border border-gray-100 active:scale-[0.99] transition-transform duration-100 relative overflow-hidden"
         @click="$inertia.get(route('sales.show', sale.id))">
        
        <!-- Decoración lateral estado -->
        <div class="absolute left-0 top-0 bottom-0 w-1.5" 
             :class="sale.paid_at ? 'bg-green-500' : 'bg-red-500'"></div>

        <!-- Encabezado: Folio y Fecha -->
        <div class="flex justify-between items-start mb-4 pl-2">
            <div>
                <span class="text-base text-gray-400 font-bold uppercase tracking-wider block">Folio</span>
                <span class="bg-gray-100 text-gray-800 text-xs font-mono font-bold px-2 py-1 rounded-md border border-gray-200">
                    {{ String(sale.folio).padStart(3, '0') }}
                </span>
            </div>
            <div class="text-right">
                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider block mb-1">Fecha</span>
                <p class="text-sm font-medium text-gray-600">{{ sale.created_at }}</p>
            </div>
        </div>

        <!-- Cuerpo: Cliente y Total -->
        <div class="flex justify-between items-center border-t border-gray-50 pt-1 pl-2">
            <div class="w-2/3 pr-2">
                <p class="text-xs text-gray-400 mb-1">Cliente</p>
                <h3 class="text-lg font-bold text-gray-800 leading-tight truncate">
                    {{ sale.client?.name ?? 'Cliente General' }}
                </h3>
                <!-- Indicador de deuda (opcional) -->
                <!-- <p v-if="!sale.paid_at" class="text-xs text-red-500 mt-1 font-medium">Pago Pendiente</p> -->
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400 mb-1">Total</p>
                <p class="text-xl font-black text-primary">
                    ${{ formatMoney(sale.total) }}
                </p>
            </div>
        </div>

        <!-- Pie: Acciones -->
        <div class="mt-1 pt-1 flex justify-end space-x-3 border-t border-dashed border-gray-100 pl-2">
            <button @click.stop="print(sale)" 
                class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 active:bg-indigo-100 transition-colors">
                <i class="fa-solid fa-print text-sm"></i>
            </button>
            
            <button @click.stop="confirmDelete(sale)"
                class="flex items-center justify-center w-10 h-10 rounded-full bg-red-50 text-red-500 active:bg-red-100 transition-colors">
                <i class="fa-regular fa-trash-can text-sm"></i>
            </button>
        </div>
    </div>
</template>

<script>
import { Link } from "@inertiajs/vue3";
import { ElMessageBox, ElNotification } from 'element-plus'
import axios from 'axios';

export default {
    components: {
        Link,
    },
    props: {
        sale: Object, // AHORA RECIBE EL OBJETO COMPLETO
    },
    methods: {
        formatMoney(amount) {
            return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        async confirmDelete(sale) {
            try {
                await ElMessageBox.confirm(
                    `¿Eliminar la venta con folio ${sale.folio}? Esta acción no se puede deshacer.`,
                    'Eliminar venta',
                    {
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        type: 'warning',
                    }
                );
            } catch (cancelled) {
                return;
            }

            await this.deleteItem(sale.id);
        },
        async deleteItem(saleId) {
            try {
                const response = await axios.delete(route('sales.destroy', saleId));
                if (response.status == 200) {
                    // El padre refresca la lista localmente (sin recargar la página)
                    this.$emit('delete-sale', saleId);

                    ElNotification({
                        title: 'Eliminado',
                        message: 'Venta eliminada correctamente',
                        type: 'success',
                    });
                }
            } catch (error) {
                console.error(error);
                ElNotification({
                    title: 'Error',
                    message: 'No se pudo eliminar la venta.',
                    type: 'error',
                });
            }
        },
        print(sale) {
            window.open(route('sales.print-ticket', sale.id), '_blank');
        },
    }
}
</script>
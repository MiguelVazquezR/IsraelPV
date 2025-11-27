<template>
    <div class="w-full mx-auto overflow-auto p-1">
        <!-- Header de la tabla -->
        <div v-if="sales.length" class="min-w-[800px]">
            <div class="flex items-center space-x-4 mb-4 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                <div class="w-[12%] text-center">Folio</div>
                <div class="w-[25%] text-left">Cliente</div>
                <div class="w-[20%] text-left">Fecha</div>
                <div class="w-[15%] text-left">Estado</div>
                <div class="w-[15%] text-right pr-4">Total</div>
                <div class="w-[13%] text-center">Acciones</div>
            </div>

            <!-- Filas de datos -->
            <div class="space-y-3">
                <div v-for="sale in sales" :key="sale.id" 
                    class="group flex items-center space-x-4 bg-white border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md hover:border-primary/30 transition-all duration-200 cursor-pointer"
                    @click="$inertia.get(route('sales.show', sale.id))">
                    
                    <!-- Folio -->
                    <div class="w-[12%] text-center">
                        <span class="bg-gray-100 text-gray-600 py-1 px-3 rounded-lg text-xs font-bold font-mono">
                            {{ sale.folio }}
                        </span>
                    </div>

                    <!-- Cliente -->
                    <div class="w-[25%] text-gray-700 font-medium truncate" :title="sale.client?.name">
                        <span @click.stop="$inertia.get(route('clients.show', sale.client.id))" 
                              class="hover:text-primary hover:underline transition-colors">
                            {{ sale.client?.name ?? 'Cliente General' }}
                        </span>
                    </div>

                    <!-- Fecha -->
                    <div class="w-[20%] text-gray-500 text-sm flex items-center">
                        <i class="fa-regular fa-calendar-days mr-2 text-xs opacity-50"></i>
                        {{ sale.created_at }}
                    </div>

                    <!-- Estado (Ejemplo visual, ajusta según tu lógica real) -->
                    <div class="w-[15%]">
                        <span v-if="sale.paid_at" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span> Pagado
                        </span>
                        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5"></span> Pendiente
                        </span>
                    </div>

                    <!-- Total -->
                    <div class="w-[15%] text-right pr-4">
                        <span class="text-gray-900 font-bold text-base">
                            ${{ formatMoney(sale.total) }}
                        </span>
                    </div>

                    <!-- Acciones -->
                    <div class="w-[13%] flex justify-center items-center space-x-2 opacity-60 group-hover:opacity-100 transition-opacity">
                        <el-tooltip content="Imprimir Ticket" placement="top">
                            <button @click.stop="print(sale)" class="p-2 rounded-full hover:bg-indigo-50 text-indigo-600 transition-colors">
                                <i class="fa-solid fa-print text-lg"></i>
                            </button>
                        </el-tooltip>

                        <el-popconfirm confirm-button-text="Sí" cancel-button-text="No" icon-color="#C30303" title="¿Eliminar venta?" @confirm="deleteItem(sale.id)">
                            <template #reference>
                                <div class="inline-block"> <!-- Wrapper para tooltip + popconfirm -->
                                    <el-tooltip content="Eliminar" placement="top">
                                        <button @click.stop class="p-2 rounded-full hover:bg-red-50 text-red-500 transition-colors">
                                            <i class="fa-regular fa-trash-can text-lg"></i>
                                        </button>
                                    </el-tooltip>
                                </div>
                            </template>
                        </el-popconfirm>
                    </div>
                </div>
            </div>
        </div>
        <el-empty v-else description="No hay ventas registradas" :image-size="200" />
    </div>
</template>

<script>
import { ElNotification } from 'element-plus'
import axios from 'axios';

export default {
    props: {
        sales: Array
    },
    methods: {
        formatMoney(amount) {
            return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        async deleteItem(saleId) {
            try {
                const response = await axios.delete(route('sales.destroy', saleId));
                if (response.status == 200) {
                    // Emitir evento al padre para actualizar la lista en lugar de manipular props directamente
                    this.$emit('delete-sale', saleId);
                    
                    // Fallback visual local si no se usa el evento
                    const indexToDelete = this.sales.findIndex(item => item.id == saleId);
                    if(indexToDelete !== -1) this.sales.splice(indexToDelete, 1);

                    ElNotification({
                        title: 'Eliminado',
                        message: 'La venta ha sido eliminada correctamente',
                        type: 'success',
                        duration: 3000
                    });
                }
            } catch (error) {
                console.error(error);
                ElNotification({
                    title: 'Error',
                    message: 'No se pudo eliminar la venta. Intente más tarde',
                    type: 'error',
                });
            }
        },
        print(sale) {
            window.open(route('sales.print-ticket', sale.id), '_blank');
        }
    }
}
</script>
<template>
    <div v-if="sales.length" class="w-full mx-auto text-[11px] md:text-sm overflow-auto">
        <div class="text-center md:text-base flex items-center space-x-4 mb-2">
            <div class="font-bold w-[15%]">Folio</div>
            <div class="font-bold pb-3 pl-2 text-left w-[18%] md:w-[13%]">Cliente</div>
            <div class="font-bold pb-3 text-left w-[20%] lg:w-[20%]">Fecha de venta</div>
            <div class="font-bold pb-3 text-left w-[20%] lg:w-[10%]">Modo de pago</div>
            <div class="font-bold pb-3 text-left w-[15%]">Total</div>
            <div class="font-bold pb-3 text-left w-[15%]">Estatus</div>
            <div class="w-[17%]"></div>
        </div>
        <div>
            <div v-for="sale in sales" :key="sale.id" class="*:p-3 h-12 cursor-pointer flex items-center space-x-4 border rounded-full mb-2 hover:border-primarylight" 
            @click="$inertia.get(route('sales.show', sale.id))">
                <div class="w-[15%] text-center rounded-l-full">{{ sale.folio }}</div>
                <div class="w-[18%] md:w-[13%] text-primary"><span @click.stop="$inertia.get(route('clients.show', sale.client.id))" :class="sale.client?.name ? 'hover:underline' : ''">{{ sale.client?.name ?? 'N/A' }}</span></div>
                <div class="w-[30%] lg:w-[20%]">{{ sale.created_at }}</div>
                <div class="w-[20%] lg:w-[10%]">{{ sale.has_credit ? 'A crédito' : 'Al contado' }}</div>
                <div class="w-[15%]">${{ sale.total?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</div>
                <div :class="sale.status?.color" class="w-[15%]">{{ sale.status?.label }}</div>
                <div class="rounded-r-full w-[17%] text-center">
                    <!-- <i @click.stop="$inertia.get(route('sales.edit', sale.id))" class="fa-solid fa-pencil text-primary cursor-pointer hover:bg-gray-200 rounded-full mr-1 p-2"></i> -->
                    <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#C30303" title="¿Continuar?" @confirm="deleteItem(sale.id)">
                        <template #reference>
                            <i @click.stop class="fa-regular fa-trash-can text-primary cursor-pointer hover:bg-gray-200 rounded-full p-2"></i>
                        </template>
                    </el-popconfirm>
                    <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#C30303" title="¿Continuar?" @confirm="print(sale)">
                        <template #reference>
                            <i @click.stop class="fa-solid fa-print text-primary cursor-pointer hover:bg-gray-200 rounded-full p-2"></i>
                        </template>
                    </el-popconfirm>
                </div>
            </div>
        </div>
    </div>
    <el-empty v-else description="No hay ventas registradas" />
</template>

<script>
import { ElNotification } from 'element-plus'
import axios from 'axios';

export default {
data() {
    return {

    };
},
components:{

},
props:{
sales: Object
},
methods:{
    async deleteItem(saleId) {
        try {
            const response = await axios.delete(route('sales.destroy', saleId));
            if (response.status == 200) {
                const indexToDelete = this.sales.findIndex(item => item.id == saleId);
                this.sales.splice(indexToDelete, 1);

                ElNotification({
                title: 'Correcto',
                message: 'Se ha eliminado la venta',
                type: 'success',
                position: 'bottom-right',
            });
            }
        } catch (error) {
            console.log(error);
            ElNotification({
                title: 'Error',
                message: 'No se pudo eliminar la venta. Inténte más tarde',
                type: 'error',
                position: 'bottom-right',
            });
        }
    },
    print(sale) {
        window.open(route('sales.print-ticket', sale.id), '_blank');
    }
}
}
</script>

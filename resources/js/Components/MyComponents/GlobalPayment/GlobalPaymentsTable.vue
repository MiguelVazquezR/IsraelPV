<template>
    <div v-if="global_payments.length" class="w-full mx-auto text-[11px] md:text-sm overflow-auto">
        <div class="text-center md:text-base flex items-center space-x-4 mb-2">
            <div class="font-bold w-[10%]">Folio</div>
            <div class="font-bold pb-3 pl-2 text-left w-[18%] md:w-[13%]">Cliente</div>
            <div class="font-bold pb-3 text-left w-[20%] lg:w-[20%]">Fecha de abono</div>
            <div class="font-bold pb-3 text-left w-[15%]">Monto</div>
            <div class="font-bold pb-3 text-left w-[15%]">Saldo</div>
            <!-- <div class="font-bold pb-3 text-left w-[15%]">Notas</div> -->
            <div class="w-[17%]"></div>
        </div>
        <div>
            <div v-for="item in global_payments" :key="item.id" class="*:p-3 h-12 cursor-pointer flex items-center space-x-4 border rounded-full mb-2 hover:border-primarylight">
                <div class="w-[10%] text-center rounded-l-full">{{ item.id }}</div>
                <div class="w-[18%] md:w-[13%] text-primary"><span @click.stop="$inertia.get(route('clients.show', item.client.id))" :class="item.client?.name ? 'hover:underline' : ''">{{ item.client?.name ?? 'N/A' }}</span></div>
                <div class="w-[30%] lg:w-[20%]">{{ formatDate(item.date) ?? '-' }}</div>
                <div class="w-[15%]">${{ item.amount?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</div>
                <div class="w-[15%]">${{ (item.initial_amount - item.amount)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</div>
                <!-- <div class="w-[30%] lg:w-[20%]">{{ item.notes ?? '-' }}</div> -->
                <div class="rounded-r-full w-[17%] text-center">
                    <!-- <i @click.stop="$inertia.get(route('sales.edit', item.id))" class="fa-solid fa-pencil text-primary cursor-pointer hover:bg-gray-200 rounded-full mr-1 p-2"></i> -->
                    <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#C30303" title="¿Continuar?" @confirm="deleteItem(item.id)">
                        <template #reference>
                            <i @click.stop class="fa-regular fa-trash-can text-primary cursor-pointer hover:bg-gray-200 rounded-full p-2"></i>
                        </template>
                    </el-popconfirm>
                    <!-- <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#C30303" title="¿Continuar?" @confirm="print(item)">
                        <template #reference>
                            <i @click.stop class="fa-solid fa-print text-primary cursor-pointer hover:bg-gray-200 rounded-full p-2"></i>
                        </template>
                    </el-popconfirm> -->
                </div>
            </div>
        </div>
    </div>
    <el-empty v-else description="No hay ventas registradas" />
</template>

<script>
import { ElNotification } from 'element-plus'
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import axios from 'axios';

export default {
data() {
    return {

    };
},
components:{

},
props:{
global_payments: Object
},
methods:{
    formatDate(date, dateTime = false) {
        if ( date ) {
            if (dateTime) {
                return format(new Date(date), 'd MMM yyyy, hh:mm a', { locale: es });
            } else {
                return format(new Date(date), 'd MMM yyyy', { locale: es });
            }
        } 
    },
    async deleteItem(globalPaymentId) {
        try {
            const response = await axios.delete(route('global-payments.destroy', globalPaymentId));
            if (response.status == 200) {
                const indexToDelete = this.global_payments.findIndex(item => item.id == globalPaymentId);
                this.global_payments.splice(indexToDelete, 1);

                ElNotification({
                title: 'Correcto',
                message: '',
                type: 'success',
            });
            }
        } catch (error) {
            console.log(error);
            ElNotification({
                title: 'Error',
                message: 'No se pudo eliminar. Inténte más tarde',
                type: 'error',
                position: 'bottom-right',
            });
        }
    },
}
}
</script>

<template>
    <div v-if="histories.length" class="w-full mx-auto text-[11px] md:text-sm overflow-auto">
        <div class="text-center md:text-base flex items-center space-x-4 mb-2">
            <div class="font-bold w-[15%]">Folio</div>
            <div class="font-bold pb-3 pl-2 text-left w-[18%] md:w-[13%]">Producto</div>
            <div class="font-bold pb-3 text-left w-[20%] lg:w-[20%]">Fecha de entrada</div>
            <div class="font-bold pb-3 text-left w-[20%] lg:w-[10%]">Cantidad</div>
            <div class="font-bold pb-3 text-left w-[15%]">Costo/Unidad</div>
            <div class="font-bold pb-3 text-left w-[15%]">Total</div>
            <div class="w-[17%]"></div>
        </div>
        <div>
            <div v-for="history in histories" :key="history.id" class="*:p-3 h-12 cursor-pointer flex items-center space-x-4 border rounded-full mb-2 hover:border-primarylight" 
            @click="$inertia.get(route('product-histories.show', history.id))">
                <div class="w-[15%] text-center rounded-l-full">{{ history.folio }}</div>
                <div class="w-[18%] md:w-[13%] text-primary"><span @click.stop="$inertia.get(route('products.show', history.product.id))" class="hover:underline">{{ history.product?.name }}</span></div>
                <div class="w-[30%] lg:w-[20%]">{{ history.created_at }}</div>
                <div class="w-[20%] lg:w-[10%]">{{ history.quantity }}</div>
                <div class="w-[15%]">${{ history.product?.cost?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</div>
                <div class="w-[15%]">${{ (history.product?.cost * history.quantity)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</div>
                <div class="rounded-r-full w-[17%] text-center">
                    <!-- <i @click.stop="$inertia.get(route('product-histories.edit', history.id))" class="fa-solid fa-pencil text-primary cursor-pointer hover:bg-gray-200 rounded-full mr-1 p-2"></i> -->
                    <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#C30303" title="¿Continuar?" @confirm="deleteItem(history.id)">
                        <template #reference>
                            <i @click.stop class="fa-regular fa-trash-can text-primary cursor-pointer hover:bg-gray-200 rounded-full p-2"></i>
                        </template>
                    </el-popconfirm>
                </div>
            </div>
        </div>
    </div>
    <el-empty v-else description="No hay historial registrado" />
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
histories: Object
},
methods:{
    async deleteItem(historyId) {
        try {
            const response = await axios.delete(route('product-histories.destroy', historyId));
            if (response.status == 200) {
                const indexToDelete = this.histories.findIndex(item => item.id == historyId);
                this.histories.splice(indexToDelete, 1);

                ElNotification({
                title: 'Success',
                message: 'Se ha eliminado el registro',
                type: 'success',
                position: 'bottom-right',
            });
            }
        } catch (error) {
            console.log(error);
            ElNotification({
                title: 'Error',
                message: 'No se pudo eliminar el registro. Inténte más tarde',
                type: 'error',
                position: 'bottom-right',
            });
        }
    }
}
}
</script>
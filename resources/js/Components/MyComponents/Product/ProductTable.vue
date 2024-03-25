<template>
    <div v-if="products.length" class="w-full mx-auto text-[11px] md:text-sm overflow-auto">
        <div class="text-center md:text-sm flex items-center mb-2 *:px-2 *:text-left">
            <!-- <div class="w-[35%] md:w-[10%]"></div> -->
            <div class="font-bold pb-3 w-[15%] md:w-[10%] !pl-4">Código</div>
            <div class="font-bold pb-3 w-[30%]">Nombre</div>
            <div class="font-bold pb-3 w-[20%]">Precio</div>
            <div class="font-bold pb-3 w-[20%]">Existencias</div>
            <div class="hidden md:block font-bold pb-3 w-[15%]">Existencias mínimas</div>
            <div class="w-[10%] md:w-[15%]"></div>
        </div>
        <div>
            <div v-for="product in products" :key="product.id" class="*:px-2 *:py-1 cursor-pointer flex items-center border rounded-full mb-2 hover:border-primarylight" 
            @click="$inertia.get(route('products.show', product.id))">
                <!-- <div class="md:block w-[50%] md:w-[10%] h-14 rounded-l-full">
                    <img class="mx-auto h-12 object-contain rounded-md" :src="product.imageCover[0]?.original_url">
                </div> -->
                <div class="w-[15%] md:w-[10%] !pl-4">{{ product.id }}</div>
                <div class="w-[30%]">{{ product.name }}</div>
                <div class="w-[20%]">${{ product.public_price }}</div>
                <div :class="product.current_stock <= product.min_stock ? 'text-[#F80505]' : ''" class="w-[20%] flex items-center space-x-2">
                    <span>{{ product.current_stock }}</span>
                    <p v-if="product.current_stock <= product.min_stock" class="flex items-center space-x-1 text-[10px] mt-px">
                        <i class="fa-solid fa-arrow-down"></i>
                        <span>Bajo stock</span>
                    </p>
                </div>
                <div class="hidden md:block w-[15%]">{{ product.min_stock }}</div>
                <div class="rounded-r-full w-[10%] md:w-[15%] text-right">
                    <i @click.stop="$inertia.get(route('products.edit', product.id))" class="fa-solid fa-pencil text-primary cursor-pointer hover:bg-gray-200 rounded-full mr-1 p-2"></i>
                    <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#C30303" title="¿Continuar?" @confirm="deleteItem(product.id)">
                        <template #reference>
                            <i @click.stop class="fa-regular fa-trash-can text-primary cursor-pointer hover:bg-gray-200 rounded-full p-2"></i>
                        </template>
                    </el-popconfirm>
                </div>
            </div>
        </div>
    </div>
    <el-empty v-else description="No hay productos registrados" />
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
products: Object
},
methods:{
    async deleteItem(productId) {
        try {
            const response = await axios.delete(route('products.destroy', productId));
            if (response.status == 200) {
                const indexToDelete = this.products.findIndex(item => item.id == productId);
                this.products.splice(indexToDelete, 1);

                ElNotification({
                title: 'Correcto',
                message: 'Se ha eliminado el producto',
                type: 'success',
                position: 'bottom-right',
            });
            }
        } catch (error) {
            console.log(error);
            ElNotification({
                title: 'Error',
                message: 'No se pudo eliminar el producto. Inténte más tarde',
                type: 'error',
                position: 'bottom-right',
            });
        }
    }
}
}
</script>
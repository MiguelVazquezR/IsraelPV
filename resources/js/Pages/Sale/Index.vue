<template>
    <AppLayout title="Ventas">
        <div class="px-2 lg:px-10 py-7">
            <!-- header botones -->
            <div class="lg:flex justify-between items-center mx-3">
                <h1 class="font-bold text-lg">Ventas registradas</h1>
                <button class="border border-[#D9D9D9] rounded-full py-1 px-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                       <path stroke-linecap="round" stroke-linejoin="round" d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5" />
                    </svg>
                    <p class="text-sm ml-2">Filtrar</p>
                </button>
            </div>

            <Loading v-if="loading" class="mt-20" />
            <div v-else class="mt-8 lg:w-11/12">
                <!-- <p v-if="localProducts.length" class="text-gray66 text-[11px]">{{ localProducts.length }} de {{ total_products }} elementos
                </p> -->
                <RegisteredSalesTable :sales="localSales" />
                <!-- <p v-if="localProducts.length" class="text-gray66 text-[11px]">{{ localProducts.length }} de {{ total_products }} elementos
                </p>
                <p v-if="loadingItems" class="text-xs my-4 text-center">
                    Cargando <i class="fa-sharp fa-solid fa-circle-notch fa-spin ml-2 text-primary"></i>
                </p>
                <button v-else-if="total_products > 15 && localProducts.length < total_products && localProducts.length" @click="fetchItemsByPage"
                    class="w-full text-primary my-4 text-xs mx-auto underline ml-6">Cargar más elementos</button> -->
            </div>


        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import RegisteredSalesTable from '@/Components/MyComponents/Sale/RegisteredSalesTable.vue';
import Loading from '@/Components/MyComponents/Loading.vue';

export default {
data() {
    return {
        Loading: false,
        localSales: this.sales.data
    }
},
components:{
AppLayout,
RegisteredSalesTable,
Loading
},
props:{
sales: Object
},
methods:{

}
}
</script>

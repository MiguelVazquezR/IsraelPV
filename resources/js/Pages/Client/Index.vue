<template>
    <AppLayout title="Clientes">
        <div class="px-2 lg:px-10 py-7">
            <!-- header botones -->
            <div class="lg:flex justify-between items-center mx-3">
                <h1 class="font-bold text-lg">Clientes</h1>
                <div class="my-4 lg:my-0 flex items-center space-x-3">
                    <!-- <ThirthButton @click="openEntryModal" class="!rounded-full">Registrar abono</ThirthButton> -->
                    <PrimaryButton @click="$inertia.get(route('clients.create'))" class="!rounded-full">Nuevo cliente</PrimaryButton>
                </div>
            </div>

            <div class="lg:w-1/4 relative">
                <input v-model="searchQuery" @keydown.enter="searchProducts" class="input w-full pl-9"
                    placeholder="Buscar cliente" type="text">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            </div>

            <Loading v-if="loading" class="mt-20" />
            <div v-else class="mt-8 lg:w-11/12">
                <!-- <p v-if="localProducts.length" class="text-gray66 text-[11px]">{{ localProducts.length }} de {{ total_products }} elementos
                </p> -->
                <ClientTable :clients="localClients" />
                <!-- <p v-if="localProducts.length" class="text-gray66 text-[11px]">{{ localProducts.length }} de {{ total_products }} elementos
                </p> -->
                <!-- <p v-if="loadingItems" class="text-xs my-4 text-center">
                    Cargando <i class="fa-sharp fa-solid fa-circle-notch fa-spin ml-2 text-primary"></i>
                </p>
                <button v-else-if="total_products > 15 && localProducts.length < total_products && localProducts.length" @click="fetchItemsByPage"
                    class="w-full text-primary my-4 text-xs mx-auto underline ml-6">
                    Cargar más elementos
                </button> -->
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import ClientTable from '@/Components/MyComponents/Client/ClientTable.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
import Loading from '@/Components/MyComponents/Loading.vue';

export default {
data() {
    return {
        searchQuery: null,
        loading: false,
        localClients: this.clients.data,
    }
},
components:{
AppLayout,
PrimaryButton,
ThirthButton,
ClientTable,
Loading,
},
props:{
clients: Object
},
methods:{

}
}
</script>

<template>
    <AppLayout title="Clientes">
        <div class="px-2 lg:px-10 py-7">
            <h1 class="font-bold text-lg">Clientes</h1>
            <div class="lg:w-1/4 relative">
                <input v-model="searchQuery" @keydown.enter="searchClients" class="input w-full pl-9"
                    placeholder="Buscar cliente" type="text">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            </div>
            <!-- header botones -->
            <div class="flex justify-end items-center">
                <div class="my-2 lg:my-0 flex items-center space-x-1">
                    <!-- <ThirthButton @click="openEntryModal" class="!rounded-full">Registrar abono</ThirthButton> -->
                    <PrimaryButton @click="$inertia.get(route('clients.create'))" class="!rounded-full">Nuevo cliente
                    </PrimaryButton>
                </div>
            </div>
            <Loading v-if="loading" class="mt-20" />
            <div v-else class="mt-4 lg:w-11/12">
                <p v-if="localClients.length" class="text-gray66 text-[11px]">{{ localClients.length }} de {{
                    total_clients }} elementos
                </p>
                <ClientTable :clients="localClients" class="hidden md:block" />
                <ClientMobileIndex v-for="item in localClients" :key="item.id" :clientId="item.id" class="md:hidden" />
                <el-empty v-if="!localClients.length" description="No hay clientes registrados" />
                <p v-if="localClients.length" class="text-gray66 text-[11px]">{{ localClients.length }} de {{
                    total_clients }} elementos
                </p>
                <p v-if="loadingItems" class="text-xs my-4 text-center">
                    Cargando <i class="fa-sharp fa-solid fa-circle-notch fa-spin ml-2 text-primary"></i>
                </p>
                <button v-else-if="total_clients > 20 && localClients.length < total_clients && localClients.length"
                    @click="fetchItemsByPage" class="w-full text-primary my-4 text-xs mx-auto underline ml-6">
                    Cargar más elementos
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import ClientTable from '@/Components/MyComponents/Client/ClientTable.vue';
import ClientMobileIndex from '@/Components/MyComponents/Client/ClientMobileIndex.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
import Loading from '@/Components/MyComponents/Loading.vue';

export default {
    data() {
        return {
            searchQuery: null,
            loading: false,
            localClients: this.clients.data,
            loadingItems: false, //para paginación
            currentPage: 1, //para paginación
        }
    },
    components: {
        AppLayout,
        PrimaryButton,
        ThirthButton,
        ClientTable,
        ClientMobileIndex,
        Loading,
    },
    props: {
        clients: Object,
        total_clients: Number
    },
    methods: {
        async searchClients() {
            this.loading = true;
            try {
                const response = await axios.get(route('clients.search'), { params: { query: this.searchQuery } });
                if (response.status === 200) {
                    this.localClients = response.data.items;
                }
            } catch (error) {
                console.log(error);
                this.$notify({
                    title: "Cliente no encontrado",
                    message: 'No se encontró el cliente',
                    type: "warning",
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchItemsByPage() {
            try {
                this.loadingItems = true;
                const response = await axios.get(route('clients.get-by-page', this.currentPage));

                if (response.status === 200) {
                    this.localClients = [...this.localClients, ...response.data.items];
                    this.currentPage++;
                }
            } catch (error) {
                console.log(error)
            } finally {
                this.loadingItems = false;
            }
        },
    }
}
</script>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ClientTable from '@/Components/MyComponents/Client/ClientTable.vue';
import ClientMobileIndex from '@/Components/MyComponents/Client/ClientMobileIndex.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Loading from '@/Components/MyComponents/Loading.vue';
import axios from 'axios';
import { ElNotification } from 'element-plus';

const props = defineProps({
    clients: Object,
    total_clients: Number
});

const searchQuery = ref(null);
const loading = ref(false);
const localClients = ref(props.clients.data);
const loadingItems = ref(false);
const currentPage = ref(1);

const visibleDebtTotal = computed(() =>
    localClients.value.reduce((sum, client) => sum + Number(client.debt || 0), 0)
);

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const searchClients = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('clients.search'), { params: { query: searchQuery.value } });
        if (response.status === 200) {
            localClients.value = response.data.items;
        }
    } catch (error) {
        console.log(error);
        ElNotification({
            title: "Cliente no encontrado",
            message: 'No se encontraron coincidencias',
            type: "warning",
        });
    } finally {
        loading.value = false;
    }
};

const fetchItemsByPage = async () => {
    try {
        loadingItems.value = true;
        const response = await axios.get(route('clients.get-by-page', currentPage.value));

        if (response.status === 200) {
            localClients.value = [...localClients.value, ...response.data.items];
            currentPage.value++;
        }
    } catch (error) {
        console.log(error)
    } finally {
        loadingItems.value = false;
    }
};
</script>

<template>
    <AppLayout title="Clientes">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Clientes</h1>
                    <p class="text-sm text-gray-500 mt-1">Administra tu base de datos de clientes</p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 items-center w-full md:w-auto">
                    <!-- Buscador -->
                    <div class="relative w-full md:w-64">
                        <input 
                            v-model="searchQuery" 
                            @keydown.enter="searchClients" 
                            class="w-full pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full text-sm focus:ring-primary focus:border-primary transition-shadow shadow-sm"
                            placeholder="Buscar por nombre o teléfono..." 
                            type="text"
                        >
                        <i class="fa-solid fa-magnifying-glass text-gray-400 absolute top-1/2 -translate-y-1/2 left-3.5 text-sm"></i>
                    </div>

                    <PrimaryButton @click="router.visit(route('clients.create'))" class="!rounded-full w-full sm:w-auto justify-center">
                        <i class="fa-solid fa-plus mr-2"></i> Nuevo cliente
                    </PrimaryButton>
                </div>
            </div>

            <!-- Contenido -->
            <Loading v-if="loading" class="mt-20" />
            
            <div v-else class="space-y-4">
                <!-- Info de paginación -->
                <div v-if="localClients.length" class="flex flex-wrap items-center justify-between gap-2 px-1">
                    <span class="text-xs text-gray-500 font-medium">
                        Mostrando {{ localClients.length }} de {{ total_clients }} registros
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                        :class="visibleDebtTotal > 0 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600'"
                    >
                        <i class="fa-solid fa-circle-dollar-to-slot text-[11px]"></i>
                        Deuda mostrada: ${{ formatMoney(visibleDebtTotal) }}
                    </span>
                </div>

                <!-- Tabla Desktop -->
                <div class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <ClientTable :clients="localClients" />
                </div>

                <!-- Lista Móvil -->
                <div class="md:hidden grid grid-cols-1 gap-3">
                    <!-- IMPORTANTE: Pasamos el objeto 'client' completo, no solo el ID -->
                    <ClientMobileIndex 
                        v-for="item in localClients" 
                        :key="item.id" 
                        :client="item" 
                    />
                </div>

                <!-- Botón Cargar Más -->
                <div class="flex justify-center mt-6">
                    <p v-if="loadingItems" class="text-sm text-gray-500 flex items-center gap-2">
                        <i class="fa-solid fa-circle-notch fa-spin text-primary"></i> Cargando...
                    </p>
                    <button 
                        v-else-if="total_clients > 50 && localClients.length < total_clients && localClients.length > 0"
                        @click="fetchItemsByPage" 
                        class="text-sm text-primary hover:text-primary-dark font-medium underline underline-offset-4 decoration-2 decoration-transparent hover:decoration-current transition-all"
                    >
                        Cargar más clientes
                    </button>
                </div>
                
                <el-empty v-if="!localClients.length" description="No se encontraron clientes" />
            </div>
        </div>
    </AppLayout>
</template>
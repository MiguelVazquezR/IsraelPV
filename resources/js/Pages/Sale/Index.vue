<template>
    <AppLayout title="Ventas">
        <div class="min-h-screen bg-gray-50">
            <div class="px-4 lg:px-10 py-7 max-w-7xl mx-auto">
                
                <!-- Header con diseño moderno -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                    <div>
                        <h1 class="font-bold text-2xl text-gray-800">Ventas Registradas</h1>
                        <p class="text-sm text-gray-500 mt-1">Gestiona y consulta el historial de transacciones</p>
                    </div>
                    
                    <div class="relative w-full md:w-auto">
                        <!-- Botón Filtro Mejorado -->
                        <button @click.stop="showFilter = !showFilter"
                            class="w-full md:w-auto bg-white border border-gray-200 hover:border-primary/50 text-gray-700 rounded-lg py-2.5 px-5 flex items-center justify-center transition-all duration-200 shadow-sm hover:shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span class="text-sm font-medium">Filtrar Resultados</span>
                            <span v-if="activeFiltersCount > 0" class="ml-2 bg-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ activeFiltersCount }}
                            </span>
                        </button>

                        <!-- Panel de Filtros -->
                        <div v-if="showFilter"
                            class="absolute top-12 right-0 w-full md:w-80 bg-white border border-gray-100 rounded-xl p-5 shadow-xl z-20 animate-fade-in-down">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-semibold text-gray-700">Filtros de búsqueda</h3>
                                <button @click="clearFilters" class="text-xs text-red-500 hover:underline">Limpiar</button>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <InputLabel value="Rango de fechas" class="mb-1.5" />
                                    <el-date-picker v-model="searchDate" type="daterange" range-separator="a"
                                        start-placeholder="Inicio" end-placeholder="Fin" class="!w-full" size="default" />
                                </div>
                                <div>
                                    <InputLabel value="Cliente" class="mb-1.5" />
                                    <el-select v-model="searchClient" clearable filterable placeholder="Buscar cliente..."
                                        class="!w-full" no-data-text="No hay opciones" no-match-text="Sin coincidencias">
                                        <el-option v-for="client in clients" :key="client.id" :label="client.name"
                                            :value="client.id" />
                                    </el-select>
                                </div>
                                <PrimaryButton @click="searchSales" class="w-full justify-center !py-2.5 mt-2">
                                    Aplicar Filtros
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas Resumidas (Opcional, visualmente agradable) -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <span class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Total Ventas</span>
                        <p class="text-2xl font-bold text-gray-800">{{ total_sales.toLocaleString() }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <span class="text-xs text-gray-400 uppercase tracking-wider font-semibold">En pantalla</span>
                        <p class="text-2xl font-bold text-primary">{{ localSales.length.toLocaleString() }}</p>
                    </div>
                </div>

                <!-- Estado de Carga -->
                <div v-if="loading" class="flex flex-col items-center justify-center py-20">
                    <Loading class="mb-4" />
                    <p class="text-gray-400 text-sm animate-pulse">Buscando información...</p>
                </div>

                <!-- Tabla de Datos -->
                <div v-else class="rounded-xl shadow-sm overflow-hidden">
                    <div v-if="localSales.length">
                        <!-- Desktop View -->
                        <div class="hidden md:block overflow-x-auto">
                            <RegisteredSalesTable :sales="localSales" />
                        </div>
                        
                        <!-- Mobile View -->
                        <div class="md:hidden divide-y divide-gray-100">
                            <SaleMobileIndex v-for="item in localSales" :key="item.id" :sale="item" class="md:hidden" />
                        </div>
                    </div>
                    
                    <div v-else class="flex flex-col items-center justify-center py-16 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-lg font-medium">No se encontraron ventas</p>
                        <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                    </div>

                    <!-- Footer / Paginación -->
                    <div v-if="localSales.length" class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col items-center">
                        <p class="text-xs text-gray-500 mb-3">
                            Mostrando {{ localSales.length }} de {{ total_sales }} registros totales
                        </p>
                        
                        <div v-if="loadingItems" class="flex items-center text-primary text-sm font-medium">
                            <i class="fa-sharp fa-solid fa-circle-notch fa-spin mr-2"></i> Cargando más...
                        </div>
                        
                        <button v-else-if="canLoadMore"
                            @click="fetchItemsByPage" 
                            class="text-sm text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 hover:text-primary hover:border-primary transition-colors duration-200 rounded-full px-6 py-2 shadow-sm font-medium">
                            Cargar siguientes elementos
                        </button>
                        
                        <span v-else class="text-xs text-gray-400 italic">No hay más registros para mostrar</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import RegisteredSalesTable from '@/Components/MyComponents/Sale/RegisteredSalesTable.vue';
import SaleMobileIndex from '@/Components/MyComponents/Sale/SaleMobileIndex.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import Loading from '@/Components/MyComponents/Loading.vue';
import axios from 'axios';

export default {
    data() {
        return {
            loading: false,
            localSales: [],
            showFilter: false,
            searchDate: null,
            searchClient: null,
            loadingItems: false,
            currentPage: 1,
            // Agregamos paginación local para controlar mejor el flujo
            lastPage: 1, 
        }
    },
    components: {
        AppLayout,
        RegisteredSalesTable,
        SaleMobileIndex,
        PrimaryButton,
        InputLabel,
        Loading
    },
    props: {
        sales: Object,
        clients: Array,
        total_sales: Number
    },
    computed: {
        activeFiltersCount() {
            let count = 0;
            if (this.searchDate) count++;
            if (this.searchClient) count++;
            return count;
        },
        canLoadMore() {
            // Lógica más robusta para saber si hay más páginas
            return this.currentPage < this.lastPage;
        }
    },
    created() {
        // Inicializar datos al montar
        this.localSales = this.sales.data;
        this.lastPage = this.sales.meta ? this.sales.meta.last_page : 1;
    },
    methods: {
        clearFilters() {
            this.searchDate = null;
            this.searchClient = null;
            this.searchSales();
        },
        async searchSales() {
            this.loading = true;
            this.showFilter = false;
            try {
                const response = await axios.get(route('sales.search'), { 
                    params: { 
                        queryDate: this.searchDate, 
                        queryClient: this.searchClient 
                    } 
                });
                
                if (response.status == 200) {
                    this.localSales = response.data.items.data ? response.data.items.data : response.data.items;
                    // Resetear paginación al buscar
                    this.currentPage = 1;
                    this.lastPage = response.data.items.meta ? response.data.items.meta.last_page : 1;
                }

            } catch (error) {
                console.error("Error al buscar:", error);
                // Aquí podrías agregar una notificación de error tipo toast
            } finally {
                this.loading = false;
            }
        },
        async fetchItemsByPage() {
            // Evitar múltiples llamadas
            if (this.loadingItems) return;
            
            try {
                this.loadingItems = true;
                const nextPage = this.currentPage + 1;
                
                // Usamos la ruta original pero el backend ahora está optimizado
                const response = await axios.get(route('sales.get-by-page', nextPage));

                if (response.status === 200) {
                    const newItems = response.data.items.data ? response.data.items.data : response.data.items;
                    
                    // Concatenar eficientemente
                    this.localSales.push(...newItems);
                    
                    this.currentPage = nextPage;
                    // Actualizar last_page si viene en la respuesta
                    if (response.data.items.meta) {
                        this.lastPage = response.data.items.meta.last_page;
                    }
                }
            } catch (error) {
                console.error("Error al paginar:", error);
            } finally {
                this.loadingItems = false;
            }
        },
    }
}
</script>

<style scoped>
/* Animación suave para el dropdown */
.animate-fade-in-down {
    animation: fadeInDown 0.2s ease-out;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
<template>
    <AppLayout title="Abonos">
        <div class="px-2 lg:px-10 py-7">
            <!-- header botones -->
            <div class="flex justify-between items-center mx-3 mb-6">
                <h1 class="font-bold text-xl text-gray-800">Registro de abonos</h1>
                <div class="relative">
                    <!-- filtro -->
                    <button @click.stop="showFilter = !showFilter"
                        class="border border-gray-300 hover:bg-gray-50 hover:border-primary transition-colors rounded-full py-2 px-4 flex items-center shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="16" width="16" class="text-gray-600">
                            <path d="M0.73 4.2791H23.27" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                            <path d="M3.131 9.426H20.869" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                            <path d="M8.7141 19.7209H15.2859" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                            <path d="M5.531 14.573H18.469" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                        </svg>
                        <p class="text-sm ml-2 font-medium text-gray-700">Filtrar</p>
                    </button>
                    
                    <!-- Dropdown filtro -->
                    <div v-if="showFilter"
                        class="absolute top-12 right-0 border border-gray-200 rounded-xl p-5 bg-white shadow-xl z-20 w-80">
                        <div class="mb-4">
                            <InputLabel value="Rango de fechas" class="ml-1 mb-1 text-xs uppercase tracking-wide text-gray-500" />
                            <el-date-picker v-model="searchDate" type="daterange" range-separator="A"
                                start-placeholder="Inicio" end-placeholder="Fin" class="!w-full" size="large" />
                        </div>
                        <div class="mb-4">
                            <InputLabel value="Cliente" class="ml-1 mb-1 text-xs uppercase tracking-wide text-gray-500" />
                            <el-select v-model="searchClient" clearable filterable placeholder="Seleccione un cliente"
                                no-data-text="No hay clientes" size="large"
                                no-match-text="No encontrado" class="!w-full">
                                <el-option v-for="client in clients" :key="client.id" :label="client.name"
                                    :value="client.id" />
                            </el-select>
                        </div>
                        <PrimaryButton @click="searchSales" class="w-full justify-center">Aplicar Filtros</PrimaryButton>
                    </div>
                </div>
            </div>

            <Loading v-if="loading" class="mt-20" />
            
            <div v-else class="mt-4 lg:w-11/12 mx-auto">
                <p v-if="localGolabalPayments.length" class="text-gray-500 text-xs mb-3 font-medium">
                    Mostrando {{ localGolabalPayments.length }} de {{ total_global_payments }} registros
                </p>
                
                <!-- Vista Desktop -->
                <div class="hidden md:block">
                    <GlobalPaymentsTable :global_payments="localGolabalPayments" />
                </div>
                
                <!-- Vista Móvil (Optimizada) -->
                <div class="md:hidden">
                    <!-- Pasamos el objeto 'payment' completo, ya NO solo el ID -->
                    <GlobalPaymentsMobileIndex 
                        v-for="item in localGolabalPayments" 
                        :key="item.id" 
                        :payment="item" 
                    />
                    <el-empty v-if="!localGolabalPayments.length" description="No hay abonos registrados" />
                </div>

                <!-- Botón cargar más -->
                <p v-if="loadingItems" class="text-xs my-6 text-center text-gray-500 font-medium">
                    Cargando más registros <i class="fa-sharp fa-solid fa-circle-notch fa-spin ml-2 text-primary"></i>
                </p>
                <button v-else-if="localGolabalPayments.length < total_global_payments"
                    @click="fetchItemsByPage" 
                    class="block w-full max-w-xs mx-auto bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 hover:text-primary font-medium py-2 px-4 rounded-full shadow-sm transition-all duration-200 text-sm my-8">
                    Cargar siguientes 100 registros
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import GlobalPaymentsTable from '@/Components/MyComponents/GlobalPayment/GlobalPaymentsTable.vue';
import GlobalPaymentsMobileIndex from '@/Components/MyComponents/GlobalPayment/GlobalPaymentsMobileIndex.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import Loading from '@/Components/MyComponents/Loading.vue';
import axios from 'axios';

export default {
    data() {
        return {
            loading: false,
            localGolabalPayments: this.global_payments,
            showFilter: false, 
            searchDate: null, 
            searchClient: null, 
            loadingItems: false, 
            currentPage: 1, 
        }
    },
    components: {
        AppLayout,
        GlobalPaymentsTable,
        GlobalPaymentsMobileIndex,
        PrimaryButton,
        InputLabel,
        Loading
    },
    props: {
        global_payments: Object,
        clients: Array,
        total_global_payments: Number
    },
    methods: {
        async searchSales() {
            this.loading = true;
            try {
                const response = await axios.get(route('global-payments.search'), { params: { queryDate: this.searchDate, queryClient: this.searchClient } });
                if (response.status == 200) {
                    this.localGolabalPayments = response.data.items;
                }

            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
                this.showFilter = false;
            }
        },
        async fetchItemsByPage() {
            try {
                this.loadingItems = true;
                const response = await axios.get(route('global-payments.get-by-page', this.currentPage));

                if (response.status === 200) {
                    this.localGolabalPayments = [...this.localGolabalPayments, ...response.data.items];
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
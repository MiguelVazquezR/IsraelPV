<template>
    <AppLayout title="Ventas">
        <div class="px-2 lg:px-10 py-7">
            <!-- header botones -->
            <div class="flex justify-between items-center mx-3">
                <h1 class="font-bold text-lg">Ventas registradas</h1>
                <div class="relative">
                    <!-- filtro -->
                    <button @click.stop="showFilter = !showFilter"
                        class="border border-[#D9D9D9] rounded-full py-1 px-4 flex items-center">
                        <!-- <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5" />
                        </svg> -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="16" width="16"
                            id="Filter-Sort-Lines-Descending--Streamline-Ultimate">
                            <desc>Filter Sort Lines Descending Streamline Icon: https://streamlinehq.com</desc>
                            <defs></defs>
                            <title>filter</title>
                            <path d="M0.73 4.2791H23.27" fill="none" stroke="#000000" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="1"></path>
                            <path d="M3.131 9.426H20.869" fill="none" stroke="#000000" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="1"></path>
                            <path d="M8.7141 19.7209H15.2859" fill="none" stroke="#000000" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="1"></path>
                            <path d="M5.531 14.573H18.469" fill="none" stroke="#000000" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="1"></path>
                        </svg>
                        <p class="text-sm ml-2">Filtrar</p>
                    </button>
                    <div v-if="showFilter"
                        class="absolute top-9 right-0 lg:-left-64 border border[#D9D9D9] rounded-md p-4 bg-white shadow-lg z-10 w-80">
                        <div>
                            <InputLabel value="Rango de fechas" class="ml-3 mb-1" />
                            <el-date-picker v-model="searchDate" type="daterange" range-separator="A"
                                start-placeholder="Fecha de inicio" end-placeholder="Fecha de fin" class="!w-full" />
                        </div>
                        <div class="my-3">
                            <InputLabel value="Cliente" class="ml-3 mb-1" />
                            <el-select v-model="searchClient" clearable filterable placeholder="Seleccione"
                                no-data-text="No hay opciones registradas"
                                no-match-text="No se encontraron coincidencias">
                                <el-option v-for="client in clients" :key="client" :label="client.name"
                                    :value="client.id" />
                            </el-select>
                        </div>
                        <PrimaryButton @click="searchSales" class="!py-1">Aplicar</PrimaryButton>
                    </div>
                </div>
            </div>

            <Loading v-if="loading" class="mt-20" />
            <div v-else class="mt-8 lg:w-11/12">
                <p v-if="localSales.length" class="text-gray66 text-[11px]">{{ localSales.length }} de {{ total_sales }}
                    elementos
                </p>
                <RegisteredSalesTable :sales="localSales" class="hidden md:block" />
                <SaleMobileIndex v-for="item in localSales" :key="item.id" :saleId="item.id" class="md:hidden" />
                <p v-if="localSales.length" class="text-gray66 text-[11px]">{{ localSales.length }} de {{ total_sales }}
                    elementos
                </p>
                <p v-if="loadingItems" class="text-xs my-4 text-center">
                    Cargando <i class="fa-sharp fa-solid fa-circle-notch fa-spin ml-2 text-primary"></i>
                </p>
                <button v-else-if="total_sales > 20 && (localSales.length < total_sales) && localSales.length"
                    @click="fetchItemsByPage" class="w-full text-primary my-4 text-xs mx-auto underline ml-6">Cargar más
                    elementos</button>
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
            localSales: this.sales.data,
            showFilter: false, //filtro opciones
            searchDate: null, //filtro fechas
            searchClient: null, //filtro cliente
            loadingItems: false, //para paginación
            currentPage: 1, //para paginación
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
    methods: {
        async searchSales() {
            this.loading = true;
            try {
                const response = await axios.get(route('sales.search'), { params: { queryDate: this.searchDate, queryClient: this.searchClient } });
                if (response.status == 200) {
                    this.localSales = response.data.items;
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
                const response = await axios.get(route('sales.get-by-page', this.currentPage));

                if (response.status === 200) {
                    this.localSales = [...this.localSales, ...response.data.items];
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

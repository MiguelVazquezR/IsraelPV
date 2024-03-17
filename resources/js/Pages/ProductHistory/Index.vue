<template>
    <AppLayout title="Ventas">
        <div class="px-2 lg:px-10 py-7">
            <!-- header botones -->
            <div class="flex justify-between items-center mx-3">
                <h1 class="font-bold text-lg">Registro de entradas de producto</h1>

                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <button @click.stop="showFilter = !showFilter" class="border border-[#D9D9D9] rounded-full py-1 px-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5" />
                            </svg>
                            <p class="text-sm ml-2">Filtrar</p>
                        </button>
                        <div v-if="showFilter" class="absolute top-9 -left-64 border border[#D9D9D9] rounded-md p-4 bg-white">
                            <div>
                                <InputLabel value="Rango de fechas" class="ml-3 mb-1" />
                                <el-date-picker
                                    v-model="searchDate"
                                    type="daterange"
                                    range-separator="A"
                                    start-placeholder="Fecha de inicio"
                                    end-placeholder="Fecha de fin"
                                    :size="size"
                                />
                            </div>
                            <div class="my-3">
                                <InputLabel value="Producto" class="ml-3 mb-1" />
                                <el-select v-model="searchProduct" clearable filterable
                                    placeholder="Seleccione" no-data-text="No hay opciones registradas"
                                    no-match-text="No se encontraron coincidencias">
                                    <el-option v-for="product in products" :key="product" :label="product.name" :value="product.id" />
                                </el-select>
                            </div>
                            <PrimaryButton @click="filterHistory" class="!py-1">Aplicar</PrimaryButton>
                        </div>
                    </div>
                    <PrimaryButton @click="entryProductModal = true">Registrar entrada</PrimaryButton>
                </div>
            </div>

            <Loading v-if="loading" class="mt-20" />
            <div v-else class="mt-8 lg:w-11/12">
                <p v-if="localHistory.length" class="text-gray66 text-[11px]">{{ localHistory.length }} de {{ total_histories }} elementos
                </p>
                <ProductHistoryTable :histories="localHistory" class="hidden md:block" />
                <ProductHistoryMobileIndex  v-for="item in localHistory" :key="item.id" :historyId="item.id" class="md:hidden" />
                <p v-if="localHistory.length" class="text-gray66 text-[11px]">{{ localHistory.length }} de {{ total_histories }} elementos
                </p>
                <p v-if="loadingItems" class="text-xs my-4 text-center">
                    Cargando <i class="fa-sharp fa-solid fa-circle-notch fa-spin ml-2 text-primary"></i>
                </p>
                <button v-else-if="total_histories > 20 && (localHistory.length < total_histories) && localHistory.length" @click="fetchItemsByPage"
                    class="w-full text-primary my-4 text-xs mx-auto underline ml-6">Cargar más elementos</button>
            </div>
        </div>

        <!-- -------------- Modal starts----------------------- -->
        <Modal :show="entryProductModal" @close="entryProductModal = false">
            <div class="p-4 relative">
                <i @click="closeEntryModal"
                    class="fa-solid fa-xmark cursor-pointer w-5 h-5 rounded-full border border-black flex items-center justify-center absolute right-3"></i>

                <h1 class="font-bold my-4">Ingresar producto a almacén</h1>
                <section class="text-center mt-5 mb-2 mx-5">
                    <div class="mt-3 col-span-2">
                        <InputLabel value="Código o nombre del producto*" class="ml-3 mb-1" />
                        <el-input v-model="form.code" @keydown.enter="getProduct" ref="codeInput"
                            placeholder="Escribe el nombre o código del producto" :maxlength="100" clearable>
                            <template #prefix>
                                <i class="fa-solid fa-barcode"></i>
                            </template>
                        </el-input>
                    </div>
                    <div v-if="productEntryFound?.length > 0" class="mt-3">
                        <InputLabel value="Cantidad" class="ml-3 mb-1 text-sm" />
                        <el-input v-model="form.quantity" ref="quantityInput" autofocus @keydown.enter="entryProduct"
                            placeholder="Catidad que entra a almacén"
                            >
                            <template #prefix>
                                <i class="fa-solid fa-hashtag"></i>
                            </template>
                        </el-input>
                        <InputError :message="form.errors.quantity" />
                    </div>

                    <!-- estado de carga -->
                    <div v-if="loading" class="flex justify-center items-center py-10">
                        <i class="fa-solid fa-square fa-spin text-4xl text-primary"></i>
                    </div>

                    <!-- informacion del producto escaneado -->
                    <div v-if="productEntryFound?.length > 0" class="mt-5 grid grid-cols-3">
                        <figure class="w-32 ml-16">
                            <img class="w-32 object-contain" :src="productEntryFound[0]?.imageCover[0]?.original_url"
                                alt="">
                        </figure>

                        <div class="col-span-2 text-left">
                            <p>Nombre: <strong class="ml-2">{{ productEntryFound[0]?.name }}</strong></p>
                            <p>Precio: <strong class="ml-2">${{ productEntryFound[0]?.public_price }}</strong></p>
                            <p>Existencias: <strong class="ml-2">{{ productEntryFound[0]?.current_stock }}</strong></p>
                        </div>
                    </div>
                    <p v-else-if="!loading && form.code" class="mt-5 text-gray-500 text-center text-sm">No se encontró
                        ningun producto</p>


                    <div class="flex justify-end space-x-3 pt-7 pb-1 py-2">
                        <PrimaryButton :disabled="!form.quantity || form.processing" @click="entryProduct" class="!rounded-full">Ingresar producto</PrimaryButton>
                        <CancelButton @click="closeEntryModal">Cancelar</CancelButton>
                    </div>
                </section>
            </div>
        </Modal>
        <!-- --------------------------- Modal ends ------------------------------------ -->
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductHistoryMobileIndex from '@/Components/MyComponents/ProductHistory/ProductHistoryMobileIndex.vue';
import ProductHistoryTable from '@/Components/MyComponents/ProductHistory/ProductHistoryTable.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CancelButton from "@/Components/MyComponents/CancelButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Loading from '@/Components/MyComponents/Loading.vue';
import Modal from "@/Components/Modal.vue";
import { useForm } from "@inertiajs/vue3";
import axios from 'axios';

export default {
data() {
    const form = useForm({
            code: null,
            quantity: null,
        });

    return {
        form,
        Loading: false,
        localHistory: this.history.data,
        showFilter: false, //filtro opciones
        searchDate: null, //filtro fechas
        searchProduct: null, //filtro cliente
        loadingItems: false, //para paginación
        currentPage: 1, //para paginación
        entryProductModal: false, //modal para entrar producto
        productEntryFound: null, //producto encontrado
    }
},
components:{
AppLayout,
ProductHistoryMobileIndex,
ProductHistoryTable,
PrimaryButton,
CancelButton,
InputLabel,
InputError,
Loading,
Modal
},
props:{
history: Object,
products: Array,
total_histories: Number,
},
methods:{
    async filterHistory() {
            this.loading = true;
            try {
                const response = await axios.get(route('product-histories.filter'), { params: { queryDate: this.searchDate, queryProduct: this.searchProduct } });
                if (response.status == 200) {
                    this.localHistory = response.data.items;
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
                const response = await axios.get(route('product-histories.get-by-page', this.currentPage));

                if (response.status === 200) {
                    this.localHistory = [...this.localHistory, ...response.data.items];
                    this.currentPage++;
                }
            } catch (error) {
                console.log(error)
            } finally {
                this.loadingItems = false;
            }
        },
        async getProduct() {
            try {
                this.loading = true;
                const response = await axios.get(route('products.search'), { params: { query: this.form.code } });
                if (response.status == 200) {
                    this.productEntryFound = response.data.items;
                    console.log(this.productEntryFound);
                    if (this.productEntryFound?.length > 0) {
                            this.$nextTick(() => {
                            this.$refs.quantityInput?.focus(); // Enfocar el input de cantidad cuando se encuentra el producto
                        });
                    } else {
                        this.$notify({
                            title: "Producto no encontrado",
                            message: 'No se encontró el producto',
                            type: "warning",
                        });
                    }
                }

            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        },
        entryProduct() {
            this.form.put(route('products.entry', this.productEntryFound[0]?.id), {
                onSuccess: () => {
                    this.$nextTick(() => {
                        this.$refs.codeInput.focus(); // Enfocar el input de código cuando se abre el modal
                    });
                    this.$notify({
                        title: "Correcto",
                        message: 'Se ha ingresado ' + this.form.quantity + ' unidades de ' + this.productEntryFound[0].name,
                        type: "success",
                    });
                        this.form.reset();
                        this.productEntryFound = null;
                },
            });
        },
        closeEntryModal() {
            // this.form.reset();
            // this.productEntryFound = null;
            // this.entryProductModal = false;
            location.reload(); //refresco la página para cargar los nuevos registros agregados
        }
}
}
</script>

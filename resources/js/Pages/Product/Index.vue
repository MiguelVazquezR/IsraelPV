<template>
    <AppLayout title="Productos">
        <div class="px-2 lg:px-10 py-7">
            <h1 class="font-bold text-lg">Productos</h1>
            <div class="lg:w-1/4 relative">
                <input v-model="searchQuery" @keydown.enter="searchProducts" class="input w-full pl-9"
                    placeholder="Buscar producto" type="text">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            </div>

            <!-- header botones -->
            <div class="flex justify-end items-center">
                <div class="my-2 lg:my-0 flex items-center space-x-1">
                    <ThirthButton @click="openEntryModal" class="!rounded-full">Entrada de producto
                    </ThirthButton>
                    <PrimaryButton @click="$inertia.get(route('products.create'))" class="!rounded-full">Nuevo producto
                    </PrimaryButton>
                </div>
            </div>
            <Loading v-if="loading" class="mt-20" />
            <div v-else class="mt-8">
                <p v-if="localProducts.length" class="text-gray66 text-[11px]">{{ localProducts.length }} de {{
                    total_products }} elementos
                </p>
                <ProductTable :products="localProducts" />
                <p v-if="localProducts.length" class="text-gray66 text-[11px]">{{ localProducts.length }} de {{
                    total_products }} elementos
                </p>
                <p v-if="loadingItems" class="text-xs my-4 text-center">
                    Cargando <i class="fa-sharp fa-solid fa-circle-notch fa-spin ml-2 text-primary"></i>
                </p>
                <button v-else-if="total_products > 15 && localProducts.length < total_products && localProducts.length"
                    @click="fetchItemsByPage" class="w-full text-primary my-4 text-xs mx-auto underline ml-6">Cargar más
                    elementos</button>
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
                            placeholder="Cantidad que entra a almacén"
                            :formatter="(value) => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                            :parser="(value) => value.replace(/\D/g, '')">
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
                    <div v-if="productEntryFound?.length > 0" class="mt-5 md:grid grid-cols-3">
                        <figure class="w-32 ml-16">
                            <img class="w-32 object-contain" :src="productEntryFound[0]?.imageCover[0]?.original_url">
                        </figure>

                        <div class="col-span-2 text-left">
                            <p>Nombre: <strong class="ml-2">{{ productEntryFound[0]?.name }}</strong></p>
                            <p>Precio: <strong class="ml-2">${{ productEntryFound[0]?.public_price }}</strong></p>
                            <p>Existencias: <strong class="ml-2">{{ productEntryFound[0]?.current_stock }}</strong></p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-7 pb-1 py-2">
                        <PrimaryButton :disabled="!form.quantity || form.processing" @click="entryProduct"
                            class="!rounded-full">Ingresar producto</PrimaryButton>
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
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
import CancelButton from "@/Components/MyComponents/CancelButton.vue";
import ProductTable from '@/Components/MyComponents/Product/ProductTable.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Pagination from '@/Components/MyComponents/Pagination.vue';
import Loading from '@/Components/MyComponents/Loading.vue';
import InputError from "@/Components/InputError.vue";
import Modal from "@/Components/Modal.vue";
import { useForm } from "@inertiajs/vue3";

export default {
    data() {
        const form = useForm({
            code: null,
            quantity: null,
        });

        return {
            form,
            loading: false,
            searchQuery: null,
            searchFocus: false,
            entryProductModal: false,
            productEntryFound: null,
            localProducts: this.products.data,
            // paginacion
            loadingItems: false,
            currentPage: 1,
        };
    },
    components: {
        AppLayout,
        PrimaryButton,
        ThirthButton,
        CancelButton,
        ProductTable,
        Pagination,
        InputError,
        Modal,
        Loading,
        InputLabel
    },
    props: {
        products: Object,
        total_products: Number,
    },
    methods: {
        openEntryModal() {
            this.entryProductModal = true;
            this.$nextTick(() => {
                this.$refs.codeInput.focus(); // Enfocar el input de código cuando se abre el modal
            });
        },
        async fetchItemsByPage() {
            try {
                this.loadingItems = true;
                const response = await axios.get(route('products.get-by-page', this.currentPage));

                if (response.status === 200) {
                    this.localProducts = [...this.localProducts, ...response.data.items];
                    this.currentPage++;
                }
            } catch (error) {
                console.log(error)
            } finally {
                this.loadingItems = false;
            }
        },
        async searchProducts() {
            this.loading = true;
            try {
                const response = await axios.get(route('products.search'), { params: { query: this.searchQuery } });
                if (response.status == 200) {
                    this.localProducts = response.data.items;
                }

            } catch (error) {
                console.log(error);
                this.$notify({
                    title: "Producto no encontrado",
                    message: 'No se encontró el producto',
                    type: "warning",
                });
            } finally {
                this.loading = false;
            }
        },
        async getProduct() {
            try {
                this.loading = true;
                const response = await axios.get(route('products.search'), { params: { query: this.form.code } });
                if (response.status == 200) {
                    this.productEntryFound = response.data.items;
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
                    const IndexProductEntry = this.localProducts.findIndex(item => item.id == this.productEntryFound[0].id);
                    if (IndexProductEntry != -1) {
                        this.localProducts[IndexProductEntry].current_stock += parseInt(this.form.quantity);
                    }
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
            this.form.reset();
            this.productEntryFound = null;
            this.entryProductModal = false;
        }
    },
}
</script>

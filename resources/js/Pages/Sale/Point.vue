<template>
  <AppLayout title="Registrar venta">
    <div class="px-1 lg:px-6 py-5">
      <!-- header botones -->
      <div class="flex justify-between items-center mx-3">
        <h1 class="font-bold text-lg">Registrar venta</h1>
      </div>

      <!-- cuerpo de la pagina -->
      <div class="lg:flex lg:space-x-5 my-8 text-xs lg:text-sm">
        <!-- scaner de código  -->
        <section class="lg:w-[70%]">
          <!-- Pestañas -->
          <div class="lg:mx-7">
            <el-tabs v-model="editableTabsValue" type="card" class="mb-5">
              <div class="m-4 flex justify-between items-center">
                <div class="flex items-center space-x-3 w-full md:w-1/2">
                  <p class="font-bold">Cliente</p>
                  <el-tooltip content="Si no es necesario agregar un cliente específico, no selecciones ninguna opción"
                    placement="top">
                    <div class="rounded-full border border-primary w-3 h-3 flex items-center justify-center px-1">
                      <i class="fa-solid fa-info text-primary text-[7px]"></i>
                    </div>
                  </el-tooltip>
                  <el-select v-model="editableTabs[this.editableTabsValue - 1].client_id" clearable filterable
                    placeholder="Seleccione" no-data-text="No hay opciones registradas"
                    no-match-text="No se encontraron coincidencias">
                    <el-option v-for="client in clients" :key="client" :label="client.name" :value="client.id" />
                  </el-select>
                  <!-- <i :class="editableTabs[this.editableTabsValue - 1].client_id ? 'text-green-500' : 'text-gray-400'"
                    class="fa-solid fa-user-check text-sm"></i> -->
                  <button @click="showClientFormModal = true" type="button"
                    class="rounded-full  border-primary size-5 flex items-center justify-center text-primary text-lg">
                    <i class="fa-solid fa-circle-plus"></i>
                  </button>
                </div>
              </div>
              <el-tab-pane v-for="tab in editableTabs" :key="tab.name" :label="tab.title" :name="tab.name">
                <el-popconfirm v-if="tab.saleProducts.length" confirm-button-text="Si" cancel-button-text="No"
                  icon-color="#C30303" title="Se eliminará todo el registro de productos ¿Deseas continuar?"
                  @confirm="clearTab()">
                  <template #reference>
                    <ThirthButton class="!text-[#F80505] !border-[#F80505] !py-1 !px-2 mb-2"><i
                        class="fa-regular fa-trash-can mr-2"></i> Limpiar registros</ThirthButton>
                  </template>
                </el-popconfirm>
                <SaleTable @delete-product="deleteProduct" :saleProducts="tab.saleProducts" />
              </el-tab-pane>
            </el-tabs>
          </div>
        </section>

        <!-- seccion de desgloce de montos -->
        <section class="lg:w-[30%]">
          <!-- buscador de productos -->
          <div class="relative">
            <input v-model="searchQuery" @focus="searchFocus = true" @blur="handleBlur" @input="searchProducts"
              ref="searchInput" class="input w-full pl-9" placeholder="Buscar código o nombre de producto"
              type="search">
            <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            <!-- Resultados de la búsqueda -->
            <div v-if="searchFocus && searchQuery"
              class="absolute mt-1 bg-white border border-gray-300 rounded shadow-lg w-full z-50">
              <ul v-if="productsFound?.length > 0 && !loading">
                <li @click="productFoundSelected = product; searchQuery = null"
                  v-for="(product, index) in productsFound" :key="index"
                  class="hover:bg-gray-200 cursor-default text-sm px-5 py-2">{{ product.name }}</li>
              </ul>
              <p v-else-if="!loading" class="text-center text-sm text-gray-600 px-5 py-2">
                No se encontraron coincidencias
              </p>
              <!-- estado de carga -->
              <div v-if="loading" class="flex justify-center items-center py-10">
                <i class="fa-solid fa-square fa-spin text-4xl text-primary"></i>
              </div>
            </div>
          </div>

          <!-- Detalle de producto encontrado -->
          <div class="border border-grayD9 rounded-lg p-4 mt-5 text-xs lg:text-base">
            <div class="relative" v-if="productFoundSelected">
              <i @click="productFoundSelected = null"
                class="fa-solid fa-xmark cursor-pointer size-5 rounded-full flex items-center justify-center absolute right-3"></i>
              <figure class="h-32">
                <img class="object-contain h-32 mx-auto" :src="productFoundSelected?.imageCover[0]?.original_url">
              </figure>
              <div class="flex justify-between items-center mt-2 mb-4">
                <p class="font-bold">{{ productFoundSelected?.name }}</p>
                <p class="text-[#5FCB1F]">${{ productFoundSelected?.public_price }}</p>
              </div>
              <div class="flex justify-between items-center">
                <p class="text-gray99">Cantidad</p>
                <!-- <el-input-number v-model="quantity" :min="0" :max="productFoundSelected.current_stock" :precision="2" /> con validacion de inventario disponible-->
                <el-input-number v-model="quantity" :min="0" :precision="2" />
              </div>
              <div class="text-center mt-7">
                <PrimaryButton @click="addSaleProduct(this.productFoundSelected); productFoundSelected = null"
                  class="!rounded-full !px-24">Agregar
                </PrimaryButton>
              </div>
            </div>
            <p v-else class="text-center text-gray-500 text-sm">Sin información de producto</p>
          </div>

          <!-- Total por cobrar -->
          <div class="border border-grayD9 rounded-lg p-4 mt-5 text-xs lg:text-base">
            <div
              v-if="!editableTabs[this.editableTabsValue - 1]?.cash && !editableTabs[this.editableTabsValue - 1]?.credit">
              <div class="flex items-center justify-between text-lg mx-5">
                <p class="font-bold">Total</p>
                <p class="text-gray-99">$ <strong class="ml-3">{{
                calculateTotal().toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,
                ",") }}</strong></p>
              </div>
              <!-- botones -->
              <div class="text-center mt-7">
                <p class="text-sm text-gray-400 text-left mb-3">Opciones de pago</p>
                <div class="flex items-center justify-end space-x-4">
                  <PrimaryButton @click="creditPayment()"
                    :disabled="editableTabs[this.editableTabsValue - 1]?.saleProducts?.length == 0"
                    class="!px-9 !bg-[#baf09b] disabled:!bg-[#999999] !text-black">A crédito</PrimaryButton>
                  <PrimaryButton @click="cashPayment()"
                    :disabled="editableTabs[this.editableTabsValue - 1]?.saleProducts?.length == 0"
                    class="!px-9 !bg-[#5FCB1F] disabled:!bg-[#999999]">Al contado</PrimaryButton>
                </div>
              </div>
            </div>

            <!-- cobrando al contado -->
            <div v-if="editableTabs[this.editableTabsValue - 1]?.cash">
              <div class="flex items-center justify-between mb-3">
                <button @click="editableTabs[this.editableTabsValue - 1].cash = false; editableTabs[this.editableTabsValue - 1].moneyReceived = null">
                  <i class="fa-solid fa-angle-left text-xs px-2 cursor-pointer"></i>
                </button>
                <p class="text-gray-99 text-sm">Total $ <strong>{{
              calculateTotal().toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</strong>
                </p>
              </div>
              <div class="flex items-center justify-between mx-5 space-x-10">
                <p>Entregado</p>
                <input v-model="editableTabs[this.editableTabsValue - 1].moneyReceived" @keydown.enter="store"
                  type="number" class="input !rounded-md w-1/3" ref="receivedInput" placeholder="$0.00">
              </div>
              <div class="flex items-center justify-between mx-5 my-2 relative">
                <p>Cambio</p>
                <p v-if="calculateTotal() <= editableTabs[this.editableTabsValue - 1]?.moneyReceived">${{
              (editableTabs[this.editableTabsValue - 1]?.moneyReceived - calculateTotal()).toLocaleString('en-US', {
                minimumFractionDigits: 2
              }) }}</p>
              </div>
              <p v-if="(calculateTotal() > editableTabs[this.editableTabsValue - 1]?.moneyReceived) && editableTabs[this.editableTabsValue - 1].moneyReceived"
                class="text-xs text-red-600 text-center mb-3">La cantidad es insuficiente. Por favor, ingrese una
                cantidad
                igual o mayor al total de compra.</p>
              <div class="flex space-x-2 justify-end">
                <!-- <CancelButton
                  @click="editableTabs[this.editableTabsValue - 1].cash = false; editableTabs[this.editableTabsValue - 1].moneyReceived = null">
                  Cancelar</CancelButton> -->
                <PrimaryButton :disabled="storeProcessing" @click="checkClientExist" class="!rounded-full">Aceptar
                </PrimaryButton>
              </div>
            </div>

            <!-- Cobrando a crédito  -->
            <div v-if="editableTabs[this.editableTabsValue - 1]?.credit">
              <div class="flex items-center justify-between">
                <i @click="editableTabs[this.editableTabsValue - 1].credit = false; editableTabs[this.editableTabsValue - 1].deposit = null; editableTabs[this.editableTabsValue - 1].deposit_notes = null"
                  class="fa-solid fa-angle-left text-xs p-2 cursor-pointer"></i>
                <p class="text-gray-99">$ <strong class="ml-3">{{
              calculateTotal().toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,
                ",") }}</strong></p>
              </div>
              <h3 class="text-lg font-bold">Registrar abono de la venta</h3>
              <div class="flex items-center justify-between space-x-7 my-3">
                <div class="w-2/3">
                  <InputLabel value="Monto abonado (opcional)" class="text-sm ml-2 !text-gray-400" />
                  <el-input type="number" class="" v-model="editableTabs[this.editableTabsValue - 1].deposit"
                    placeholder="ingresa el abono">
                    <template #prefix>
                      <i class="fa-solid fa-dollar-sign"></i>
                    </template>
                  </el-input>
                </div>
                <div class="w-1/3">
                  <InputLabel value="Saldo restante" class="text-sm !text-gray-400" />
                  <p v-if="(calculateTotal() - editableTabs[this.editableTabsValue - 1].deposit) > 0">${{
              (calculateTotal() -
                editableTabs[this.editableTabsValue - 1].deposit)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            }}</p>
                  <p class="text-red-600 text-xs" v-else>La cantidad abonada debe de ser menor al monto total</p>
                </div>
              </div>
              <div class="w-2/3 pr-5">
                <InputLabel value="Fecha de vencimiento" class="text-sm !text-gray-400 ml-2" />
                <el-date-picker v-model="editableTabs[this.editableTabsValue - 1].limit_date" class="!w-full"
                  type="date" placeholder="Seleccione" :disabled-date="disabledDate" />
              </div>
              <div class="mt-3">
                <InputLabel value="Notas (opcional)" class="text-sm ml-2 !text-gray-400" />
                <el-input v-model="editableTabs[this.editableTabsValue - 1].deposit_notes"
                  :autosize="{ minRows: 3, maxRows: 5 }" type="textarea" placeholder="Escribe tus notas"
                  :maxlength="200" show-word-limit clearable />
              </div>
              <div class="flex items-center justify-end space-x-3 mt-4">
                <!-- <ThirthButton @click="editableTabs[this.editableTabsValue - 1].has_credit = true; checkClientExist()">No abonar</ThirthButton> -->
                <PrimaryButton @click="editableTabs[this.editableTabsValue - 1].has_credit = true; checkClientExist()"
                  :disabled="(calculateTotal() - editableTabs[this.editableTabsValue - 1].deposit) < 0">Finalizar venta
                </PrimaryButton>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- client form -->
    <DialogModal :show="showClientFormModal" @close="showClientFormModal = false">
      <template #title> Agregar cliente </template>
      <template #content>
        <form @submit.prevent="storeClient" class="md:grid grid-cols-2 gap-x-3">
          <div class="mt-3">
            <InputLabel value="Nombre*" class="ml-3 mb-1" />
            <el-input v-model="form.name" placeholder="Escribe el nombre del cliente" :maxlength="100" clearable />
            <InputError :message="form.errors.name" />
          </div>
          <div class="mt-3">
            <InputLabel value="RFC" class="ml-3 mb-1" />
            <el-input v-model="form.rfc" placeholder="Escribe el RFC en caso de tenerlo" :maxlength="100" clearable />
            <InputError :message="form.errors.rfc" />
          </div>
          <div class="mt-3">
            <InputLabel class="mb-1 ml-2" value="Teléfono *" />
            <el-input v-model="form.phone"
            :formatter="(value) => `${value}`.replace(/(\d{2})(\d{4})(\d{4})/, '$1 $2 $3')"
            :parser="(value) => value.replace(/\D/g, '')" maxlength="10" clearable
            placeholder="Escribe el número de teléfono" />
            <InputError :message="form.errors.phone" />
          </div>
          <div class="mt-3">
            <InputLabel value="Dirección" class="ml-3 mb-1" />
            <el-input v-model="form.address" placeholder="Escribe la dirección" :maxlength="100" clearable />
            <InputError :message="form.errors.address" />
          </div>
        </form>
      </template>
      <template #footer>
        <div class="flex items-center space-x-2">
          <CancelButton @click="showClientFormModal = false" :disabled="form.processing">Cancelar</CancelButton>
          <PrimaryButton @click="storeClient()" :disabled="form.processing">Crear</PrimaryButton>
        </div>
      </template>
    </DialogModal>

    <ConfirmationModal :show="showConfirmModal" @close="showConfirmModal = false">
      <template #title> No seleccionaste un cliente </template>
      <template #content> No has seleccionado cliente en la venta. ¿Deseas continuar? </template>
      <template #footer>
        <div>
          <CancelButton @click="showConfirmModal = false" class="mr-2">Cancelar</CancelButton>
          <PrimaryButton @click="showConfirmModal = false; store()">Continuar</PrimaryButton>
        </div>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
import CancelButton from "@/Components/MyComponents/CancelButton.vue";
import SaleTable from '@/Components/MyComponents/Sale/SaleTable.vue';
import InputLabel from "@/Components/InputLabel.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import axios from 'axios';
import { useForm } from "@inertiajs/vue3";

export default {
  data() {
    const form = useForm({
      name: null,
      rfc: null,
      phone: null,
      address: null,
    });

    return {
      form,
      showConfirmModal: false, //confirmar crear venta sin cliente seleccionado
      showClientFormModal: false, //modal para agregar un cliente
      storeProcessing: false, //cargando store de venta
      scanning: false, //cargando la busqueda de productos por escaner
      loading: false, //cargando la busqueda de productos
      scannerQuery: null, //input para scanear el codigo de producto
      searchQuery: null, //buscador
      searchFocus: false, //buscador
      productsFound: null, //buscador
      productSelected: null, //producto escaneado agergado a la lista de compras
      productFoundSelected: null, //producto seleccionado desde barra de busqueda
      quantity: 1, //cantidad para agregar del producto escaneado o buscado
      tabIndex: 1, //index del tab - componente de tabs
      editableTabsValue: "1", //tab seleccionado - componente de tabs
      editableTabs: [ //Informacion del tab - componente de tabs
        {
          title: "Carrito de compras",
          name: "1",
          saleProducts: [],
          cash: false,
          credit: false,
          moneyReceived: null,
          client_id: null,
          deposit: null,
          deposit_notes: null,
          has_credit: false,
          limit_date: null,
        },
        // {
        //   title: "Registro 2",
        //   name: "2",
        //   saleProducts: [],
        //   paying: false,
        //   moneyReceived: null,
        // },
      ],
    }
  },
  components: {
    AppLayout,
    ConfirmationModal,
    PrimaryButton,
    ThirthButton,
    CancelButton,
    DialogModal,
    InputError,
    InputLabel,
    SaleTable
  },
  props: {
    products: Array,
    clients: Array,
  },
  methods: {
    checkClientExist() {
      if (this.editableTabs[this.editableTabsValue - 1]?.client_id == null) {
        this.showConfirmModal = true;
      } else {
        this.store();
      }
    },
    async store() {
      try {
        this.storeProcessing = true;
        const response = await axios.post(route('sales.store'), {
          data: {
            saleProducts: this.editableTabs[this.editableTabsValue - 1]?.saleProducts,
            has_credit: this.editableTabs[this.editableTabsValue - 1]?.has_credit,
            client_id: this.editableTabs[this.editableTabsValue - 1]?.client_id,
            deposit: this.editableTabs[this.editableTabsValue - 1]?.deposit,
            deposit_notes: this.editableTabs[this.editableTabsValue - 1]?.deposit_notes,
            limit_date: this.editableTabs[this.editableTabsValue - 1]?.limit_date,
            total: this.calculateTotal(),
          }
        });
        if (response.status === 200) {
          // this.$notify({
          //   title: "Correcto",
          //   text: "Se ha registrado la venta con éxito!",
          //   type: "success",
          // });
          this.storeProcessing = false;
          this.clearTab();
          window.open(route('sales.print-ticket', response.data.item.id), '_blank');
        }
      } catch (error) {
        console.log(error);
      }
    },
    deleteProduct(productId) {
      const indexToDelete = this.editableTabs[this.editableTabsValue - 1].saleProducts.findIndex(sale => sale.product.id === productId);
      this.editableTabs[this.editableTabsValue - 1].saleProducts.splice(indexToDelete, 1);
    },
    async searchProducts() {
      try {
        this.loading = true;
        const response = await axios.get(route('products.search'), { params: { query: this.searchQuery } });
        if (response.status === 200) {
          this.productsFound = response.data.items;
          this.loading = false;
        }
      } catch (error) {
        console.log(error);
      }
    },
    handleBlur() {
      // Introducir un retraso para dar tiempo al evento click de ejecutarse antes del blur
      setTimeout(() => {
        this.searchFocus = false;
      }, 80);
    },
    async getProductByCode() {
      this.scanning = true;
      let productScaned = this.products.find(item => item.code === this.scannerQuery);

      try {
        if (productScaned && productScaned.id) {
          const response = await axios.get(route('products.get-product-scaned', productScaned.id));

          if (response.status === 200 && response.data && response.data.item) {
            this.productSelected = response.data.item;
            this.addSaleProduct(this.productSelected);
          } else {
            console.error('La respuesta no tiene el formato esperado.');
          }
        } else {
          this.$notify({
            title: "Poducto no encontrado",
            message: "El producto escaneado no esta registrado en la base de datos",
            type: "warning"
          });
          console.error('El producto escaneado no tiene la propiedad "id".');
          this.scannerQuery = null;
        }
      } catch (error) {
        console.error('Error al realizar la solicitud:', error);
      } finally {
        this.scanning = false;
      }
    },
    addSaleProduct(product) {
      //revisa si el producto escaneado ya esta dentro del arreglo
      const existingIndex = this.editableTabs[this.editableTabsValue - 1].saleProducts.findIndex(sale => sale.product.id === product.id);
      if (existingIndex !== -1) {
        this.editableTabs[this.editableTabsValue - 1].saleProducts[existingIndex] = {
          ...this.editableTabs[this.editableTabsValue - 1].saleProducts[existingIndex],
          quantity: this.editableTabs[this.editableTabsValue - 1].saleProducts[existingIndex].quantity + this.quantity
        };
      } else {
        // Si el producto no existe, agrégalo al array
        this.editableTabs[this.editableTabsValue - 1].saleProducts.push({
          product: product,
          quantity: this.quantity
        });
      }
      this.scannerQuery = null;
      this.quantity = 1;
      this.scanning = false;
      // this.scanInputFocus();
    },
    clearTab() {
      this.searchQuery = null;
      this.scannerQuery = null;
      this.searchFocus = false;
      this.productsFound = null;
      this.productSelected = null;
      this.editableTabs[this.editableTabsValue - 1].saleProducts = [];
      this.editableTabs[this.editableTabsValue - 1].client_id = null;
      this.editableTabs[this.editableTabsValue - 1].deposit = null;
      this.editableTabs[this.editableTabsValue - 1].deposit_notes = null;
      this.editableTabs[this.editableTabsValue - 1].cash = false;
      this.editableTabs[this.editableTabsValue - 1].has_credit = false;
      this.editableTabs[this.editableTabsValue - 1].moneyReceived = null;
      this.editableTabs[this.editableTabsValue - 1].limit_date = null;
      // this.scanInputFocus();
    },
    calculateTotal() {
      // Suma de los productos del precio y la cantidad para cada elemento en saleProducts
      const total = this.editableTabs[this.editableTabsValue - 1]?.saleProducts?.reduce((accumulator, sale) => {
        return accumulator + sale.product.public_price * sale.quantity;
      }, 0);

      // Formatear el resultado al final
      return total;
    },
    cashPayment() {
      this.editableTabs[this.editableTabsValue - 1].cash = true;
      // this.receivedInputFocus();
    },
    creditPayment() {
      this.editableTabs[this.editableTabsValue - 1].credit = true;
    },
    // scanInputFocus() {
    //   this.$nextTick(() => {
    //     this.$refs.scanInput.focus(); // Enfocar el input de código cuando se abre el modal
    //   });
    // },
    receivedInputFocus() {
      this.$nextTick(() => {
        this.$refs.receivedInput.focus(); // Enfocar el input de código cuando se abre el modal
      });
    },
    storeClient() {
      this.form.post(route('clients.store'), {
        onSuccess: () => {
          this.$notify({
            title: "Éxito",
            message: "Se ha creado un nuevo ccliente",
            type: "success",
          });
          this.showClientFormModal = false;
        },
      });
    },
    disabledDate(time) {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return time.getTime() < today.getTime();
    },
  },
  mounted() {
    // this.$refs.scanInput.focus(); // Enfocar el input de código cuando se abre el modal
  }
}
</script>

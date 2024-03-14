<template>
    <AppLayout :title="'Venta' + sale.data.folio">
        <div class="px-10 py-7">
            <div class="flex justify-between items-center">
                <Back />
                <div class="flex items-center space-x-2">
                    <ThirthButton @click="paymentModal = true" v-if="sale.data.has_credit && (sale.data.total - totalPaymentAmount) > 0" class="!rounded-full">Registrar abono</ThirthButton>
                    <p v-else class="text-lg font-bold text-green-500">Pagado</p>
                     <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#C30303" title="¿Continuar?" @confirm="deleteItem(sale.data.id)">
                        <template #reference>
                            <i @click.stop class="fa-regular fa-trash-can text-primary cursor-pointer bg-gray-200 rounded-full p-2"></i>
                        </template>
                    </el-popconfirm>
                </div>
            </div>

            <!-- Información de la venta -->
            <div class="mt-7 lg:mx-16">
                <p class="font-bold">Folio: <span class="!font-thin ml-2 text-gray-600">{{ sale.data.folio }}</span></p>
                <p class="font-bold">Fecha de venta: <span class="!font-thin ml-2 text-gray-600">{{ sale.data.created_at }}</span></p>
                <p class="font-bold">Cliente: <span class="!font-thin ml-2 text-gray-600">{{ sale.data.client?.name ?? '-' }}</span></p>
                <p class="font-bold">Método de pago: <span class="!font-thin ml-2 text-gray-600">{{ sale.data.has_credit ? 'A crédito' : 'Al contado'}}</span></p>
                <p class="font-bold">Total de venta: <span class="!font-thin ml-2 text-gray-600">${{ sale.data.total?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span></p>
                <p v-if="sale.data.has_credit" class="font-bold">Abonos: <span class="!font-thin ml-2 text-gray-600">${{ totalPaymentAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span></p>
                <p v-if="sale.data.has_credit" class="font-bold">Crédito vence el: <span class="!font-thin ml-2 text-red-600">{{ sale.data.limit_date }}</span></p>
                <p v-if="sale.data.has_credit" class="font-bold bg-yellow-200 inline-block">Saldo restante: <span class="!font-thin ml-2 text-gray-600">${{ (sale.data.total - totalPaymentAmount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span></p>
            </div>

            <!-- Productos -->
            <section class="mt-7 grid grid-cols-2">
                <!-- detalle de productos -->
                <div :class="sale.data.has_credit ? 'border-r' : '' " class="grid grid-cols-3 lg:ml-16 mr-3">
                    <p class="font-bold">Producto</p>
                    <p class="font-bold">Cantidad</p>
                    <p class="font-bold ml-8">Total</p>

                    <div class="mt-2">
                        <p @click="$inertia.get(route('products.show', product.id))" class="text-primary underline cursor-pointer text-sm" v-for="product in sale.data.products" :key="product">{{ product.name }}</p>
                    </div>
                    <div class="mt-2">
                        <p class="text-sm" v-for="product in sale.data.products" :key="product">{{ product.pivot.quantity }}</p>
                    </div>
                    <div class="mt-2 ml-8">
                        <p class="text-sm" v-for="product in sale.data.products" :key="product">${{ (product.pivot.quantity * product.pivot.price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
                    </div>
                    <div class="border-b border-primary w-28 col-start-3 my-3"></div>
                    <p class="col-start-3 text-sm font-bold">Total: <span class="ml-2">${{ sale.data.total?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span></p>
                </div>

                <!-- Abonos -->
                <div v-if="sale.data.has_credit" class="ml-3 lg:ml-8">
                    <h2 class="font-bold mb-2">Abonos</h2>
                    <div class="grid grid-cols-2">
                        <p class="font-bold">Fecha</p>
                        <p class="font-bold ml-8">Monto</p>
                        
                        <div class="mt-2">
                            <p class="text-sm" v-for="payment in sale.data.payments" :key="payment">{{ payment.created_at }}</p>
                        </div>
                        <div class="mt-2 ml-8">
                            <p class="text-sm" v-for="payment in sale.data.payments" :key="payment">${{ payment.amount }}</p>
                        </div>
                        <div class="border-b border-primary w-28 col-start-2 my-3"></div>
                        <p class="col-start-2 text-sm font-bold">Total: <span class="ml-2">${{ totalPaymentAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span></p>

                    </div>
                </div>

            </section>
        </div>


        <!-- -------------- Modal starts----------------------- -->
      <Modal :show="paymentModal" @close="paymentModal = false">
        <div class="p-4 relative">
          <i @click="paymentModal = false"
            class="fa-solid fa-xmark cursor-pointer w-5 h-5 rounded-full border border-black flex items-center justify-center absolute right-3"></i>

          <form class="mt-5 mb-2 grid grid-cols-2 gap-3" @submit.prevent="storePayment">
            <h2 class="font-bold col-span-full">Registrar abono</h2>
            
            <div>
                <el-select v-model="form.client_id" clearable filterable disabled
                    placeholder="Seleccione" no-data-text="No hay opciones registradas"
                    no-match-text="No se encontraron coincidencias">
                    <el-option v-for="client in clients" :key="client" :label="client.name" :value="client.id" />
                </el-select>
            </div>

            <div class="flex space-x-3 items-center -mt-4">
                <div>
                    <p class="text-gray-500 border-b border-primary pb-1 mb-1">Saldo pendiente</p>
                    <p>${{ (sale.data.total - totalPaymentAmount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
                </div>
                <i class="fa-solid fa-arrow-right-long text-primary px-3"></i>
                <div>
                    <p class="text-gray-500 border-b border-green-500 pb-1 mb-1">Saldo restante</p>
                    <p v-if="((sale.data.total - totalPaymentAmount) - form.amount) >= 0">${{ ((sale.data.total - totalPaymentAmount) - form.amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
                    <p class="text-xs text-red-600" v-else>Cantidad mayor al saldo</p>
                </div>
            </div>

            <div>
                <InputLabel value="Fecha de abono" class="ml-3 mb-1" />
                <!-- <el-date-picker v-model="form.date" type="date" placeholder="Seleccione" /> -->
                <input type="date" v-model="form.date" placeholder="Seleccione">
            </div>

            <div>
                <InputLabel value="Monto abonado" class="ml-3 mb-1 text-sm" />
                <el-input v-model="form.amount" placeholder="ingresa el monto"
                    >
                    <template #prefix>
                    <i class="fa-solid fa-dollar-sign"></i>
                    </template>
                </el-input>
            </div>

            <div class="col-span-full">
                <InputLabel value="Notas (opcional)" class="text-sm ml-2" />
                <el-input v-model="form.notes" :autosize="{ minRows: 3, maxRows: 5 }" type="textarea"
                    placeholder="Escribe tus notas" :maxlength="200" show-word-limit clearable />
            </div>

            <div class="flex justify-end space-x-3 pt-2 pb-1 py-2 col-span-full">
              <PrimaryButton :disabled="((sale.data.total - totalPaymentAmount) - form.amount) < 0 || !form.amount || !form.date">Abonar</PrimaryButton>
              <CancelButton @click="paymentModal = false; form.reset();">Cancelar</CancelButton>
            </div>
          </form>
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
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import Back from "@/Components/MyComponents/Back.vue";
import { useForm } from "@inertiajs/vue3";

export default {
data() {
    const form = useForm({
        client_id: this.sale.data.client?.id,
        sale_id: this.sale.data.id,
        amount: null,
        notes: null,
        date: null,
    });

    return {
        form,
        paymentModal: false,
    }
},
components:{
AppLayout,
PrimaryButton,
ThirthButton,
CancelButton,
InputLabel,
Modal,
Back
},
props:{
sale: Object,
clients: Array,
},
methods:{
    storePayment() {
        this.form.post(route('payments.store'), {
            onSuccess: () => {
                this.$notify({
                title: "Correcto",
                message: "Se ha registrado el abono",
                type: "success",
                });

                this.paymentModal = false;
                this.form.reset();
            },
      })
    },
async deleteItem(saleId) {
        try {
            const response = await axios.delete(route('sales.destroy', saleId));
            if (response.status == 200) {

                this.$notify({
                    title: 'Success',
                    message: 'Se ha eliminado la venta',
                    type: 'success',
                    position: 'bottom-right',
            });

            this.$inertia.get(route('sales.index'));
            }
        } catch (error) {
            console.log(error);
            this.$notify({
                title: 'Error',
                message: 'No se pudo eliminar la venta. Inténte más tarde',
                type: 'error',
                position: 'bottom-right',
            });
        }
    }
},
computed: {
    totalPaymentAmount() {
        return this.sale.data.payments.reduce((total, payment) => total + payment.amount, 0);
    }
}
}
</script>
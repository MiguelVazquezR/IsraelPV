<template>
    <AppLayout :title="client.name">
        <div class="px-2 lg:px-10 py-7 text-xs lg:text-sm">
            <Back />

            <!-- header botones -->
            <div class="lg:flex justify-between items-center mx-3 mt-4">
                <h1 class="font-bold text-lg">Detalles del cliente</h1>
                <div class="my-4 lg:my-0 flex items-center space-x-3">
                    <ThirthButton @click="debtModal = true" class="!rounded-full">Agregar adeudo</ThirthButton>
                    <ThirthButton @click="paymentModal = true" class="!rounded-full">Registrar abono</ThirthButton>
                    <PrimaryButton @click="$inertia.get(route('clients.edit', client.id))" class="!rounded-full">Editar
                    </PrimaryButton>
                </div>
            </div>

            <div class="lg:w-1/4 relative">
                <el-select v-model="currentClient" clearable filterable placeholder="Seleccione"
                    no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                    <el-option @click="$inertia.get(route('clients.show', client.id))" v-for="client in clients"
                        :key="client" :label="client.name" :value="client.id" />
                </el-select>
            </div>

            <!-- informacion del cliente -->
            <div class="grid grid-cols-3 lg:grid-cols-12 gap-1 my-7 ml-4">
                <p class="font-bold">Nombre</p>
                <p class="col-span-2 lg:col-span-11">{{ client.name }}</p>
                <p class="font-bold">RFC</p>
                <p class="col-span-2 lg:col-span-11">{{ client.rfc ?? '-' }}</p>
                <p class="font-bold">Teléfono</p>
                <p class="col-span-2 lg:col-span-11">{{ client.phone }}</p>
                <p class="font-bold">Dirección</p>
                <p class="col-span-2 lg:col-span-11">{{ client.address ?? '-' }}</p>
                <p class="font-bold">Deuda total</p>
                <p class="col-span-2 lg:col-span-11 bg-yellow-200 w-28">${{ totalDebt?.replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
            </div>

            <el-tabs v-model="activeTab">
                <el-tab-pane label="Ventas sin liquidar" name="1">
                    <SaleMobileDetail
                        v-for="item in reversedPendentSales"
                        :key="item.id" :saleId="item.id" class="md:hidden mb-2" />
                    <SaleDesktopDetail v-if="reversedPendentSales?.length"
                        :salesId="client.sales.filter(sale => sale.has_credit && sale.paid_at === null).map(item => item.id)"
                        class="hidden md:block mb-2" />
                    <el-empty v-else description="No hay ventas con adeudos" />
                </el-tab-pane>
                <el-tab-pane label="Todas las ventas" name="2">
                    <SaleMobileDetail v-for="item in reversedSales" :key="item.id" :saleId="item.id" class="md:hidden mb-2" />
                    <SaleDesktopDetail :salesId="client.sales.map(item => item.id)" class="hidden md:block mb-2" />
                    <!-- <el-empty v-else description="No hay ventas de este cliente" /> -->
                </el-tab-pane>
            </el-tabs>
        </div>

        <!-- -------------- Payment Modal starts----------------------- -->
        <Modal :show="paymentModal" @close="paymentModal = false">
        <div class="p-4 relative">
          <i @click="paymentModal = false"
            class="fa-solid fa-xmark cursor-pointer w-5 h-5 rounded-full border border-black flex items-center justify-center absolute right-3"></i>

          <form class="mt-5 mb-2 md:grid grid-cols-2 gap-3" @submit.prevent="storePayment">
            <h2 class="font-bold col-span-full">Registrar abono</h2>
            
            <div>
                <el-select v-model="form.client_id" clearable filterable disabled
                    placeholder="Seleccione" no-data-text="No hay opciones registradas"
                    no-match-text="No se encontraron coincidencias">
                    <el-option v-for="client in clients" :key="client" :label="client.name" :value="client.id" />
                </el-select>
            </div>

            <div class="flex space-x-3 items-center mt-2 md:-mt-4">
                <div>
                    <p class="text-gray-500 border-b border-primary pb-1 mb-1">Saldo pendiente</p>
                    <p>${{ totalDebt?.replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
                </div>
                <i class="fa-solid fa-arrow-right-long text-primary px-3"></i>
                <div>
                    <p class="text-gray-500 border-b border-green-500 pb-1 mb-1">Saldo restante</p>
                    <p v-if="(totalDebt - localPaymentAmount) >= 0"> ${{ (totalDebt - localPaymentAmount)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
                    <p class="text-xs text-red-600" v-else>Cantidad mayor al saldo</p>
                </div>
            </div>

            <div class="mt-2">
                <InputLabel value="Fecha de abono*" class="ml-3 mb-1" />
                <!-- <el-date-picker v-model="form.date" type="date" placeholder="Seleccione" class="!w-full" /> -->
                <input class="input !rounded-md !h-8" type="date" v-model="form.date" placeholder="Seleccione">
            </div>

            <div class="mt-2">
                <InputLabel value="Monto abonado*" class="ml-3 mb-1 text-sm" />
                <el-input v-model="localPaymentAmount" placeholder="ingresa el monto"
                    :formatter="(value) => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                    :parser="(value) => value.replace(/\$\s?|(,*)/g, '')">
                    <template #prefix>
                        <i class="fa-solid fa-dollar-sign"></i>
                    </template>
                </el-input>
            </div>

            <div class="col-span-full mt-2">
                <InputLabel value="Notas (opcional)" class="text-sm ml-2" />
                <el-input v-model="form.notes" :autosize="{ minRows: 3, maxRows: 5 }" type="textarea"
                    placeholder="Escribe tus notas" :maxlength="200" show-word-limit clearable />
            </div>

            <div class="flex justify-end space-x-3 pt-2 pb-1 py-2 col-span-full">
                <CancelButton @click="paymentModal = false; form.reset();">Cancelar</CancelButton>
                <PrimaryButton :disabled="(totalDebt - localPaymentAmount) < 0 || !localPaymentAmount || !form.date">Abonar</PrimaryButton>
            </div>
          </form>
        </div>
      </Modal>
      <!-- --------------------------- Payment Modal ends ------------------------------------ -->


        <!-- -------------- Debt Modal starts----------------------- -->
        <Modal :show="debtModal" @close="debtModal = false">
        <div class="p-4 relative">
          <i @click="debtModal = false"
            class="fa-solid fa-xmark cursor-pointer w-5 h-5 rounded-full border border-black flex items-center justify-center absolute right-3"></i>

          <form class="mt-5 mb-2 md:grid grid-cols-2 gap-3" @submit.prevent="storeDebt">
            <h2 class="font-bold col-span-full">Registrar deuda</h2>
            
            <div>
                <el-select v-model="form.client_id" clearable filterable disabled
                    placeholder="Seleccione" no-data-text="No hay opciones registradas"
                    no-match-text="No se encontraron coincidencias">
                    <el-option v-for="client in clients" :key="client" :label="client.name" :value="client.id" />
                </el-select>
            </div>

            <div class="flex space-x-3 items-center mt-2 md:-mt-4">
                <div>
                    <p class="text-gray-500 border-b border-primary pb-1 mb-1">Saldo actual</p>
                    <p>${{ totalDebt?.replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
                </div>
                <i class="fa-solid fa-arrow-right-long text-primary px-3"></i>
                <div>
                    <p class="text-gray-500 border-b border-green-500 pb-1 mb-1">Saldo resultante</p>
                    <p v-if="debtForm.total"> ${{ (parseFloat(totalDebt) + parseFloat(debtForm.total)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') }}</p>
                </div>
            </div>

            <div class="mt-2">
                <InputLabel value="Monto de deuda*" class="ml-3 mb-1 text-sm" />
                <el-input v-model="debtForm.total" placeholder="ingresa el monto"
                    :formatter="(value) => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                    :parser="(value) => value.replace(/\$\s?|(,*)/g, '')">
                    <template #prefix>
                        <i class="fa-solid fa-dollar-sign"></i>
                    </template>
                </el-input>
                <InputError :message="debtForm.errors.total" />
            </div>

            <div class="flex justify-end space-x-3 pt-2 pb-1 py-2 col-span-full">
                <CancelButton @click="debtModal = false; debtForm.reset();">Cancelar</CancelButton>
                <PrimaryButton :disabled="!debtForm.total">Registrar</PrimaryButton>
            </div>
          </form>
        </div>
      </Modal>
      <!-- --------------------------- Debt Modal ends ------------------------------------ -->
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CancelButton from "@/Components/MyComponents/CancelButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
import SaleMobileDetail from '@/Components/MyComponents/Client/SaleMobileDetail.vue';
import InputError from "@/Components/InputError.vue";
import SaleDesktopDetail from '@/Components/MyComponents/Client/SaleDesktopDetail.vue';
import Back from "@/Components/MyComponents/Back.vue";
import { useForm } from "@inertiajs/vue3";

export default {
    data() {
        const form = useForm({
            client_id: this.client?.id,
            sale_id: null,
            amount: null,
            notes: null,
            date: null,
        });

        const debtForm = useForm({
            client_id: this.client?.id,
            has_credit: true,
            total: null,
        });

        return {
            form,
            debtForm,
            currentClient: this.client.id,
            paymentModal: false,
            debtModal: false, //modal para agregar adeudo
            localPaymentAmount: null, //monto de abono local para calcular y repartir montos a ventas 
            activeTab: '1',
        }
    },
    components: {
        SaleDesktopDetail,
        SaleMobileDetail,
        PrimaryButton,
        ThirthButton,
        CancelButton,
        InputLabel,
        InputError,
        AppLayout,
        Modal,
        Back,
    },
    props: {
        client: Object,
        clients: Array,
        pendent_sales: Array,
    },
    computed: {
        reversedSales() {
            return this.client.sales.reverse();
        },
        reversedPendentSales() {
            return this.client.sales.filter(sale => sale.has_credit && sale.paid_at === null).reverse();
        },
        totalDebt() {
            return this.pendent_sales.reduce((acc, sale) => {
                const paymentsTotal = sale.payments?.reduce((sum, payment) => sum + payment.amount, 0) || 0;
                return acc + (sale.total - paymentsTotal);
            }, 0).toFixed(2);
        }
    },
    methods:{
        storePayment() {
            this.pendent_sales.forEach(sale => {

                const totalPayments = sale.payments?.reduce((sum, payment) => sum + payment.amount, 0) || 0;
                const saleBalance = sale.total - totalPayments; // Saldo de la venta actual


                if (this.localPaymentAmount >= saleBalance) {

                    // Si el abono cubre el saldo, marca la venta como pagada
                    this.form.sale_id = sale.id;
                    this.form.amount = saleBalance;
                    this.form.post(route('payments.store'));

                    this.localPaymentAmount -= saleBalance;
                    if (this.localPaymentAmount == 0) {
                        location.reload();
                    }
                } else {
                    // Si el abono es menor que el saldo, aplicar solo la cantidad restante
                    this.form.sale_id = sale.id;
                    this.form.amount = this.localPaymentAmount;
                    this.form.post(route('payments.store') , {
                        onSuccess: () => {
                            this.$notify({
                                title: "Correcto",
                                message: "Se ha registrado el abono",
                                type: "success",
                            });
                            location.reload();
                        }
                    });
                    this.localPaymentAmount = 0;
                } 
            });
        },
        storeDebt() {
            this.debtForm.post(route('clients.store-debt') , {
                onSuccess: () => {
                    this.$notify({
                        title: "Correcto",
                        message: "Se ha registrado la deuda",
                        type: "success",
                    });
                    location.reload();
                }
            });
        }
    }
}
</script>

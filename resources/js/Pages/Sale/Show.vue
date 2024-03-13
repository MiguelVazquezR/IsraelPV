<template>
    <AppLayout :title="'Venta' + sale.data.folio">
        <div class="px-10 py-7">
            <Back />

            <!-- Información de la venta -->
            <div class="mt-7 lg:mx-16">
                <p class="font-bold">Folio: <span class="!font-thin ml-2 text-gray-600">{{ sale.data.folio }}</span></p>
                <p class="font-bold">Fecha de venta: <span class="!font-thin ml-2 text-gray-600">{{ sale.data.created_at }}</span></p>
                <p class="font-bold">Cliente: <span class="!font-thin ml-2 text-gray-600">{{ sale.data.client?.name ?? '-' }}</span></p>
                <p class="font-bold">Método de pago: <span class="!font-thin ml-2 text-gray-600">{{ sale.data.has_credit ? 'A crédito' : 'Al contado'}}</span></p>
                <p class="font-bold">Total de venta: <span class="!font-thin ml-2 text-gray-600">${{ sale.data.total?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span></p>
                <p class="font-bold">Abonos: <span class="!font-thin ml-2 text-gray-600">${{ totalPaymentAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span></p>
                <p class="font-bold">Saldo restante: <span class="!font-thin ml-2 text-gray-600">${{ sale.data.total - totalPaymentAmount }}</span></p>
            </div>

            <!-- Productos -->
            <section class="mt-7 grid grid-cols-2">
                <!-- detalle de productos -->
                <div :class="sale.data.has_credit ? 'border-r' : '' " class="grid grid-cols-3 lg:ml-16">
                    <p class="font-bold">Producto</p>
                    <p class="font-bold">Cantidad</p>
                    <p class="font-bold ml-8">Total</p>

                    <div class="mt-2">
                        <p @click="$inertia.get(route('products.show', product.id))" class="text-primary underline cursor-pointer inline-block text-sm" v-for="product in sale.data.products" :key="product">{{ product.name }}</p>
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
                <div class="lg:ml-8">
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
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Back from "@/Components/MyComponents/Back.vue";

export default {
data() {
    return {

    }
},
components:{
AppLayout,
Back
},
props:{
sale: Object,
},
methods:{

},
computed: {
    totalPaymentAmount() {
        return this.sale.data.payments.reduce((total, payment) => total + payment.amount, 0);
    }
}
}
</script>
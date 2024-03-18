<template>
    <div class="border border-grayD9 rounded-[20px] py-2 px-4 min-h-32">
        <Loading v-if="loading" class="pt-6" />
        <Link :href="route('sales.show', saleId)" v-else>
        <header class="flex items-center justify-between">
            <h1 class="text-gray99"><b class="mr-1">Folio</b>{{ 'V-' + String(saleId).padStart(4, '0') }}</h1>
        </header>
        <main v-if="sale" class="text-gray99 mt-1">
            <div class="flex items-center space-x-1">
                <p>Creado el: <span class="text-black">{{ format(sale.created_at, true) }}</span></p>
                <i v-if="sale.has_credit" class="fa-solid fa-minus"></i>
                <p v-if="sale.has_credit">Crédito vence el: <span class="text-black">{{ format(sale.limit_date) }}</span></p>
            </div>
            <div v-if="sale.has_credit">
                <p>Total: <span class="text-black">${{ getTotal.toLocaleString('en-US', {minimumFractionDigits: 2}) }}</span></p>
                <p>Abonado: <span class="text-black">${{ getTotalPaid.toLocaleString('en-US', {minimumFractionDigits: 2}) }}</span></p>
                <p class="font-bold">Saldo pendinte: <span class="text-black">${{ (getTotal - getTotalPaid).toLocaleString('en-US', {minimumFractionDigits: 2}) }}</span></p>
            </div>
            <div class="mt-2">
                <p>Productos:</p>
                <ul class="text-primary">
                    •
                    <Link :href="route('products.show', item)" v-for="(item, index) in sale.products" :key="item.id"
                        class="underline">
                    {{ item.name }}
                    </Link>
                </ul>
            </div>
        </main>
        </Link>
    </div>
</template>
<script>
import { Link } from "@inertiajs/vue3";
import axios from 'axios';
import Loading from '../Loading.vue';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

export default {
    data() {
        return {
            loading: true,
            sale: null,
        }
    },
    props: {
        saleId: Number,
    },
    components: {
        Loading,
        Link,
    },
    computed: {
        getTotal() {
            return this.sale.products.reduce((total, product) => {
                return total + (product.pivot.quantity * product.pivot.price);
            }, 0);
        },
        getTotalPaid() {
            return this.sale.payments.reduce((total, payment) => {
                return total + payment.amount;
            }, 0);
        },
    },
    methods: {
        format(date, dateTime = false) {
            if (dateTime) {
                return format(new Date(date), 'd MMM yyyy, hh:mm a', { locale: es });
            } else {
                return format(new Date(date), 'd MMM yyyy', { locale: es });
            }
        },
        async fetchSale() {
            try {
                const response = await axios.get(route('sales.get-by-id', this.saleId));

                if (response.status === 200) {
                    this.sale = response.data.item;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        }
    },
    async mounted() {
        await this.fetchSale();
    }
}
</script>
<style scoped>
.component-crop {
    clip-path: inset(0 round 20px);
    /* Recorta el componente con redondeo en las esquinas */
}
</style>
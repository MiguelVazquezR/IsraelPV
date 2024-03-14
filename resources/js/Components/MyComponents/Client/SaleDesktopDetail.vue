<template>
    <div class="border border-grayD9 rounded-full py-2 px-4 hover:bg-primarylight text-xs">
        <Loading v-if="loading" />
        <Link :href="route('sales.show', saleId)" v-else>
        <main class="flex items-center space-x-2 h-8">
            <span class="w-[11%] truncate">{{ 'V-' + String(saleId).padStart(4, '0') }}</span>
            <span class="w-[11%] truncate">{{ format(sale.created_at, true) }}</span>
            <span class="w-[11%] truncate">{{ sale.has_credit ? 'A crédito' : 'Al contado' }}</span>
            <span class="w-[11%] truncate">{{ sale.limit_date ? format(sale.limit_date) : '-' }}</span>
            <span class="w-[11%] truncate">{{ sale.products.length }}</span>
            <span class="w-[11%] truncate">${{ getTotal.toLocaleString('en-US', {minimumFractionDigits: 2}) }}</span>
            <span class="w-[11%] truncate">{{ sale.has_credit ? '$' + getTotalPaid.toLocaleString('en-US', {minimumFractionDigits: 2}) : '-' }}</span>
            <span class="w-[11%] truncate">{{ sale.has_credit ? '$' + (getTotal - getTotalPaid).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-' }}</span>
            <span class="w-[11%] truncate" :class="getStatus == 'Pagado' ? 'text-[#5FCB1F]' : 'text-[#1761B7]'">{{ getStatus }}</span>
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
        getStatus() {

            if ( (this.getTotal - this.getTotalPaid) > 0 && this.sale.has_credit ) return 'Pendiente';
            else return 'Pagado';
        }
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
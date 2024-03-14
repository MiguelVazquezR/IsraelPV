<template>
    <div v-if="loading" class="flex justify-center items-center">
        <Loading />
    </div>
    <div v-else>
        <table>
            <thead>
                <tr class="*:text-start *:pb-3 *:px-4">
                    <th># Venta</th>
                    <th>Fecha de venta</th>
                    <th>Modo de pago</th>
                    <th>Vence el</th>
                    <th>Productos</th>
                    <th>Total</th>
                    <th>Saldo abonado</th>
                    <th>Saldo pendiente</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(sale, index) in sales" :key="index" @click="$inertia.visit(route('sales.show', sale))"
                    class="*:py-2 *:px-4 hover:bg-primarylight cursor-pointer text-xs">
                    <td class="w-[11%] truncate rounded-s-lg">{{ 'V-' + String(sale.id).padStart(4, '0') }}</td>
                    <td class="w-[11%] truncate">{{ format(sale.created_at, true) }}</td>
                    <td class="w-[11%] truncate">{{ sale.has_credit ? 'A crédito' : 'Al contado' }}</td>
                    <td class="w-[11%] truncate">{{ sale.limit_date ? format(sale.limit_date) : '-' }}</td>
                    <td class="w-[11%] truncate">{{ sale.products.length }}</td>
                    <td class="w-[11%] truncate">${{ getTotal(index).toLocaleString('en-US', { minimumFractionDigits: 2 })
                        }}</td>
                    <td class="w-[11%] truncate">{{ sale.has_credit ? '$' + getTotalPaid(index).toLocaleString('en-US',
        { minimumFractionDigits: 2 }) : '-' }}</td>
                    <td class="w-[11%] truncate">{{ sale.has_credit ? '$' + (getTotal(index) -
        getTotalPaid(index)).toLocaleString('en-US', { minimumFractionDigits: 2 }) : '-' }}</td>
                    <td class="w-[11%] truncate rounded-e-lg" :class="getStatus(index) == 'Pagado' ? 'text-[#5FCB1F]' : 'text-red-600'">{{
                        getStatus(index) }}</td>
                </tr>
            </tbody>
        </table>
        <!-- <main class="flex items-center space-x-2 h-8">
            <span class="w-[11%] truncate">{{ 'V-' + String(saleId).padStart(4, '0') }}</span>
            <span class="w-[11%] truncate">{{ format(sale.created_at, true) }}</span>
            <span class="w-[11%] truncate">{{ sale.has_credit ? 'A crédito' : 'Al contado' }}</span>
            <span class="w-[11%] truncate">{{ sale.limit_date ? format(sale.limit_date) : '-' }}</span>
            <span class="w-[11%] truncate">{{ sale.products.length }}</span>
            <span class="w-[11%] truncate">${{ getTotal.toLocaleString('en-US', {minimumFractionDigits: 2}) }}</span>
            <span class="w-[11%] truncate">{{ sale.has_credit ? '$' + getTotalPaid.toLocaleString('en-US', {minimumFractionDigits: 2}) : '-' }}</span>
            <span class="w-[11%] truncate">{{ sale.has_credit ? '$' + (getTotal - getTotalPaid).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-' }}</span>
            <span class="w-[11%] truncate" :class="getStatus == 'Pagado' ? 'text-[#5FCB1F]' : 'text-red-600'">{{ getStatus }}</span>
        </main> -->
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
            sales: null,
        }
    },
    props: {
        salesId: Array,
    },
    components: {
        Loading,
        Link,
    },
    computed: {

    },
    methods: {
        getTotal(index) {
            return this.sales[index].products.reduce((total, product) => {
                return total + (product.pivot.quantity * product.pivot.price);
            }, 0);
        },
        getTotalPaid(index) {
            return this.sales[index].payments.reduce((total, payment) => {
                return total + payment.amount;
            }, 0);
        },
        getStatus(index) {
            if ((this.getTotal(index) - this.getTotalPaid(index)) > 0 && this.sales[index].has_credit) return 'Pendiente';
            else return 'Pagado';
        },
        format(date, dateTime = false) {
            if (dateTime) {
                return format(new Date(date), 'd MMM yyyy, hh:mm a', { locale: es });
            } else {
                return format(new Date(date), 'd MMM yyyy', { locale: es });
            }
        },
        async fetchSales() {
            try {
                const response = await axios.post(route('sales.get-by-ids'), { ids: this.salesId });

                if (response.status === 200) {
                    this.sales = response.data.items;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        }
    },
    async mounted() {
        await this.fetchSales();
    }
}
</script>
<style scoped>
.component-crop {
    clip-path: inset(0 round 20px);
    /* Recorta el componente con redondeo en las esquinas */
}
</style>
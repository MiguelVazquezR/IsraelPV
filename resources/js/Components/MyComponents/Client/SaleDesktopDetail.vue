<script setup>
import { router } from "@inertiajs/vue3";
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

// AHORA RECIBIMOS EL ARRAY DE VENTAS DIRECTAMENTE. NADA DE AXIOS.
const props = defineProps({
    sales: {
        type: Array,
        required: true
    },
});

const formatDate = (date, dateTime = false) => {
    if (!date) return '-';
    if (dateTime) {
        return format(new Date(date), 'd MMM yy, HH:mm', { locale: es });
    } else {
        return format(new Date(date), 'd MMM yyyy', { locale: es });
    }
};

const getTotal = (sale) => {
    if (sale.total) return sale.total; // Si el backend ya lo manda calculado
    return sale.products?.reduce((sum, p) => sum + (p.pivot.quantity * p.pivot.price), 0) || 0;
};

const getTotalPaid = (sale) => {
    return sale.payments?.reduce((sum, p) => sum + p.amount, 0) || 0;
};

const getStatus = (sale) => {
    if (!sale.has_credit) return 'Contado';
    const debt = getTotal(sale) - getTotalPaid(sale);
    return debt > 0.1 ? 'Pendiente' : 'Pagado'; // Umbral 0.1 para flotantes
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 font-medium">Folio</th>
                    <th class="px-4 py-3 font-medium">Fecha</th>
                    <th class="px-4 py-3 font-medium">Tipo</th>
                    <th class="px-4 py-3 font-medium text-center">Items</th>
                    <th class="px-4 py-3 font-medium text-right">Total</th>
                    <th class="px-4 py-3 font-medium text-right">Abonado</th>
                    <th class="px-4 py-3 font-medium text-right">Pendiente</th>
                    <th class="px-4 py-3 font-medium text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <tr 
                    v-for="sale in sales" 
                    :key="sale.id" 
                    @click="router.visit(route('sales.show', sale.id))"
                    class="bg-white hover:bg-gray-50 transition-colors cursor-pointer group"
                >
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">
                        V-{{ String(sale.id).padStart(4, '0') }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ formatDate(sale.created_at, true) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium" :class="sale.has_credit ? 'bg-orange-100 text-orange-700' : 'bg-blue-50 text-blue-700'">
                            {{ sale.has_credit ? 'Crédito' : 'Contado' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">
                        {{ sale.products?.length || 0 }}
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-gray-900">
                        ${{ getTotal(sale).toLocaleString('en-US', {minimumFractionDigits: 2}) }}
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">
                        {{ sale.has_credit ? '$' + getTotalPaid(sale).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-' }}
                    </td>
                    <td class="px-4 py-3 text-right font-bold" :class="(getTotal(sale) - getTotalPaid(sale)) > 0.1 ? 'text-red-600' : 'text-green-600'">
                        {{ sale.has_credit ? '$' + (getTotal(sale) - getTotalPaid(sale)).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span 
                            class="text-xs font-bold"
                            :class="getStatus(sale) === 'Pagado' || getStatus(sale) === 'Contado' ? 'text-green-600' : 'text-orange-500'"
                        >
                            {{ getStatus(sale) }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
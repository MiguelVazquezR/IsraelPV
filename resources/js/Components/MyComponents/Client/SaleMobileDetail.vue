<script setup>
import { Link } from "@inertiajs/vue3";
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

// AHORA RECIBIMOS EL OBJETO VENTA COMPLETO
const props = defineProps({
    sale: {
        type: Object,
        required: true
    },
});

const formatDate = (date, dateTime = false) => {
    if (!date) return '-';
    if (dateTime) {
        return format(new Date(date), 'd MMM, HH:mm', { locale: es });
    } else {
        return format(new Date(date), 'd MMM yyyy', { locale: es });
    }
};

const getTotal = () => {
    return props.sale.products?.reduce((sum, p) => sum + (p.pivot.quantity * p.pivot.price), 0) || 0;
};

const getTotalPaid = () => {
    return props.sale.payments?.reduce((sum, p) => sum + p.amount, 0) || 0;
};

const total = getTotal();
const paid = getTotalPaid();
const pending = total - paid;
</script>

<template>
    <Link :href="route('sales.show', sale.id)" class="block">
        <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow relative overflow-hidden">
            <!-- Banda lateral de estado -->
            <div 
                class="absolute left-0 top-0 bottom-0 w-1" 
                :class="pending > 0.1 && sale.has_credit ? 'bg-orange-400' : 'bg-green-500'"
            ></div>

            <div class="pl-2">
                <header class="flex items-center justify-between mb-2">
                    <span class="text-xs font-mono text-gray-400 uppercase">V-{{ String(sale.id).padStart(4, '0') }}</span>
                    <span class="text-xs text-gray-500">{{ formatDate(sale.created_at, true) }}</span>
                </header>

                <div class="flex justify-between items-end mb-3">
                    <div>
                        <p class="text-sm font-bold text-gray-900">
                            ${{ total.toLocaleString('en-US', {minimumFractionDigits: 2}) }}
                        </p>
                        <p class="text-[10px] text-gray-500 uppercase tracking-wide">
                            {{ sale.has_credit ? 'Crédito' : 'Contado' }}
                        </p>
                    </div>

                    <div v-if="sale.has_credit" class="text-right">
                        <p class="text-xs text-gray-500">Pendiente</p>
                        <p class="text-sm font-bold" :class="pending > 0.1 ? 'text-red-500' : 'text-green-500'">
                            ${{ pending.toLocaleString('en-US', {minimumFractionDigits: 2}) }}
                        </p>
                    </div>
                </div>
                
                <!-- Lista resumida de productos -->
                <div class="pt-2 border-t border-gray-50">
                    <p class="text-[10px] text-gray-400 mb-1">Items ({{ sale.products?.length || 0 }})</p>
                    <div class="flex flex-wrap gap-1">
                        <span 
                            v-for="prod in sale.products?.slice(0, 3)" 
                            :key="prod.id"
                            class="text-[10px] bg-gray-50 text-gray-600 px-1.5 py-0.5 rounded truncate max-w-[100px]"
                        >
                            {{ prod.name }}
                        </span>
                        <span v-if="sale.products?.length > 3" class="text-[10px] text-gray-400 px-1">+{{ sale.products.length - 3 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>
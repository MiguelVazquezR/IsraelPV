<script setup>
import { Link, router } from "@inertiajs/vue3";
import { computed } from 'vue';
import axios from 'axios';
import { ElNotification } from 'element-plus';

// Ahora recibimos el objeto CLIENTE completo. Ya no hacemos fetch aquí.
const props = defineProps({
    client: Object,
});

const hasDebt = computed(() => Number(props.client.debt || 0) > 0);

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const deleteItem = async (clientId) => {
    try {
        const response = await axios.delete(route('clients.destroy', clientId));
        if (response.status == 200) {
            // Recargamos la página o emitimos evento para actualizar lista
            // En este caso simple, recargar es seguro o podrías emitir al padre
            router.reload({ only: ['clients'] }); 
            ElNotification({
                title: 'Correcto',
                message: 'Se ha eliminado el cliente',
                type: 'success',
            });
        }
    } catch (error) {
        console.log(error);
        ElNotification({
            title: 'Error',
            message: 'No se pudo eliminar el cliente.',
            type: 'error',
        });
    }
}
</script>

<template>
    <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-4 relative overflow-hidden transition-all hover:shadow-md">
        <!-- Banda de color lateral (opcional, decorativo) -->
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary"></div>

        <!-- Encabezado Tarjeta -->
        <div class="flex justify-between items-start mb-3 pl-2">
            <div class="flex flex-col">
                <span class="text-[14px] text-gray-400 font-mono uppercase tracking-widest">ID #{{ String(client.id).padStart(3, '0') }}</span>
                <Link :href="route('clients.show', client.id)" class="text-base font-bold text-gray-800 leading-tight active:text-primary">
                    {{ client.name }}
                </Link>
            </div>
            
            <!-- Acciones -->
            <div class="flex items-center gap-2">
                <button 
                    @click="router.visit(route('clients.edit', client.id))"
                    class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                >
                    <i class="fa-solid fa-pencil text-xs"></i>
                </button>
                
                <el-popconfirm 
                    confirm-button-text="Sí" 
                    cancel-button-text="No" 
                    icon-color="#C30303"
                    title="¿Eliminar?" 
                    @confirm="deleteItem(client.id)"
                >
                    <template #reference>
                        <button class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                            <i class="fa-regular fa-trash-can text-xs"></i>
                        </button>
                    </template>
                </el-popconfirm>
            </div>
        </div>

        <!-- Cuerpo Info -->
        <Link :href="route('clients.show', client.id)" class="space-y-2 pl-2 block">
            <div class="flex items-center text-sm text-gray-600">
                <div class="w-6 flex justify-center shrink-0">
                    <i class="fa-solid fa-phone text-xs text-gray-400"></i>
                </div>
                <span>{{ client.phone }}</span>
            </div>

            <div class="flex items-start text-sm text-gray-600">
                <div class="w-6 flex justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-location-dot text-xs text-gray-400"></i>
                </div>
                <span class="line-clamp-2 leading-tight">{{ client.address ?? 'Sin dirección' }}</span>
            </div>

            <div class="flex items-center text-sm text-gray-600">
                <div class="w-6 flex justify-center shrink-0">
                    <i class="fa-solid fa-file-invoice text-xs text-gray-400"></i>
                </div>
                <span>{{ client.rfc ?? 'Sin RFC' }}</span>
            </div>
        </Link>

        <!-- Deuda -->
        <div class="flex items-center justify-between pl-2 mt-3 pt-2 border-t border-gray-100">
            <span class="text-[11px] text-gray-400 font-semibold uppercase tracking-widest">Deuda</span>
            <span
                class="px-2.5 py-1 rounded-full text-xs font-bold"
                :class="hasDebt ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600'"
            >
                {{ hasDebt ? '$' + formatMoney(client.debt) : 'Sin deuda' }}
            </span>
        </div>
    </div>
</template>
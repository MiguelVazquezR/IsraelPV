<script setup>
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ElNotification } from 'element-plus';

const props = defineProps({
    clients: Array
});

const deleteItem = async (clientId) => {
    try {
        const response = await axios.delete(route('clients.destroy', clientId));
        if (response.status == 200) {
            // Actualización local para sentirlo instantáneo
            const index = props.clients.findIndex(c => c.id === clientId);
            if (index !== -1) props.clients.splice(index, 1);
            
            ElNotification({
                title: 'Correcto',
                message: 'Cliente eliminado',
                type: 'success',
            });
        }
    } catch (error) {
        console.log(error);
        ElNotification({ title: 'Error', message: 'No se pudo eliminar', type: 'error' });
    }
};

const editItem = (id) => {
    router.visit(route('clients.edit', id));
};

const showItem = (id) => {
    router.visit(route('clients.show', id));
};
</script>

<template>
    <div v-if="clients.length" class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3 font-medium tracking-wider w-[10%]">ID</th>
                    <th scope="col" class="px-6 py-3 font-medium tracking-wider w-[25%]">Nombre</th>
                    <th scope="col" class="px-6 py-3 font-medium tracking-wider w-[30%]">Dirección</th>
                    <th scope="col" class="px-6 py-3 font-medium tracking-wider w-[15%]">Teléfono</th>
                    <th scope="col" class="px-6 py-3 font-medium tracking-wider w-[10%]">RFC</th>
                    <th scope="col" class="px-6 py-3 font-medium tracking-wider text-right w-[10%]">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <tr 
                    v-for="client in clients" 
                    :key="client.id"
                    @click="showItem(client.id)"
                    class="bg-white hover:bg-gray-50 transition-colors cursor-pointer group"
                >
                    <td class="px-6 py-4 font-mono text-gray-500 text-xs">
                        {{ String(client.id).padStart(3, '0') }}
                    </td>
                    <td class="px-6 py-4 font-medium text-gray-900 group-hover:text-primary transition-colors">
                        {{ client.name }}
                    </td>
                    <td class="px-6 py-4 text-gray-500 truncate max-w-xs">
                        {{ client.address ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                        {{ client.phone }}
                    </td>
                    <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                        {{ client.rfc ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end items-center gap-2" @click.stop>
                            <button 
                                @click="editItem(client.id)"
                                class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all"
                                title="Editar"
                            >
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            
                            <el-popconfirm 
                                confirm-button-text="Sí" 
                                cancel-button-text="No" 
                                icon-color="#C30303"
                                title="¿Eliminar?" 
                                @confirm="deleteItem(client.id)"
                            >
                                <template #reference>
                                    <button 
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all"
                                        title="Eliminar"
                                    >
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </template>
                            </el-popconfirm>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<template>
    <div class="w-full mx-auto text-[11px] md:text-sm overflow-auto">
        <div class="text-center md:text-base flex items-center space-x-4 mb-2">
            <div class="font-bold w-[10%]">ID</div>
            <div class="font-bold pb-3 pl-2 text-left w-[18%] md:w-[13%]">Nombre</div>
            <div class="font-bold pb-3 text-left w-[35%] md:w-[30%]">Dirección</div>
            <div class="font-bold pb-3 text-left w-[10%]">Teléfono</div>
            <div class="font-bold pb-3 text-left w-[10%]">RFC</div>
            <div class="font-bold pb-3 text-left w-[10%]">Saldo pendiente</div>
            <div class="w-[17%]"></div>
        </div>
        <div>
            <div v-for="client in clients" :key="client.id"
                class="*:p-3 h-12 cursor-pointer flex items-center space-x-4 border rounded-full mb-2 hover:border-primarylight"
                @click="$inertia.get(route('clients.show', client.id))">
                <div class="md:block w-[10%] md:w-[10%] text-center rounded-l-full">{{ client.id }}</div>
                <div class="w-[18%] md:w-[13%]">{{ client.name }}</div>
                <div class="w-[20%] md:w-[30%]">{{ client.address }}</div>
                <div class="w-[10%]">{{ client.phone }}</div>
                <div class="w-[10%]">{{ client.rfc }}</div>
                <div class="w-[10%]">{{ 'client.saldo' }}</div>
                <div class="rounded-r-full w-[17%] text-center">
                    <i @click.stop="$inertia.get(route('clients.edit', client.id))"
                        class="fa-solid fa-pencil text-primary cursor-pointer hover:bg-gray-200 rounded-full mr-1 p-2"></i>
                    <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#C30303"
                        title="¿Continuar?" @confirm="deleteItem(client.id)">
                        <template #reference>
                            <i @click.stop
                                class="fa-regular fa-trash-can text-primary cursor-pointer hover:bg-gray-200 rounded-full p-2"></i>
                        </template>
                    </el-popconfirm>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
        };
    },
    components: {
    },
    props: {
        clients: Object
    },
    methods: {
        async deleteItem(clientId) {
            try {
                const response = await axios.delete(route('clients.destroy', clientId));
                if (response.status == 200) {
                    const indexToDelete = this.clients.findIndex(item => item.id == clientId);
                    this.clients.splice(indexToDelete, 1);

                    this.$notify({
                        title: 'Success',
                        message: 'Se ha eliminado el cliente',
                        type: 'success',
                    });
                }
            } catch (error) {
                console.log(error);
                this.$notify({
                    title: 'Error',
                    message: 'No se pudo eliminar el cliente. Inténte más tarde',
                    type: 'error',
                });
            }
        }
    }
}
</script>

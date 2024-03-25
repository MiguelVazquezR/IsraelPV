?? '-'<template>
    <div>
        <div class="border border-grayD9 rounded-[20px] py-2 px-4 min-h-32 text-xs mb-2">
            <Loading v-if="loading" class="pt-4" />
            <div v-else>
                <header class="flex items-center justify-between">
                    <h1 class="text-gray99"><b class="mr-1">ID</b>{{ String(client.id).padStart(3, '0') }}</h1>
                    <div class="flex items-center space-x-1">
                        <button class="flex items-center justify-center text-primary bg-grayED rounded-full size-6">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                            </svg>
                        </button>
                        <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#D37E2D"
                            title="¿Continuar?" @confirm="deleteItem(client.id)">
                            <template #reference>
                                <button
                                    class="flex items-center justify-center text-primary bg-grayED rounded-full size-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </template>
                        </el-popconfirm>
                    </div>
                </header>
                <Link :href="route('clients.show', clientId)">
                <main class="text-gray99 mt-1 *:flex *:items-center *:space-x-1">
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span>Nombre </span>

                        <span class="text-gray37">{{ client.name }}</span>
                    </p>
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                        <span>Teléfono </span>

                        <span class="text-gray37">{{ client.phone }}</span>
                    </p>
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span>Dirección </span>

                        <span class="text-gray37">{{ client.address ?? '-' }}</span>
                    </p>
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5" />
                        </svg>
                        <span>Rfc </span>

                        <span class="text-gray37">{{ client.rfc ?? '-' }}</span>
                    </p>
                </main>
                <footer class="mt-2 mx-5">
                    <p class="text-gray99">
                        Saldo pendiente: ${{ pendentAmount.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </p>
                </footer>
                </Link>
            </div>
        </div>
    </div>
</template>

<script>
import Loading from "@/Components/MyComponents/Loading.vue";
import { Link } from "@inertiajs/vue3";
import axios from 'axios';

export default {
    data() {
        return {
            client: null,
            pendentAmount: 0,
            loading: true,
        };
    },
    components: {
        Link,
        Loading,
    },
    props: {
        clientId: Number,
    },
    methods: {
        async fetchClient() {
            try {
                const response = await axios.get(route('clients.get-by-id', this.clientId));

                if (response.status === 200) {
                    this.client = response.data.item;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        },
        async fetchClientPendentAmount() {
            this.loading = true;
            try {
                const response = await axios.get(route('clients.get-pendent-amount', this.clientId));
                if (response.status === 200) {
                    this.pendentAmount = response.data.item;
                }
            } catch (error) {
                console.log(error);
                this.$notify({
                    title: 'Error',
                    message: 'No se pudo calcular el saldo pendiente del cliente',
                    type: 'error',
                });
            } finally {
                this.loading = false;
            }
        },
        async deleteItem(clientId) {
            try {
                const response = await axios.delete(route('clients.destroy', clientId));
                if (response.status == 200) {
                    const indexToDelete = this.clients.findIndex(item => item.id == clientId);
                    this.clients.splice(indexToDelete, 1);

                    this.$notify({
                        title: 'Correcto',
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
    },
    async mounted() {
        await this.fetchClient();
        await this.fetchClientPendentAmount();
    }
}
</script>

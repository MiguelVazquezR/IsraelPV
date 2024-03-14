<template>
    <AppLayout :title="client.name">
        <div class="px-2 lg:px-10 py-7 text-xs lg:text-sm">
            <Back />

            <!-- header botones -->
            <div class="lg:flex justify-between items-center mx-3 mt-4">
                <h1 class="font-bold text-lg">Detalles del cliente</h1>
                <div class="my-4 lg:my-0 flex items-center space-x-3">
                    <!-- <ThirthButton @click="openEntryModal" class="!rounded-full">Registrar abono</ThirthButton> -->
                    <PrimaryButton @click="$inertia.get(route('clients.edit', client.id))" class="!rounded-full">Editar
                    </PrimaryButton>
                </div>
            </div>

            <div class="lg:w-1/4 relative">
                <el-select v-model="currentClient" clearable filterable placeholder="Seleccione"
                    no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                    <el-option @click="$inertia.get(route('clients.show', client.id))" v-for="client in clients"
                        :key="client" :label="client.name" :value="client.id" />
                </el-select>
            </div>

            <!-- informacion del cliente -->
            <div class="grid grid-cols-3 lg:grid-cols-12 gap-1 my-7 ml-4">
                <p class="font-bold">Nombre</p>
                <p class="col-span-2 lg:col-span-11">{{ client.name }}</p>
                <p class="font-bold">RFC</p>
                <p class="col-span-2 lg:col-span-11">{{ client.rfc ?? '-' }}</p>
                <p class="font-bold">Teléfono</p>
                <p class="col-span-2 lg:col-span-11">{{ client.phone }}</p>
                <p class="font-bold">Dirección</p>
                <p class="col-span-2 lg:col-span-11">{{ client.address ?? '-' }}</p>
            </div>

            <el-tabs v-model="activeTab">
                <el-tab-pane label="Todas las ventas" name="1">
                    <SaleMobileDetail v-for="item in client.sales" :key="item.id" :saleId="item.id"
                        class="lg:hidden mb-2" />
                    <SaleDesktopDetail :salesId="client.sales.map(item => item.id)" class="hidden lg:block mb-2" />
                </el-tab-pane>
                <el-tab-pane label="Ventas sin liquidar" name="2">
                    <SaleMobileDetail
                        v-for="item in client.sales.filter(sale => sale.has_credit && sale.paid_at === null)"
                        :key="item.id" :saleId="item.id" class="lg:hidden mb-2" />
                    <SaleDesktopDetail
                        :salesId="client.sales.filter(sale => sale.has_credit && sale.paid_at === null).map(item => item.id)"
                        class="hidden lg:block mb-2" />
                </el-tab-pane>
            </el-tabs>

        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
import SaleMobileDetail from '@/Components/MyComponents/Client/SaleMobileDetail.vue';
import SaleDesktopDetail from '@/Components/MyComponents/Client/SaleDesktopDetail.vue';
import Back from "@/Components/MyComponents/Back.vue";

export default {
    data() {
        return {
            currentClient: this.client.id,
            activeTab: '1',
        }
    },
    components: {
        AppLayout,
        PrimaryButton,
        ThirthButton,
        Back,
        SaleMobileDetail,
        SaleDesktopDetail,
    },
    props: {
        client: Object,
        clients: Array,
    },
    methods: {

    }
}
</script>

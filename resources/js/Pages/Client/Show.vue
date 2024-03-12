<template>
    <AppLayout :title="client.name">
        <div class="px-2 lg:px-10 py-7">
            <Back :route="'clients.index'" />

            <!-- header botones -->
            <div class="lg:flex justify-between items-center mx-3">
                <h1 class="font-bold text-lg">Detalles del cliente</h1>
                <div class="my-4 lg:my-0 flex items-center space-x-3">
                    <ThirthButton @click="openEntryModal" class="!rounded-full">Registrar abono</ThirthButton>
                    <PrimaryButton @click="$inertia.get(route('clients.edit', client.id))" class="!rounded-full">Editar</PrimaryButton>
                </div>
            </div>

            <div class="lg:w-1/4 relative">
                <input v-model="searchQuery" @keydown.enter="searchProducts" class="input w-full pl-9"
                    placeholder="Buscar cliente" type="text">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            </div>

            <!-- informacion del cliente -->
            <div class="flex mt-7 ml-4">
                <div class="w-32 space-y-2">
                    <p class="font-bold">Nombre</p>
                    <p class="font-bold">RFC</p>
                    <p class="font-bold">Teléfono</p>
                    <p class="font-bold">Dirección</p>
                </div>
                <div class="space-y-2">
                    <p>{{ client.name }}</p>
                    <p>{{ client.rfc }}</p>
                    <p>{{ client.phone }}</p>
                    <p>{{ client.address }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
import Back from "@/Components/MyComponents/Back.vue";

export default {
data() {
    return{

    }
},
components:{
AppLayout,
PrimaryButton,
ThirthButton,
Back
},
props:{
client: Object
},
methods:{

}
}
</script>

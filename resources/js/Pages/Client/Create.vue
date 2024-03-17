<template>
    <AppLayout title="Nuevo cliente">
        <div class="md:px-10 px-2 py-7">
            <Back :route="'clients.index'" />

            <form @submit.prevent="store" class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-1/2 mx-auto mt-7 lg:grid lg:grid-cols-2 gap-x-3">
                <h1 class="font-bold ml-2 col-span-full">Nuevo cliente</h1>
                <div class="mt-3">
                    <InputLabel value="Nombre*" class="ml-3 mb-1" />
                    <el-input v-model="form.name" placeholder="Escribe el nombre del cliente" :maxlength="100" clearable />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="mt-3">
                    <InputLabel value="RFC" class="ml-3 mb-1" />
                    <el-input v-model="form.rfc" placeholder="Escribe el RFC en caso de tenerlo" :maxlength="100" clearable />
                    <InputError :message="form.errors.rfc" />
                </div>
                <div class="mt-3">
                    <InputLabel value="Teléfono*" class="ml-3 mb-1" />
                    <el-input v-model="form.phone" placeholder="Ingresa el número de teléfono" :maxlength="100" clearable />
                    <InputError :message="form.errors.phone" />
                </div>
                <div class="mt-3">
                    <InputLabel value="Dirección" class="ml-3 mb-1" />
                    <el-input v-model="form.address" placeholder="Escribe la dirección" :maxlength="100" clearable />
                    <InputError :message="form.errors.address" />
                </div>
                <div class="col-span-2 text-right mt-8">
                    <PrimaryButton class="!rounded-full" :disabled="form.processing">Guardar cliente</PrimaryButton>
                </div>
            </form>
        </div>

    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Back from "@/Components/MyComponents/Back.vue";
import { useForm } from "@inertiajs/vue3";

export default {
data() {
    const form = useForm({
      name: null,
      rfc: null,
      phone: null,
      address: null,
    });

    return {
       form,
    };
},
components:{
AppLayout,
PrimaryButton,
InputLabel,
InputError,
Back
},
methods:{
    store() {
      this.form.post(route("clients.store"), {
        onSuccess: () => {
         this.$notify({
          title: "Correcto",
          message: "Se ha agregado un nuevo cliente",
          type: "success",
        });
        this.$inertia.get(route('clients.index'));
        },
      });
    },
}
}
</script>
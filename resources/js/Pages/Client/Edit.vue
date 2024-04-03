<template>
    <AppLayout title="Editar cliente">
        <div class="md:px-10 px-2 py-7">
            <Back :route="'clients.index'" />

            <form @submit.prevent="update" class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-1/2 mx-auto mt-7 lg:grid lg:grid-cols-2 gap-x-3">
                <h1 class="font-bold ml-2 col-span-full">Editar cliente</h1>
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
                    <InputLabel class="mb-1 ml-2" value="Teléfono *" />
                    <el-input v-model="form.phone"
                    :formatter="(value) => `${value}`.replace(/(\d{2})(\d{4})(\d{4})/, '$1 $2 $3')"
                    :parser="(value) => value.replace(/\D/g, '')" maxlength="10" clearable
                    placeholder="Escribe el número de teléfono" />
                    <InputError :message="form.errors.phone" />
                </div>
                <div class="mt-3">
                    <InputLabel value="Dirección" class="ml-3 mb-1" />
                    <el-input v-model="form.address" placeholder="Escribe la dirección" :maxlength="100" clearable />
                    <InputError :message="form.errors.address" />
                </div>
                <div class="col-span-2 text-right mt-8">
                    <PrimaryButton class="!rounded-full" :disabled="form.processing">Guardar cambios</PrimaryButton>
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
      name: this.client.name,
      rfc: this.client.rfc,
      phone: this.client.phone,
      address: this.client.address,
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
props:{
client: Object
},
methods:{
    update() {
      this.form.put(route("clients.update", this.client.id), {
        onSuccess: () => {
         this.$notify({
          title: "Correcto",
          message: "Se ha editado el cliente",
          type: "success",
        });
        },
      });
    },
}
}
</script>
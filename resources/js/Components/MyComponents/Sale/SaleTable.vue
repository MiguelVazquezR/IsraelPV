<template>
  <!-- vista desktop -->
  <div v-if="saleProducts?.length" class="w-full mx-auto text-[11px] lg:text-sm over hidden md:block">
    <div class="text-center lg:text-base flex items-center space-x-4 mb-3 border-b border-primary">
      <div class="font-bold pb-3 pl-2 text-left w-[45%]">Producto</div>
      <div class="font-bold pb-3 text-left w-[15%]">Precio unitario</div>
      <div class="font-bold pb-3 text-left w-[20%]">Cantidad</div>
      <div class="font-bold pb-3 text-left w-[15%]">Importe</div>
      <div class="font-bold pb-3 text-left w-[5%]"></div>
    </div>
    <div class="overflow-auto h-[500px]">
      <div v-for="(sale, index) in saleProducts" :key="index"
        class="mb-2 flex items-center space-x-4 border rounded-lg relative py-3 px-4">
        <div class="grid grid-cols-2 items-center h-28 w-[50%]">
          <img class="mx-auto h-28 object-contain" :src="sale.product.imageCover[0]?.original_url">
          <div>
            <p class="text-sm text-gray-400 pl-2">{{ sale.product.category?.name }}</p>
            <p class="font-bold text-lg">{{ sale.product.name }}</p>
          </div>
        </div>
        <div :class="editMode !== null ? 'w-[35%]' : 'w-[15%]'" class="text-base flex items-center">
          <template v-if="editMode !== index">
            ${{ sale.product.public_price }}
            <button @click.stop="startEditing(sale, index)"
              class="flex items-center justify-center text-primary bg-gray-200 size-5 rounded-full ml-2 mr-1">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-[14px]">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
              </svg>
            </button>
          </template>
          <template v-else-if="editMode == index">
            <div class="flex items-center space-x-2">
              <el-input v-model="editedPrice" @keyup.enter="stopEditing(sale)" type="number" step="0.01">
                <template #prefix>
                  <i class="fa-solid fa-dollar-sign"></i>
                </template>
              </el-input>
              <button @click="stopEditing(sale)"
                class="flex items-center justify-center rounded-full size-5 bg-primary flex-shrink-0"><i
                  class="fa-solid fa-check text-white text-[10px]"></i></button>
              <button @click="editMode = false"
                class="flex items-center justify-center rounded-full size-5 bg-[#EDEDED] flex-shrink-0"><i
                  class="fa-solid fa-x mark text-black text-[10px]"></i></button>
            </div>
          </template>
        </div>
        <div class="w-[20%]">
          <!-- <el-input-number v-model="sale.quantity" :min="0" :max="sale.product.current_stock" :precision="2" /> -->
          <el-input-number v-model="sale.quantity" :min="0" :precision="2" />
        </div>
        <div class="text-[#5FCB1F] font-bold w-[15%] text-base">
          ${{ (sale.product.public_price * sale.quantity).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
        </div>
        <div class="w-[5%] text-right">
          <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#C30303" title="¿Continuar?"
            @confirm="deleteItem(sale.product.id)">
            <template #reference>
              <i
                class="fa-regular fa-trash-can mr-2 text-primary cursor-pointer p-2 hover:bg-gray-100 rounded-full"></i>
            </template>
          </el-popconfirm>
        </div>
      </div>
    </div>
  </div>

  <!-- vista movil ----------->
  <div class="overflow-y-auto h-[230px] md:hidden text-[11px]">
    <div v-for="(sale, index) in saleProducts" :key="index"
      class="mb-2 grid grid-cols-3 gap-2 border rounded-md items-center relative">
      <figure>
        <img class="mx-auto w-3/4 object-contain" :src="sale.product.imageCover[0]?.original_url" alt="">
      </figure>
      <div class="col-span-2 flex flex-col space-y-1 justify-center py-1">
        <p class="text-xs text-gray-400 pl-2">{{ sale.product.category?.name }}</p>
        <p class="font-bold text-base">{{ sale.product.name }}</p>
        <div class="flex items-center space-x-2">
          <template v-if="editMode !== index">
            ${{ sale.product.public_price }}
            <button @click.stop="startEditing(sale, index)"
              class="flex items-center justify-center text-primary bg-gray-200 size-4 rounded-full ml-2 mr-1">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-3">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
              </svg>
            </button>
          </template>
          <template v-else-if="editMode == index">
            <div class="flex items-center space-x-2">
              <div class="w-1/2">
                <el-input v-model="editedPrice" @keyup.enter="stopEditing(sale)" type="number" step="0.01">
                  <template #prefix>
                    <i class="fa-solid fa-dollar-sign"></i>
                  </template>
                </el-input>
              </div>
              <button @click="stopEditing(sale)"
                class="flex items-center justify-center rounded-full size-5 bg-primary flex-shrink-0"><i
                  class="fa-solid fa-check text-white text-[10px]"></i></button>
              <button @click="editMode = false"
                class="flex items-center justify-center rounded-full size-5 bg-[#EDEDED] flex-shrink-0"><i
                  class="fa-solid fa-x mark text-black text-[10px]"></i></button>
            </div>
          </template>
        </div>
        <!-- <el-input-number v-model="sale.quantity" :min="0" :max="sale.product.current_stock" :precision="2" size="small" /> con validacion de cantidad de stock disponible --> 
        <el-input-number v-model="sale.quantity" :min="0" :precision="2" size="small" />
        <div class="text-[#5FCB1F] font-bold text-sm">${{ (sale.product.public_price * sale.quantity).toLocaleString('en-US',
      {
        minimumFractionDigits: 2
      }) }}</div>
        <div class="self-end">
          <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#C30303" title="¿Continuar?"
            @confirm="deleteItem(sale.product.id)" class="justify-self-end">
            <template #reference>
              <button class="mr-2 text-primary cursor-pointer size-7 hover:bg-gray-100 rounded-full">
                <i class="fa-regular fa-trash-can text-sm"></i>
              </button>
            </template>
          </el-popconfirm>
        </div>
      </div>
    </div>
  </div>
  <p class="text-center text-gray-500 text-sm mt-14" v-if="saleProducts?.length == 0">Ingresa un producto para comenzara generar la venta</p>
</template>

<script>
export default {
  data() {
    return {
      quantity: 1,
      editMode: null,
      editedPrice: null
    };
  },
  props: {
    saleProducts: Array
  },
  emits: ['delete-product'],
  methods: {
    deleteItem(productId) {
      this.$emit('delete-product', productId);
    },
    startEditing(sale, index) {
      this.editMode = index;
      this.editedPrice = sale.product.public_price;
    },
    stopEditing(sale) {
      this.editMode = null;
      // Aquí puedes realizar la lógica para actualizar el precio en tu modelo de datos.
      // En este ejemplo, simplemente actualizamos el precio en el objeto de venta directamente.
      sale.product.public_price = parseFloat(this.editedPrice);
    },
  },
};
</script>
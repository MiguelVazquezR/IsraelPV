<template>
<Head :title="sale.data.folio" />

    <div id="text-to-print" class="w-full lg:w-[220px] mx-auto text-sm">
        <!-- <p class="h-2 border-dashed border-b border-gray-900"></p>
        <p class="h-2 border-dashed border-b border-gray-900"></p> -->
        <span>----------------------------------------------------------------</span>
        <p class="text-right mt-2">Folio: {{ sale.data.folio }}</p>
        <p v-if="sale.data.has_credit" class="text-right mt-2">SISTEMA DE CREDITO</p>

        <div class="mt-3 text-xs">
            <p class="flex justify-between">
              <span>Atiende: {{ $page.props.auth.user.name }}</span>
              <span>{{ sale.data.created_at }}</span>
            </p>

            <p class="flex justify-between">
              <span>Cliente: {{ sale.data.client?.name ?? '--' }}</span>
            </p>
        </div>
        <span>--------------------------------</span>
        <!-- <p class="h-2 border-dashed border-b border-gray-900"></p> -->

        <div class="flex flex-col">
          <span v-for="product in sale.data.products" :key="product">
            {{ product.pivot?.quantity + '. ' }} {{ product.name + ' ' }}
            ${{ product.pivot.price?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' = ' }}
            ${{ (product.pivot?.quantity * product.pivot.price)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
          </span>
        </div>
        <!-- tabla de productos -->
        <!-- <table class="mt-2 w-full text-[10px]">
            <tr class="text-left">
                <th>Cant.</th>
                <th>Descrip.</th>
                <th>Precio</th>
                <th>Importe</th>
            </tr>
            <tr v-for="product in sale.data.products" :key="product">
                <td>{{ product.pivot?.quantity }}</td>
                <td class="uppercase px-[2px]">{{ product.name }}</td>
                <td>${{ product.public_price?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</td>
                <td>{{ (product.pivot?.quantity * product.public_price)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</td>
            </tr>
        </table> -->

        <p class="font-bold text-base my-2">TOTAL ${{ sale.data.total?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }} </p>
        <span>--------------------------------</span>
        <!-- <p class="h-2 border-dashed border-b border-gray-900"></p> -->
        <p class="uppercase text-s mt-2">{{ '(' + convertirNumeroALetra(sale.data.total) + ' PESOS)' }}</p>
        <p class="font-bold text-xs my-1">TOTAL DE ARTICULOS: {{ sale.data.products?.length }}</p>
        <p class="font-bold">GRACIAS POR SU COMPRA</p>

        <!-- Pagaré en caso de crédito -->
        <div class="mt-1 text-[10px]">
            <p>DEBO Y PAGARE INCONDICIONALMENTE A LA ORDEN DE ISAAC DIAZ EN ESTA CIUDAD O EN LA QUE SE ME REQUIERA EL DIA</p>
            <p>DE____________DEL 20_______LA CANTIDAD</p>
            <p>DE $__________________MXN.</p>
        </div>

        <!-- texto y firma -->
        <footer class="mt-2 text-[10px]">
            <p>
                VALOR DE LAS MERCANCIAS RECIBIDAS A MI ENTERA SATISFACCION ESTE PAGARE ES MERCANTIL Y ESTA REGIDO POR LA LEY
                GENERAL DE TITULOS Y OPERACIONES DE CREDITO EN SU ARTÍCULO 173 PARTE FINAL Y ARTICULOS CORRELATIVOS POR NO SER
                PAGARE DOMICILIARIO.
            </p>
            <!-- <div class="border-b border-black w-[170px] mx-auto mt-8"></div> -->
            <p class="text-center mt-5">______________________________</p>
            <p class="text-center">---- FIRMA DEL CLIENTE ----</p>


            <div class="text-sm font-bold flex flex-col" v-if="sale.data.has_credit && sale.data.client">
              <p class="text-center">--------------------------------</p>
              <!-- <p class="h-2 border-dashed border-b border-gray-900 my-3"></p> -->

            <span>Saldo inicial: ${{ initial_saldo?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span>
            <span>Venta: ${{ sale.data.total?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span>
            <span>Abono: ${{ payment?.amount?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") ?? '00.00' }}</span>
            <span v-if="payment">Nuevo saldo: ${{ (initial_saldo + sale.data.total - payment?.amount)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span>
            <span v-else>Nuevo saldo: ${{ (initial_saldo + sale.data.total)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span>
            </div>
        </footer>
    </div>

    <!-- Botones de conexión e impresión -->
    <section class="text-center lg:space-x-2 mt-7 mx-10">
      <ThirthButton v-if="!printing" @click="conectarBluetooth" class="!py-1 !border-blue-600 !text-blue-600 mr-2 mb-2">
        <i class="fa-brands fa-bluetooth text-lg mr-2"></i>
        Conectar impresora
      </ThirthButton>

      <!-- imprimir desde dispositivo movil -->
      <PrimaryButton v-if="device && !printing" class="mb-2 mr-2" @click="enviarDatosImpresion(device)">
        <i class="fa-solid fa-print"></i>
        Imprimir ticket
      </PrimaryButton>

      <!-- imprimir desde pc de escritorio o dispositivo con windows -->
      <PrimaryButton v-if="!printing" @click="print">
        <i class="fa-solid fa-display"></i>
        Imprimir Pantalla 
      </PrimaryButton>
    </section>

</template>

<script>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
import { Head } from '@inertiajs/vue3';
export default {
data() {
    return {
      device: null, //Dispositivo de impresora guardada al hacer vínculo
      printing: false, //bandera que oculta o muestra botones para que no salgan en impresión
      text: null, //guarda el texto a pimprimir. (ticket)
    }
},
components:{
PrimaryButton,
ThirthButton,
Head
},
props:{
sale: Object,
payment: Object,
initial_saldo: Number
},
methods: {
    convertirNumeroALetra(numero) {
      const unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
      const decenas = ['', 'diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
      const especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];

      let letra = '';

      if (numero === 0) {
        return 'cero';
      }

      if (numero >= 100000) {
        letra += unidades[Math.floor(numero / 100000)] + ' mil ';
        numero %= 100000;
      }

      if (numero >= 1000) {
        letra += this.convertirMiles(numero);
      } else {
        letra += this.convertirCentenas(numero);
      }

      return letra.trim();
    },
    convertirMiles(numero) {
      const unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
      const decenas = ['', 'diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];

      let miles = Math.floor(numero / 1000);
      let centenas = numero % 1000;
      let milesEnLetra = '';

      if (miles >= 100) {
        milesEnLetra += unidades[Math.floor(miles / 100)] + 'cientos ';
        miles %= 100;
      }

      milesEnLetra += this.convertirCentenas(miles);

      if (centenas > 0) {
        milesEnLetra += ' mil ' + this.convertirCentenas(centenas);
      }

      return milesEnLetra;
    },
    convertirCentenas(numero) {
      const unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
      const decenas = ['', 'diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
      const especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];

      let centenasEnLetra = '';

      if (numero >= 100) {
        centenasEnLetra += unidades[Math.floor(numero / 100)] + 'cientos ';
        numero %= 100;
      }

      if (numero >= 20) {
        centenasEnLetra += decenas[Math.floor(numero / 10)] + ' ';
        numero %= 10;
        if (numero !== 0) {
          centenasEnLetra += 'y ';
        }
      } else if (numero >= 10) {
        centenasEnLetra += especiales[numero - 10];
        return centenasEnLetra;
      }

      if (numero > 0) {
        centenasEnLetra += unidades[numero];
      }

      return centenasEnLetra;
    },
    conectarBluetooth() {
      // Solicitar al usuario que seleccione la impresora vía Bluetooth
      navigator.bluetooth.requestDevice({
        acceptAllDevices: true, // Aceptar cualquier dispositivo Bluetooth
        optionalServices: ['49535343-fe7d-4ae5-8fa9-9fafd205e455'] // UUID del servicio de la impresora
      })
      .then(device => {
        console.log('Dispositivo Bluetooth conectado:', device);
        this.device = device;

      })
      .catch(error => {
        console.error('Error al conectar con dispositivo Bluetooth:', error);
      });
    },
    async enviarDatosImpresion(device) {
      try {
        // Obtener el servicio de impresión
        const service = await device.gatt.connect().then(server => server.getPrimaryService('49535343-fe7d-4ae5-8fa9-9fafd205e455'));

        // Obtener el carácterística de escritura
        const characteristic = await service.getCharacteristic('49535343-8841-43f4-a8d4-ecbe34729bb3');

        // // Aquí puedes enviar los datos de impresión a través de la característica de escritura
        // // Por ejemplo, puedes enviar una cadena de texto a imprimir

        // Dividir el texto en fragmentos
        const fragmentSize = 20; // Ajusta este tamaño según sea necesario
        const fragments = this.chunkText(this.text, fragmentSize);

        // Enviar cada fragmento por separado
        for (const fragment of fragments) {
          await characteristic.writeValue(new TextEncoder().encode(fragment));
        }

        console.log('Datos de impresión enviados correctamente');
      } catch (error) {
        console.error('Error al enviar datos de impresión:', error);
      }
    },
    chunkText(text, size) {
      const chunks = [];
      for (let i = 0; i < text.length; i += size) {
        chunks.push(text.slice(i, i + size));
      }
      return chunks;
    },
    print() {
      this.printing = true;
      setTimeout(() => {
        window.print();
      }, 300);
    },
    handleAfterPrint() {
      this.printing = false;
    }
  },
  mounted() {
    window.addEventListener('afterprint', this.handleAfterPrint);
    this.text = document.getElementById('text-to-print').innerText;
  },
  beforeDestroy() {
    window.removeEventListener('afterprint', this.handleAfterPrint);
  }
}
</script>
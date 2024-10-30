<template>
<Head :title="sale.data.folio" />

    <div id="text-to-print" class="w-full lg:w-[220px] mx-auto text-sm">
        <span>--------------------------------</span>
        <p class="text-right mt-2">Folio: {{ sale.data.folio }}</p>
        <small v-if="sale.data.has_credit" class="text-right mt-2">SISTEMA DE CREDITO</small>

        <div class="mt-1 text-xs">
            <small class="flex justify-between">
              <span>Atiende: {{ $page.props.auth.user.name }}</span>
              <span>{{ sale.data.created_at }}</span>
            </small>

            <small class="flex justify-between">
              <span>Cliente: {{ sale.data.client?.name ?? '--' }}</span>
            </small>
        </div>
        <span>--------------------------------</span>

        <!-- <div class="flex flex-col"> -->
          <p class="text-xs" v-for="product in sale.data.products" :key="product">
            {{ product.pivot?.quantity + '  ' }} <span class="ml-[5px]"> {{ product.name + ' ' }}</span>
            ${{ product.pivot.price?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' = ' }}
            ${{ (product.pivot?.quantity * product.pivot.price)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
          </p>
        <!-- </div> -->

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

        <p class="font-bold text-base mt-1">TOTAL ${{ sale.data.total?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }} </p>
        <span>--------------------------------</span>
        <p class="uppercase text-s mt-2">{{ '(' + numberToWords(sale.data.total) + ' )' }}</p>
        <p class="font-bold text-xs my-1">TOTAL DE ARTICULOS: {{ sale.data.products?.length }}</p>

        <!-- Pagaré en caso de crédito -->
        <div class="mt-1 text-[10px]">
            <small>DEBO Y PAGARE INCONDICIONALMENTE A LA ORDEN DE ISAAC DIAZ EN ESTA CIUDAD O EN LA QUE SE ME REQUIERA EL DIA
              DE____________DEL 20____LA CANTIDAD DE $_____________MXN.
            </small>
        </div>

        <!-- texto y firma -->
        <footer class="mt-2 text-[10px]">
            <small>
                VALOR DE LAS MERCANCIAS RECIBIDAS A MI ENTERA SATISFACCION ESTE PAGARE ES MERCANTIL Y ESTA REGIDO POR LA LEY
                GENERAL DE TITULOS Y OPERACIONES DE CREDITO EN SU ARTICULO 173 PARTE FINAL Y ARTICULOS CORRELATIVOS POR NO SER
                PAGARE DOMICILIARIO.
            </small>

            <div class="text-center">
              <p class="text-center mt-5">________________________________</p>
              <small class="text-center">----- FIRMA DEL CLIENTE -----</small>
            </div>

            <p class="font-bold text-center">GRACIAS POR SU COMPRA</p>
            <span class="text-center">--------------------------------</span>

            <div class="text-sm font-bold flex flex-col" v-if="sale.data.has_credit && sale.data.client">
              <span class="text-center">--------------------------------</span>
              <span>Saldo inicial: ${{ initial_saldo?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span>
              <span>Venta: ${{ sale.data.total?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span>
              <span>Abono: ${{ payment?.amount?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") ?? '00.00' }}</span>
              <span v-if="payment">Nuevo saldo: ${{ (initial_saldo + sale.data.total - payment?.amount)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span>
              <span v-else>Nuevo saldo: ${{ (initial_saldo + sale.data.total)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span>
              <p class="text-center">--------------------------------</p>
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
    numberToWords(amount) {
      const unidades = [
          '', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'
      ];
      const decenas = [
          '', 'diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'
      ];
      const centenas = [
          '', 'cien', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'
      ];

      function convertGroup(n) {
          if (n === '100') return 'cien';
          let output = '';
          if (n[0] !== '0') output += centenas[parseInt(n[0])] + ' ';
          
          const tens = parseInt(n[1]);
          const units = parseInt(n[2]);

          if (tens === 1 && units === 0) { // Para casos como "10", "20" sin unidades
              output += decenas[tens];
          } else if (tens > 0) {
              output += decenas[tens];
              if (units > 0) output += ' y ' + unidades[units];
          } else {
              output += unidades[units];
          }

          return output.trim();
      }

      function numberToSpanish(n) {
          const number = parseFloat(n).toFixed(2).split('.');
          const integerPart = number[0];
          const decimalPart = number[1];

          let output = '';
          if (integerPart === '0') {
              output = 'cero';
          } else {
              const groups = [
                  '', 'mil', 'millón', 'mil millones', 'billón', 'mil billones'
              ];
              const groupCount = Math.ceil(integerPart.length / 3);

              for (let i = 0; i < groupCount; i++) {
                  const start = integerPart.length - (i + 1) * 3;
                  const end = integerPart.length - i * 3;
                  const group = integerPart.substring(start < 0 ? 0 : start, end);
                  let groupInWords = convertGroup(group.padStart(3, '0'));

                  if (groupInWords === 'uno mil') {
                      groupInWords = 'mil'; // Ajuste para evitar "uno mil"
                  }

                  if (groupInWords !== '') output = groupInWords + ' ' + groups[i] + ' ' + output;
              }
          }

          output = output.trim();
          return output.charAt(0).toUpperCase() + output.slice(1);
      }

      const [integerPart, decimalPart] = parseFloat(amount).toFixed(2).split('.');
      const integerPartInWords = numberToSpanish(integerPart);
      const centavos = `${decimalPart}/100`;

      return `${integerPartInWords} pesos ${centavos} M.N.`;
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

        // Guardar el dispositivo en localStorage
        localStorage.setItem('bluetoothDeviceId', device);
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

        // Comando ESC/POS para fuente pequeña-media (valores: ESC ! n, donde n = 10 para reducir fuente)
        const smallFontCommand = new Uint8Array([0x1B, 0x21, 0x10]);

        // Reducir el espacio entre líneas (ESC 3 n, donde n define el espacio en puntos)
        const lineSpacingCommand = new Uint8Array([0x1B, 0x33, 20]); // Ajusta 20 según necesites

        // Restablecer fuente normal
        const resetFontCommand = new Uint8Array([0x1B, 0x21, 0x00]);

        // Enviar comandos de fuente y espaciado
        // await characteristic.writeValue(mediumFontCommand);
        // await characteristic.writeValue(lineSpacingCommand);

        // Dividir el texto en fragmentos
        const fragmentSize = 20; // Ajusta este tamaño según sea necesario
        const fragments = this.chunkText(this.text, fragmentSize);

        // Enviar cada fragmento por separado con retraso para que imprima todo
        for (const fragment of fragments) {
          await characteristic.writeValue(new TextEncoder().encode(fragment));
          await new Promise(resolve => setTimeout(resolve, 100)); // Agrega un retraso de 100 ms
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

    // Al cargar la página, intenta recuperar el dispositivo Bluetooth guardado
    const savedBluetoothDevice = localStorage.getItem('semillasBluetoothDeviceId');
    if (savedBluetoothDevice) {
      this.device = savedBluetoothDevice;
    }
  },
  beforeDestroy() {
    window.removeEventListener('afterprint', this.handleAfterPrint);
  }
}
</script>
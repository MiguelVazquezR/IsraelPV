<template>
<Head :title="sale.data.folio" />

    <div class="w-full lg:w-[220px] mx-auto text-sm">
        <p class="h-2 border-dashed border-b border-gray-900"></p>
        <p class="h-2 border-dashed border-b border-gray-900"></p>
        <p class="text-right mt-2">Folio: {{ sale.data.folio }}</p>

        <div class="grid grid-cols-2 mt-3">
            <p>Atiende: {{ $page.props.auth.user.name }}</p>
            <p class="text-right">{{ sale.data.created_at.split(',')[0] }}</p>

            <p>Cliente: {{ sale.data.client?.name }}</p>
            <p class="text-right">{{ sale.data.created_at.split(',')[1] }}</p>
        </div>
        <p class="h-2 border-dashed border-b border-gray-900"></p>

        <!-- tabla de productos -->
        <table class="mt-2 w-full text-xs">
            <tr class="text-left">
                <th>Cantidad</th>
                <th class="px-[2px]">Descripción</th>
                <th class="px-[2px]">Precio</th>
                <th class="px-[2px]">Importe</th>
            </tr>
            <tr v-for="product in sale.data.products" :key="product">
                <td>{{ product.pivot?.quantity }}</td>
                <td class="uppercase px-[2px]">{{ product.name }}</td>
                <td class="px-[2px]">${{ product.public_price?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</td>
                <td class="px-[2px]">{{ (product.pivot?.quantity * product.public_price)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</td>
            </tr>
        </table>

        <p class="font-bold text-base my-2">TOTAL ${{ sale.data.total?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }} </p>
        <p class="h-2 border-dashed border-b border-gray-900"></p>
        <p class="uppercase text-s mt-2">{{ '(' + convertirNumeroALetra(sale.data.total) + ' PESOS)' }}</p>
        <p class="font-bold text-xs mt-1">TOTAL DE ARTÍCULOS: {{ sale.data.products?.length }}</p>
        <p class="font-bold">GRACIAS POR SU COMPRA</p>

        <!-- Pagaré en caso de crédito -->
        <div v-if="sale.data.has_credit" class="mt-1 text-xs">
            <p>DEBO Y PAGARÉ INCONDICIONALMENTE A LA ORDEN DE ISAAC DÍAZ EN ESTA CIUDAD O EN LA QUE SE ME REQUIERA EL DÍA</p>
            <p>DE____________DEL 20_______LA CANTIDAD</p>
            <p>DE $__________________MXN.</p>
        </div>

        <!-- texto y firma -->
        <footer v-if="sale.data.has_credit" class="mt-2 text-xs">
            <p>
                VALOR DE LAS MERCANCÍAS RECIBIDAS A MI ENTERA SATISFACCIÓNESTE PAGARÉ ES MERCANTÍL Y ESTÁ REGIDO POR LA LEY
                GENERAL DE TÍTULOS Y OPERACIONES DE CRÉDITO EN SU ARTÍCULO 173 PARTE FINAL Y ARTÍCULOS CORRELATIVOS POR NO SER
                PAGARÉ DOMICILIARIO.
            </p>
            <div class="border-b border-black w-[170px] mx-auto mt-8"></div>
            <p class="text-center">FIRMA</p>
        </footer>
    </div>

    <!-- <button @click="conectarBluetooth" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
        Conectar a la impresora vía Bluetooth
    </button>
    <button @click="print" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded mt-4">
        Imprimir
    </button>

    <div>
      <p>Nombre del dispositivo: {{ infoDispositivo.name }}</p>
      <p>ID del dispositivo: {{ infoDispositivo.id }}</p>
      <p>UUIDs del dispositivo: {{ infoDispositivo?.uuids?.join(', ') }}</p>
      <p>Versión de Bluetooth: {{ infoDispositivo.version }}</p>
      <p>RSSI (Received Signal Strength Indicator): {{ infoDispositivo.rssi }}</p>
      <p>Fabricante: {{ infoDispositivo.manufacturer }}</p>
    </div> -->
</template>

<script>
import { Head } from '@inertiajs/vue3';
export default {
data() {
    return {
      infoDispositivo: 'nullo'
    }
},
components:{
Head
},
props:{
sale: Object
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
        // optionalServices: ['printer_service_uuid'] // UUID del servicio de la impresora
      })
      .then(device => {
        console.log('Dispositivo Bluetooth conectado:', device);
        // Llamar a la función para enviar datos de impresión después de que se haya emparejado el dispositivo
        this.enviarDatosImpresion(device);


        // Obtener información del dispositivo -------------------
        const info = {
          name: device.name,
          id: device.id,
          uuids: device.uuids,
          version: device.bluetoothVersion,
        //   batteryLevel: device.battery ? await device.battery.getLevel() : 'N/A',
          rssi: device.rssi ? device.rssi : 'N/A',
          manufacturer: device.manufacturerName ? device.manufacturerName : 'Desconocido'
          // Agrega más información del dispositivo según sea necesario
        };

        // Guardar la información del dispositivo en los datos del componente
        this.infoDispositivo = info;

      })
      .catch(error => {
        console.error('Error al conectar con dispositivo Bluetooth:', error);
      });
    },
     async enviarDatosImpresion(device) {
    try {
      // Obtener el servicio de impresión
      const service = await device.gatt.connect().then(server => server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb'));

      // Obtener el carácterística de escritura
      const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');

      // Aquí puedes enviar los datos de impresión a través de la característica de escritura
      // Por ejemplo, puedes enviar una cadena de texto a imprimir
      const data = 'Este es un ejemplo de texto a imprimir';
      await characteristic.writeValue(new TextEncoder().encode(data));

      console.log('Datos de impresión enviados correctamente');
    } catch (error) {
      console.error('Error al enviar datos de impresión:', error);
    }
  },
    print() {
        window.print()
    }
  },
  mounted(){
    this.print();
  }
}
</script>
<template>
<Head title="ticket de abono" />

    <div id="text-to-print" class="w-full lg:w-[220px] mx-auto text-sm">
        <span>--------------------------------</span>
        
        <div class="mt-1 text-xs">
            <small class="flex justify-between">
              <span>Atiende: {{ $page.props.auth.user.name }}</span>
              <span>{{ date }}</span>
            </small>

            <small class="flex justify-between">
              <span>Cliente: {{ client?.name ?? '--' }}</span>
            </small>
        </div>
        <span>--------------------------------</span>

        <p class="font-bold text-base mt-1">COMPROBANTE DE ABONO</p>

        <!-- texto y firma -->
        <footer class="mt-2 text-[10px]">
            <div class="text-sm font-bold flex flex-col">
              <span class="text-center">--------------------------------</span>
              <span>Saldo inicial: ${{ initial_saldo?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span>
              <span>Abono: ${{ payment?.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") ?? '00.00' }}</span>
              <span>Nuevo saldo: ${{ (initial_saldo - payment)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</span>
              <p class="text-center">--------------------------------</p>
            </div>
        </footer>

        <footer>
          <div class="text-center">
              <p class="text-center mt-5">________________________________</p>
              <small class="text-center">----- FIRMA DEL CLIENTE -----</small>
            </div>
            <span class="text-center">--------------------------------</span>
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
      date: this.formatDate(new Date()) // Formatear la fecha al inicializar
    }
},
components:{
PrimaryButton,
ThirthButton,
Head
},
props:{
client: Object,
payment: Number,
initial_saldo: Number
},
methods: {
  formatDate(date) {
        const options = {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        };
        return new Intl.DateTimeFormat('es-ES', options).format(date).replace(',', ''); // Reemplazar la coma
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

        // Guardar el ID del dispositivo en localStorage
        localStorage.setItem('bluetoothDeviceId', device.id);
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
    const savedDeviceId = localStorage.getItem('bluetoothDeviceId');
    if (savedDeviceId) {
      navigator.bluetooth.getDevices().then(devices => {
        const device = devices.find(d => d.id === savedDeviceId);
        if (device) {
          this.device = device;
          console.log('Dispositivo Bluetooth recuperado y conectado:', device);
        } else {
          console.log('No se encontró el dispositivo guardado.');
        }
      }).catch(error => {
        console.error('Error al recuperar dispositivo Bluetooth:', error);
      });
    }
  },
  beforeDestroy() {
    window.removeEventListener('afterprint', this.handleAfterPrint);
  }
}
</script>
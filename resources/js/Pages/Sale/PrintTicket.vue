<template>
    <div class="w-[220px] mx-auto text-sm">
        <p class="h-2">-------------------------------------</p>
        <p class="h-2">-------------------------------------</p>
        <p class="text-right mt-2">Folio: {{ sale.data.folio }}</p>

        <div class="grid grid-cols-2 mt-3">
            <p>Atiende: {{ $page.props.auth.user.name }}</p>
            <p class="text-right">{{ sale.data.created_at.split(',')[0] }}</p>

            <p>Cliente: {{ sale.data.client?.name }}</p>
            <p class="text-right">{{ sale.data.created_at.split(',')[1] }}</p>
        </div>
        <p class="h-2">-------------------------------------</p>

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
        <p class="h-2">-------------------------------------</p>
        <p class="uppercase text-s mt-2">{{ '(' + convertirNumeroALetra(sale.data.total) + ' PESOS)' }}</p>
        <p class="font-bold text-xs mt-1">TOTAL DE ARTÍCULOS: {{ sale.data.products?.length }}</p>
        <p class="font-bold">GRACIAS POR SU COMPRA</p>

        <!-- Pagaré en caso de crédito -->
        <div v-if="sale.data.has_credit" class="mt-3 text-xs">
            <p>DEBO Y PAGARÉ INCONDICIONALMENTE A LA ORDEN DE ISAAC DÍAZ EN ESTA CIUDAD O EN LA QUE SE ME REQUIERA EL DÍA</p>
            <p>DE____________DEL 20_______LA CANTIDAD</p>
            <p>DE $__________________MXN.</p>
        </div>

        <!-- texto y firma -->
        <footer v-if="sale.data.has_credit" class="mt-3 text-xs">
            <p>
                VALOR DE LAS MERCANCÍAS RECIBIDAS A MI ENTERA SATISFACCIÓNESTE PAGARÉ ES MERCANTÍL Y ESTÁ REGIDO POR LA LEY
                GENERAL DE TÍTULOS Y OPERACIONES DE CRÉDITO EN SU ARTÍCULO 173 PARTE FINAL Y ARTÍCULOS CORRELATIVOS POR NO SER
                PAGARÉ DOMICILIARIO.
            </p>
            <div class="border-b border-black w-[170px] mx-auto mt-9"></div>
            <p class="text-center">FIRMA</p>
        </footer>

    </div>
</template>

<script>
export default {
data() {
    return {

    }
},
components:{

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
    }
  },
  mounted(){
    window.print();
  }
}
</script>
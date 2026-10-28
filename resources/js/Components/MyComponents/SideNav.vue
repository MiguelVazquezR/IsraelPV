<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['toggle-collapse']);

// Estructura de menú reactiva para manejar estados de apertura/cierre de submenús
const menus = ref([
    {
        label: 'Punto de venta',
        icon: 'fa-solid fa-basket-shopping',
        route: 'sales.point',
        activeKey: 'sales.point',
        options: []
    },
    {
        label: 'Productos',
        icon: 'fa-regular fa-clipboard',
        activeKey: 'products.*', // Usamos un string para identificar el grupo activo
        isOpen: false, // Estado local para el acordeón
        options: [
            { label: 'Lista de productos', route: 'products.index', activeKey: 'products.index' },
            { label: 'Registro de entradas', route: 'product-histories.index', activeKey: 'product-histories.*' },
        ]
    },
    {
        label: 'Gestión de ventas',
        icon: 'fa-solid fa-chart-simple',
        activeKey: ['dashboard', 'sales.index'], // Puede ser array
        isOpen: false,
        options: [
            { label: 'Análisis de ventas', route: 'dashboard', activeKey: 'dashboard' },
            { label: 'Ventas registradas', route: 'sales.index', activeKey: 'sales.index' },
        ]
    },
    {
        label: 'Clientes',
        icon: 'fa-solid fa-users',
        route: 'clients.index',
        activeKey: 'clients.*',
        options: []
    },
    {
        label: 'Abonos',
        icon: 'fa-solid fa-dollar-sign', // Corregido fa-dollar a fa-dollar-sign (estándar FA 5/6)
        route: 'global-payments.index',
        activeKey: 'global-payments.*',
        options: []
    },
]);

// Función auxiliar para verificar ruta activa (más flexible)
const isActive = (key) => {
    if (Array.isArray(key)) {
        return key.some(k => route().current(k));
    }
    return route().current(key);
};

// Alternar submenú
const toggleSubmenu = (index) => {
    if (props.collapsed) {
        emit('toggle-collapse'); // Si está cerrado y clicamos, lo abrimos
        setTimeout(() => {
            menus.value[index].isOpen = !menus.value[index].isOpen;
        }, 150);
    } else {
        // Cierra otros menús (estilo acordeón estricto) o deja múltiples abiertos
        menus.value.forEach((menu, i) => {
            if (i !== index) menu.isOpen = false;
        });
        menus.value[index].isOpen = !menus.value[index].isOpen;
    }
};

const goToRoute = (routeName) => {
    if (routeName) router.visit(route(routeName));
};

</script>

<template>
    <div class="h-full flex flex-col bg-white">
        <!-- Header del Sidebar (Logo) -->
        <div class="h-16 flex items-center justify-center border-b border-gray-100 shrink-0">
            <Link :href="route('sales.index')" class="flex items-center gap-2 overflow-hidden px-2 transition-all duration-300">
                <template v-if="collapsed">
                    <ApplicationMark class="h-8 w-8 text-primary" />
                </template>
                <template v-else>
                     <!-- Asumiendo que AuthenticationCardLogo es el logo completo -->
                    <div class="flex items-center gap-2">
                        <ApplicationMark class="h-8 w-8 text-primary" />
                        <span class="font-bold text-lg text-gray-700 tracking-tight">SEMILLAS Y CEREALES</span>
                    </div>
                </template>
            </Link>
        </div>

        <!-- Lista de Navegación -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <template v-for="(menu, index) in menus" :key="index">
                
                <!-- Caso 1: Item Simple sin Submenú -->
                <div v-if="!menu.options || menu.options.length === 0">
                    <Link 
                        :href="route(menu.route)" 
                        :title="menu.label"
                        class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200"
                        :class="isActive(menu.activeKey) 
                            ? 'bg-indigo-50 text-indigo-700' 
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                    >
                        <i :class="[menu.icon, 'text-lg transition-colors duration-200 shrink-0', isActive(menu.activeKey) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500']"></i>
                        
                        <span 
                            class="ml-3 transition-all duration-300 whitespace-nowrap overflow-hidden"
                            :class="collapsed ? 'w-0 opacity-0' : 'w-auto opacity-100'"
                        >
                            {{ menu.label }}
                        </span>
                    </Link>
                </div>

                <!-- Caso 2: Item con Submenú -->
                <div v-else>
                    <button 
                        @click="toggleSubmenu(index)"
                        :title="menu.label"
                        class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none"
                        :class="isActive(menu.activeKey) || menu.isOpen
                            ? 'bg-gray-50 text-gray-900' 
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                    >
                        <div class="flex items-center overflow-hidden">
                            <i :class="[menu.icon, 'text-lg shrink-0 transition-colors duration-200', isActive(menu.activeKey) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500']"></i>
                            <span 
                                class="ml-3 transition-all duration-300 whitespace-nowrap"
                                :class="collapsed ? 'w-0 opacity-0' : 'w-auto opacity-100'"
                            >
                                {{ menu.label }}
                            </span>
                        </div>
                        
                        <!-- Flecha indicadora -->
                        <svg 
                            v-if="!collapsed"
                            class="ml-2 h-4 w-4 transform transition-transform duration-200 text-gray-400" 
                            :class="menu.isOpen ? 'rotate-90 text-gray-600' : 'rotate-0'"
                            viewBox="0 0 20 20" fill="currentColor"
                        >
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Opciones del Submenú (Transición nativa sin componente externo) -->
                    <div 
                        v-show="menu.isOpen && !collapsed" 
                        class="mt-1 space-y-1 overflow-hidden transition-all duration-300"
                    >
                        <Link 
                            v-for="(option, idx) in menu.options" 
                            :key="idx"
                            :href="route(option.route)"
                            class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-lg transition-colors duration-150"
                            :class="isActive(option.activeKey)
                                ? 'text-indigo-600 bg-indigo-50'
                                : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'"
                        >
                            <!-- Pequeño punto para indicar item de submenú -->
                            <span 
                                class="w-1.5 h-1.5 rounded-full mr-2 transition-colors"
                                :class="isActive(option.activeKey) ? 'bg-indigo-600' : 'bg-gray-300 group-hover:bg-gray-400'"
                            ></span>
                            <span class="truncate">{{ option.label }}</span>
                        </Link>
                    </div>
                </div>
            </template>
        </nav>

        <!-- Footer del Sidebar (Opcional: Botón de Toggle para Desktop) -->
        <div class="p-4 border-t border-gray-100 hidden lg:flex justify-end">
            <button 
                @click="$emit('toggle-collapse')"
                class="p-2 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
            >
                <i :class="['fa-solid', collapsed ? 'fa-angles-right' : 'fa-angles-left']"></i>
            </button>
        </div>
    </div>
</template>
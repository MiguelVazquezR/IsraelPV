<template>
    <!-- sidebar -->
    <div class="h-screen hidden lg:block shadow-lg relative">
        <i @click="small = false" v-if="small"
            class="fa-solid fa-angle-right text-center text-xs pt-[2px] text-white rounded-full size-5 bg-primary absolute top-24 -right-3 cursor-pointer hover:scale-125 transition-transform ease-linear duration-150"></i>
        <i @click="small = true" v-else
            class="fa-solid fa-angle-left text-center text-xs pt-[2px] text-white rounded-full size-5 bg-primary absolute top-24 -right-3 cursor-pointer hover:scale-125 transition-transform ease-linear duration-150"></i>
        <div class="bg-[#F2F2F2] h-full overflow-auto">
            <!-- Logo -->
            <div class="flex items-center justify-center">
                <Link v-if="small" class="mt-4" :href="route('sales.index')">
                <ApplicationMark />
                </Link>
                <Link class="w-28" v-else :href="route('sales.index')">
                <AuthenticationCardLogo />
                </Link>
            </div>
            <nav class="pr-2 pt-20 text-gray-600 text-xs">
                <!-- Con barra pequeña -->
                <template v-if="small">
                    <div v-for="(menu, index) in menus" :key="index">
                        <button v-if="menu.show" @click="goToRoute(menu.route)" :active="menu.active"
                            :title="menu.label"
                            class="w-full text-center py-2 pr-3 pl-5 justify-between rounded-r-[10px] mt-2 transition ease-linear duration-150"
                            :class="menu.active ? 'bg-gray-300 text-primary border-l-4 border-primary' : 'hover:text-primary hover:bg-gray-300 text-gray-600'">
                            <span v-html="menu.icon"></span>
                        </button>
                    </div>
                </template>

                <!-- Con barra grande -->
                <template v-else v-for="(menu, index) in menus" :key="index">
                    <!-- Con submenues -->
                    <div v-if="menu.show">
                        <Accordion v-if="menu.options.length" :icon="menu.icon" :active="menu.active" :ref="'accordion-'+index"
                            :title="menu.label" :id="index">
                            <div v-for="(option, index2) in menu.options" :key="index2">
                                <button @click="goToRoute(option.route)" v-if="option.show"
                                    :title="option.label"
                                    class="w-full text-start pl-6 pr-2 mt-2 flex justify-between text-xs rounded-md py-1 transition ease-linear duration-150"
                                    :class="option.active ? 'text-primary' : 'hover:text-primary hover:bg-gray-300 text-gray-600'">
                                    <p class="w-full truncate"> {{ option.label }}</p>
                                </button>
                            </div>
                        </Accordion>
                        <!-- Sin submenues -->
                        <button v-else-if="menu.show" @click="goToRoute(menu.route)" :active="menu.active"
                            :title="menu.label"
                            class="w-full text-start pl-4 pr-3 mt-2 border-l-4 flex items-center justify-between text-xs rounded-r-[10px] py-1 transition ease-linear duration-150"
                            :class="menu.active ? 'bg-gray-300 text-primary border-primary font-bold' : 'hover:text-primary border-transparent hover:bg-gray-300 text-gray-600'">
                            <p class="w-full truncate"><span class="mr-2" v-html="menu.icon"></span> {{
                                menu.label }}</p>
                        </button>
                    </div>
                </template>
            </nav>
        </div>
    </div>
</template>

<script>
import Accordion from './Accordion.vue';
import { Link } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

export default {
    data() {
        return {
            small: true,
            collapsedMenu: null,
            menus: [
                {
                    label: 'Punto de venta',
                    icon: '<i class="fa-solid fa-basket-shopping text-lg"></i>',
                    route: route('sales.point'),
                    active: route().current('sales.point'),
                    options: [],
                    dropdown: false,
                    show: true
                },
                {
                    label: 'Productos',
                    icon: '<i class="fa-regular fa-clipboard text-lg"></i>',
                    route: route('products.index'),
                    active: route().current('products.*'),
                    options: [],
                    dropdown: false,
                    show: true
                },
                {
                    //    ejemplo para usar submenues
                    label: 'Gestión de ventas',
                    icon: '<i class="fa-solid fa-chart-simple text-lg"></i>',
                    route: null,
                    active: route().current('dashboard') || route().current('sales.index'),
                    options: [
                        {
                            label: 'Análisis de ventas',
                            route: route('dashboard'),
                            active: route().current('dashboard'),
                            show: true,
                        },
                        {
                            label: 'Ventas registradas',
                            route: route('sales.index'),
                            active: route().current('sales.index'),
                            show: true,
                        },
                    ],
                    dropdown: true,
                    show: true
                },
                {
                    label: 'Clientes',
                    icon: '<i class="fa-solid fa-users text-lg"></i>',
                    route: route('clients.index'),
                    active: route().current('clients.*'),
                    options: [],
                    dropdown: false,
                    show: true
                },
                // {
                //     label: 'Configuraciones',
                //     icon: '<i class="fa-solid fa-gears text-lg"></i>',
                //     route: route('settings.index'),
                //     active: route().current('settings.*'),
                //     options: [],
                //     dropdown: false,
                //     show: true
                // },
            ],
        }
    },
    components: {
        AuthenticationCardLogo,
        ApplicationMark,
        Accordion,
        DropdownLink,
        Dropdown,
        Link
    },
    methods: {
        handleClickInMenu(index) {
            if (this.menus[index].options.length) {
                if (this.collapsedMenu === index) {
                    this.collapsedMenu = null;
                } else {
                    this.collapsedMenu = index;
                }
            } else {
                this.goToRoute(this.menus[index].route)
            }
        },
        goToRoute(route) {
            if (route === null) {
                this.small = false;
            } else {
                this.$inertia.get(route);
            }
        },
        logout() {
            this.$inertia.post(route('logout'));
        }
    },
    mounted() {
    }
}
</script>
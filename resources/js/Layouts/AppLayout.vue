<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import SideNav from '@/Components/MyComponents/SideNav.vue';
import NotificationsCenter from '@/Components/MyComponents/NotificationsCenter.vue';

defineProps({
    title: String,
});

const isSidebarOpen = ref(false); // Controla el sidebar en móvil
const isSidebarCollapsed = ref(false); // Controla si está minimizado en escritorio

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebar = () => {
    isSidebarOpen.value = false;
};

// Controlar el estado colapsado desde el hijo si es necesario, o localmente
const toggleCollapse = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
}

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="bg-gray-50 min-h-screen font-sans text-gray-900">
        <Head :title="title" />
        <Banner />

        <div class="flex h-screen overflow-hidden">
            
            <!-- Backdrop para móvil (oscurece el fondo cuando sale el menú) -->
            <div :class="isSidebarOpen ? 'block' : 'hidden'" 
                 @click="closeSidebar"
                 class="fixed inset-0 z-20 bg-black bg-opacity-50 transition-opacity lg:hidden">
            </div>

            <!-- SideNav Container -->
            <!-- En móvil es fixed (flotante), en escritorio es relative (ocupa espacio) -->
            <aside :class="[
                'fixed inset-y-0 left-0 z-30 bg-white shadow-xl transition-all duration-300 ease-in-out lg:static lg:shadow-none lg:border-r lg:border-gray-200',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                isSidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
                'w-64' 
            ]">
                <!-- Pasamos props para controlar el aspecto visual interno -->
                <SideNav 
                    :collapsed="isSidebarCollapsed" 
                    @toggle-collapse="toggleCollapse" 
                />
            </aside>

            <!-- Contenido Principal -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                
                <!-- Top Navbar -->
                <nav class="bg-white border-b border-gray-200 h-16 flex-shrink-0">
                    <div class="px-4 sm:px-6 lg:px-8 h-full">
                        <div class="flex justify-between items-center h-full">
                            
                            <!-- Izquierda: Hamburger & Logo Móvil -->
                            <div class="flex items-center gap-4">
                                <button @click="toggleSidebar" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>
                                
                                <!-- Logo visible solo en móvil (en escritorio está en el sidebar) -->
                                <div class="lg:hidden flex-shrink-0 flex items-center">
                                    <Link :href="route('dashboard')">
                                        <ApplicationMark class="block w-auto" />
                                    </Link>
                                </div>

                                <!-- Botón para colapsar menú en escritorio (Opcional, si quieres controlarlo desde arriba) -->
                                <button @click="toggleCollapse" class="hidden lg:block text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Derecha: Notificaciones y Perfil -->
                            <div class="flex items-center ml-4 md:ml-6 space-x-4">
                                <NotificationsCenter />

                                <!-- Profile Dropdown -->
                                <div class="relative">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                                <img class="h-9 w-9 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                            </button>
                                            <span v-else class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                                    {{ $page.props.auth.user.name }}
                                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <div class="block px-4 py-2 text-xs text-gray-400">Administrar Cuenta</div>
                                            <DropdownLink :href="route('profile.show')">Perfil</DropdownLink>
                                            <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">API Tokens</DropdownLink>
                                            <div class="border-t border-gray-100" />
                                            <form @submit.prevent="logout">
                                                <DropdownLink as="button">Cerrar sesión</DropdownLink>
                                            </form>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Área de Contenido con Scroll Independiente -->
                <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8 relative">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
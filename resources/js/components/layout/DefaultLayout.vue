<template>
  <div class="h-screen bg-gray-100 flex overflow-hidden">
    <!-- Sidebar -->
    <AppSidebar 
      :is-open="sidebarOpen" 
      @close="closeSidebar" 
    />
    
    <!-- Main content area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Header -->
      <AppHeader @toggle-sidebar="toggleSidebar" />
      
      <!-- Page content with proper scroll containment -->
      <main class="flex-1 overflow-y-auto overflow-x-hidden">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-full">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import AppHeader from './AppHeader.vue';
import AppSidebar from './AppSidebar.vue';

// Sidebar state management
const sidebarOpen = ref(false);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
  sidebarOpen.value = false;
};
</script>
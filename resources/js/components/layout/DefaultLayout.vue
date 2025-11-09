<template>
  <div class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <AppSidebar />
    
    <!-- Main content area -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Header -->
      <AppHeader />
      
      <!-- Page content -->
      <main class="flex-1 overflow-auto">
        <div class="py-6 px-4 sm:px-6 lg:px-8">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import AppHeader from './AppHeader.vue';
import AppSidebar from './AppSidebar.vue';
</script>
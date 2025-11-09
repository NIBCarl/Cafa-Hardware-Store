<template>
  <div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h1 class="text-center text-3xl font-bold text-primary-600 mb-2">CAFA Hardware</h1>
      <h2 class="text-center text-2xl font-semibold text-gray-900">
        Point of Sale System
      </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow-lg rounded-lg sm:px-10">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup>
// Guest layout for authentication pages
</script>
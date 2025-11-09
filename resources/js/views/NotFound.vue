<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
      <div class="mb-8">
        <h1 class="text-9xl font-bold text-gray-300">404</h1>
        <h2 class="text-3xl font-semibold text-gray-800 mt-4">Page Not Found</h2>
        <p class="text-gray-600 mt-2">
          The page you are looking for doesn't exist or has been moved.
        </p>
      </div>
      
      <div class="space-y-3">
        <router-link
          to="/"
          class="inline-block w-full px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors"
        >
          Go to Dashboard
        </router-link>
        
        <button
          @click="$router.go(-1)"
          class="inline-block w-full px-6 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors"
        >
          Go Back
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// 404 Not Found page
</script>

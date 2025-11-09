<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 via-blue-50 to-primary-100">
    <!-- Header with gradient and shadow -->
    <header class="bg-gradient-to-r from-white to-primary-50 shadow-lg border-b border-primary-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
        <div class="flex justify-between items-center">
          <!-- Logo with gradient background -->
          <div class="flex items-center space-x-1">
            <div class="bg-gradient-to-br from-primary-500 to-primary-700 p-2 rounded-lg shadow-md">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <span class="text-5xl font-bold bg-gradient-to-r from-primary-600 to-primary-800 bg-clip-text text-transparent">CAFA</span>
            <span class="ml-2 text-3xl font-semibold text-gray-700">Hardware Store</span>
          </div>
          
          <!-- Navigation -->
          <div class="flex items-center space-x-2">
            <router-link
              to="/customer/shop"
              class="px-4 py-2 text-gray-700 hover:text-primary-600 font-semibold transition-all duration-200 hover:bg-primary-50 rounded-lg"
            >
              Shop
            </router-link>
            <router-link
              to="/customer/login"
              class="px-5 py-2.5 text-sm font-semibold text-primary-700 bg-white hover:bg-primary-50 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 border border-primary-200"
            >
              Login
            </router-link>
            <router-link
              to="/customer/register"
              class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-primary-600 to-primary-700 rounded-lg shadow-md hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 active:scale-95"
            >
              Register
            </router-link>
          </div>
        </div>
      </div>
    </header>

    <!-- Hero Section with enhanced depth -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
      <div class="text-center">
        <!-- Badge -->
        <div class="inline-block mb-6">
          <span class="px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white text-sm font-semibold rounded-full shadow-lg">
            🎉 Welcome to Our Store
          </span>
        </div>
        
        <!-- Main Heading with gradient -->
        <h1 class="text-6xl font-extrabold mb-6">
          <span class="bg-gradient-to-r from-gray-900 via-primary-800 to-gray-900 bg-clip-text text-transparent">
            Welcome to CAFA
          </span>
          <br/>
          <span class="bg-gradient-to-r from-primary-600 to-primary-700 bg-clip-text text-transparent">
            Hardware Store
          </span>
        </h1>
        
        <p class="text-xl text-gray-700 mb-10 max-w-3xl mx-auto leading-relaxed">
          Your trusted source for quality hardware and building materials. Shop online and enjoy convenient pickup or delivery options with real-time SMS notifications.
        </p>
        
        <!-- CTA Buttons with enhanced gradients -->
        <div class="flex justify-center space-x-4">
          <router-link
            to="/customer/shop"
            class="group px-10 py-4 text-lg font-bold text-white bg-gradient-to-r from-primary-600 via-primary-700 to-primary-600 rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 active:scale-95 relative overflow-hidden"
          >
            <span class="relative z-10 flex items-center space-x-2">
              <span>Start Shopping</span>
              <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-primary-700 via-primary-800 to-primary-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          </router-link>
          
          <router-link
            to="/customer/register"
            class="group px-10 py-4 text-lg font-bold text-primary-700 bg-white rounded-xl shadow-lg hover:shadow-2xl border-2 border-primary-200 hover:border-primary-300 transform hover:-translate-y-1 transition-all duration-300 active:scale-95"
          >
            <span class="flex items-center space-x-2">
              <span>Create Account</span>
              <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </span>
          </router-link>
        </div>
      </div>

      <!-- Features with enhanced depth and hierarchy -->
      <div class="mt-24 grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Feature Card 1 -->
        <div class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl border border-gray-100 hover:border-primary-200 transition-all duration-300 hover:-translate-y-2">
          <!-- Icon with gradient background -->
          <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-md group-hover:shadow-xl transition-all duration-300 group-hover:scale-110">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-3 group-hover:text-green-600 transition-colors">Easy Online Shopping</h3>
          <p class="text-gray-600 leading-relaxed">Browse our extensive catalog and order from the comfort of your home with our intuitive interface</p>
        </div>

        <!-- Feature Card 2 -->
        <div class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl border border-gray-100 hover:border-primary-200 transition-all duration-300 hover:-translate-y-2">
          <!-- Icon with gradient background -->
          <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-md group-hover:shadow-xl transition-all duration-300 group-hover:scale-110">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors">Fast Processing</h3>
          <p class="text-gray-600 leading-relaxed">Quick order confirmation and processing with real-time SMS notifications to keep you updated</p>
        </div>

        <!-- Feature Card 3 -->
        <div class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl border border-gray-100 hover:border-primary-200 transition-all duration-300 hover:-translate-y-2">
          <!-- Icon with gradient background -->
          <div class="w-20 h-20 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-md group-hover:shadow-xl transition-all duration-300 group-hover:scale-110">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-3 group-hover:text-purple-600 transition-colors">Quality Products</h3>
          <p class="text-gray-600 leading-relaxed">Trusted brands and quality materials for all your construction and hardware projects</p>
        </div>
      </div>

      <!-- Why Choose Us Section -->
      <div class="mt-24 bg-gradient-to-br from-white to-primary-50 rounded-3xl p-12 shadow-xl border border-primary-100">
        <h2 class="text-4xl font-bold text-center mb-12">
          <span class="bg-gradient-to-r from-primary-600 to-primary-800 bg-clip-text text-transparent">
            Why Choose CAFA Hardware?
          </span>
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Benefit 1 -->
          <div class="flex items-start space-x-4 p-4 bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 group">
            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">Extensive Inventory</h4>
              <p class="text-gray-600 text-sm">Wide selection of hardware and building materials</p>
            </div>
          </div>

          <!-- Benefit 2 -->
          <div class="flex items-start space-x-4 p-4 bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 group">
            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">Competitive Prices</h4>
              <p class="text-gray-600 text-sm">Best value for your money with quality assurance</p>
            </div>
          </div>

          <!-- Benefit 3 -->
          <div class="flex items-start space-x-4 p-4 bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 group">
            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">SMS Notifications</h4>
              <p class="text-gray-600 text-sm">Stay updated with order status via SMS</p>
            </div>
          </div>

          <!-- Benefit 4 -->
          <div class="flex items-start space-x-4 p-4 bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 group">
            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">Quick Delivery</h4>
              <p class="text-gray-600 text-sm">Fast processing and convenient delivery options</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer with gradient -->
    <footer class="bg-gradient-to-r from-white to-primary-50 border-t-2 border-primary-100 shadow-inner mt-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="text-center">
          <div class="mb-4">
            <span class="text-3xl font-bold bg-gradient-to-r from-primary-600 to-primary-800 bg-clip-text text-transparent">CAFA</span>
            <span class="ml-2 text-xl font-semibold text-gray-600">Hardware Store</span>
          </div>
          <p class="text-gray-600 font-medium">&copy; 2025 CAFA Hardware Store. All rights reserved.</p>
          <p class="mt-3">
            <router-link 
              to="/auth/login" 
              class="inline-flex items-center space-x-2 text-gray-500 hover:text-primary-600 font-semibold transition-colors duration-200 group"
            >
              <span>Staff Login</span>
              <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </router-link>
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
// Simple home page - no logic needed
</script>


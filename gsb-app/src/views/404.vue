<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const goToHome = () => {
  router.push('/');
};
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-xl px-8 py-12 bg-white rounded-xl shadow-lg border border-gray-100">
      <div class="text-center">
        
        <div class="flex items-center justify-center mb-6">
          <h1 class="text-6xl font-extrabold text-indigo-800">404</h1>
          <div class="h-12 w-px bg-gray-200 mx-6"></div>
          <h2 class="text-2xl font-bold text-gray-800">Page non trouvée</h2>
        </div>

        <p class="mt-4 text-gray-600 max-w-md mx-auto">
          La page que vous recherchez semble introuvable.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
          <button
            @click="goToHome"
            class="w-full sm:w-auto px-6 py-3 font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-300 flex items-center justify-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Retour à l'accueil
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
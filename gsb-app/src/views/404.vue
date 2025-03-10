<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const goToHome = () => {
  router.push('/');
};
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md px-8 py-10 bg-white rounded-lg shadow-md">
      <div class="text-center">
        <h1 class="text-6xl font-bold text-pink-800">404</h1>
        <h2 class="mt-2 text-xl font-semibold text-gray-800">Page non trouvée</h2>
        <p class="mt-4 text-gray-600">
          Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
        </p>
        <button
          @click="goToHome"
          class="mt-6 px-5 py-2 font-medium text-white bg-pink-800 rounded-md hover:bg-pink-900 transition-colors duration-300"
        >
          Retour à l'accueil
        </button>
      </div>
    </div>
  </div>
</template>
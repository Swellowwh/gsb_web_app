<script setup>
import { useUserStore } from '@/stores/user';
const userStore = useUserStore();;
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
      <div class="bg-white rounded-2xl shadow-xl p-8 max-w-xl w-full text-center">
        <h1 class="text-2xl font-bold text-red-600 mb-4">Accès limité</h1>
        <h2 class="text-indigo-700 pb-2">{{ userStore.userData?.email }}</h2>
        <p class="text-gray-700 mb-6">
          Vous êtes actuellement en attente de validation par un administrateur.
          <br />
          Merci de patienter pour être intégré dans notre base employée.
        </p>
        <router-link
          to="/login"
          class="inline-block bg-red-500 hover:bg-red-600 text-white font-semibold px-6 py-2 rounded-lg"
        >
          Retour à l’accueil
        </router-link>
      </div>
    </div>
  </template>
  
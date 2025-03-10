<script setup>
import { computed } from 'vue';
import { useUserStore } from '@/stores/user';

const userStore = useUserStore();

// Message d'accueil en fonction de l'heure
const greetingMessage = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Bonjour';
  if (hour < 18) return 'Bon après-midi';
  return 'Bonsoir';
});

// Fonction pour obtenir les initiales de l'utilisateur
const getUserInitials = computed(() => {
  const username = userStore.userData?.username || '';
  if (!username) return 'UN';

  if (username.includes(' ')) {
    return username
      .split(' ')
      .map(part => part.charAt(0))
      .join('')
      .toUpperCase();
  }

  return username.substring(0, 2).toUpperCase();
});

// Formater le rôle pour l'affichage
const formattedRole = computed(() => {
  const role = userStore.userData?.role || '';
  if (role.toUpperCase() === 'VISITEUR_MEDICAL') return 'Visiteur médical';
  if (role.toUpperCase() === 'COMPTABLE') return 'Comptable';
  return role;
});
</script>

<template>
    <div class="relative flex items-center justify-center min-h-screen w-full">
      <div class="absolute inset-0 bg-pattern opacity-10"></div>
      
      <div class="relative z-10 text-center w-full max-w-lg px-8 py-12 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="size-36 rounded-full bg-gradient-to-r from-pink-600 to-pink-900 flex items-center justify-center mx-auto mb-8 shadow-lg border-4 border-white">
          <span class="text-white text-5xl font-bold">{{ getUserInitials }}</span>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-800 mb-3">
          {{ greetingMessage }}, {{ userStore.userData?.username || 'Utilisateur' }} !
        </h1>
  
        <div class="inline-block px-4 py-1 bg-pink-100 text-pink-800 rounded-full text-sm font-medium mb-6">
          {{ formattedRole }}
        </div>
        
        <div class="mt-6">
          <router-link to="/about/frais" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-pink-600 to-pink-800 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1">
            Accéder à mon espace
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </router-link>
        </div>
      </div>
    </div>
  </template>
  
  <style scoped>
  .bg-pattern {
    background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23db2777' fill-opacity='0.5' fill-rule='evenodd'/%3E%3C/svg%3E");
  }
  </style>
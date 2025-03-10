<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/user';

const userStore = useUserStore();
const router = useRouter();

// État d'authentification
const isLoggedIn = ref(false);
const isAuthenticating = ref(false);
const user = ref({});
const showLoginForm = ref(true);

// Formulaire
const username = ref('');
const password = ref('');
const registerUsername = ref('');
const registerPassword = ref('');

// Messages
const errorMessage = ref('');
const successMessage = ref('');

// Fonction de notification
const showNotification = (message) => {
  successMessage.value = message;
  setTimeout(() => {
    successMessage.value = '';
  }, 3000);
};

// Fonction de connexion
const login = async () => {
  isAuthenticating.value = true;
  errorMessage.value = '';

  try {
    const response = await fetch("http://51.83.76.210/gsb/backend/login.php", {
      method: "POST",
      credentials: "include",
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: username.value,
        password: password.value
      })
    });

    const data = await response.json();
    if (!data.success) throw new Error(data.message || 'Erreur de connexion');

    console.log(data);

    isLoggedIn.value = true;
    userStore.setUser(data.user);
    showNotification('Connexion réussie');

    router.push('/');
  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    isAuthenticating.value = false;
  }
};

// Fonction d'inscription
const register = async () => {
  isAuthenticating.value = true;
  errorMessage.value = '';

  try {
    const response = await fetch("http://51.83.76.210/gsb/backend/register.php", {
      method: "POST",
      credentials: "include",
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: registerUsername.value,
        password: registerPassword.value
      })
    });

    const data = await response.json();
    if (!data.success) throw new Error(data.message || 'Erreur d\'inscription');

    showNotification('Utilisateur créé avec succès');
    showLoginForm.value = true;
    username.value = registerUsername.value;
    
    // Réinitialisation des champs
    registerUsername.value = '';
    registerPassword.value = '';

  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    isAuthenticating.value = false;
  }
};

// Basculer entre les formulaires
const toggleForm = (isLogin) => {
  showLoginForm.value = isLogin;
  errorMessage.value = '';
};
</script>

<template>
  <div class="w-full h-screen flex items-center justify-center p-4 bg-gray-50 relative">
    <div class="absolute inset-0 bg-pattern opacity-10"></div>

    <div v-if="successMessage" class="absolute top-5 right-5 bg-gradient-to-r from-pink-600 to-pink-800 text-white px-4 py-3 rounded-lg shadow-md z-50 flex items-center transition-all duration-300 transform hover:scale-105">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      {{ successMessage }}
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden transition-all duration-300 max-w-md w-full relative z-10">
      <div class="pt-8 pb-4 flex flex-col items-center">
        <div class="w-20 h-20 rounded-full bg-gradient-to-r from-pink-600 to-pink-900 flex items-center justify-center mb-4 shadow-md">
          <span class="text-white text-2xl font-bold">GSB</span>
        </div>
        <p class="text-gray-500 text-sm mt-1">Gestion des frais professionnels</p>
      </div>
      
      <div v-if="!isLoggedIn">
        <div class="flex bg-white border-b">
          <button @click="toggleForm(true)" class="flex-1 py-3 font-medium transition-colors duration-200"
            :class="showLoginForm ? 'text-pink-600 border-b-2 border-pink-600' : 'text-gray-600 hover:text-pink-500'">
            Connexion
          </button>
          <button @click="toggleForm(false)" class="flex-1 py-3 font-medium transition-colors duration-200"
            :class="!showLoginForm ? 'text-pink-600 border-b-2 border-pink-600' : 'text-gray-600 hover:text-pink-500'">
            Inscription
          </button>
        </div>

        <div v-if="errorMessage" class="bg-red-50 border-l-4 border-pink-500 text-pink-700 p-4" role="alert">
          <div class="flex">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p>{{ errorMessage }}</p>
          </div>
        </div>

        <div v-if="showLoginForm" class="p-6">
          <form @submit.prevent="login" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <input type="text" v-model="username"
                  class="pl-10 w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-pink-500 focus:ring focus:ring-pink-200 focus:ring-opacity-50"
                  placeholder="Votre nom d'utilisateur" required>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input type="password" v-model="password"
                  class="pl-10 w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-pink-500 focus:ring focus:ring-pink-200 focus:ring-opacity-50"
                  placeholder="Votre mot de passe" required>
              </div>
            </div>

            <button type="submit"
              class="w-full py-2 px-4 rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-pink-600 to-pink-800 hover:from-pink-700 hover:to-pink-900 transition-all duration-200"
              :class="{ 'opacity-75 cursor-not-allowed': isAuthenticating }" :disabled="isAuthenticating">
              {{ isAuthenticating ? 'Connexion en cours...' : 'Se connecter' }}
            </button>
          </form>
        </div>

        <div v-else class="p-6">
          <form @submit.prevent="register" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <input type="text" v-model="registerUsername"
                  class="pl-10 w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-pink-500 focus:ring focus:ring-pink-200 focus:ring-opacity-50"
                  placeholder="Choisissez un nom d'utilisateur" required>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input type="password" v-model="registerPassword"
                  class="pl-10 w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-pink-500 focus:ring focus:ring-pink-200 focus:ring-opacity-50"
                  placeholder="Créez un mot de passe sécurisé" required>
              </div>
            </div>

            <button type="submit"
              class="w-full py-2 px-4 rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-pink-600 to-pink-800 hover:from-pink-700 hover:to-pink-900 transition-all duration-200"
              :class="{ 'opacity-75 cursor-not-allowed': isAuthenticating }" :disabled="isAuthenticating">
              {{ isAuthenticating ? 'Inscription en cours...' : 'S\'inscrire' }}
            </button>
          </form>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 text-center text-xs text-gray-500">
          © 2025 STINDEL Thomas - Tous droits réservés
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.bg-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23db2777' fill-opacity='0.3' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>
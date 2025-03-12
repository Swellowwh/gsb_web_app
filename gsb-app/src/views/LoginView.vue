<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/user';
import { useNotificationService } from '@/services/notification/notification';
const { NotifSuccess } = useNotificationService();

const userStore = useUserStore();
const router = useRouter();

const isLoggedIn = ref(false);
const isAuthenticating = ref(false);
const showLoginForm = ref(true);

const username = ref('');
const password = ref('');
const registerUsername = ref('');
const registerPassword = ref('');

const errorMessage = ref('');
const successMessage = ref('');

const showNotification = (message) => {
  successMessage.value = message;
  setTimeout(() => {
    successMessage.value = '';
  }, 3000);
};

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

    userStore.setUser(data.user);
    NotifSuccess('Connexion réussie');

    router.push('/');
  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    isAuthenticating.value = false;
  }
};

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

    NotifSuccess('Utilisateur créé avec succès');
    showLoginForm.value = true;
    username.value = registerUsername.value;
  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    isAuthenticating.value = false;
  }
};

const toggleForm = (isLogin) => {
  showLoginForm.value = isLogin;
  errorMessage.value = '';
};
</script>

<template>
  <div class="w-full h-screen flex items-center justify-center p-4 bg-gray-50 relative">
    <div class="absolute inset-0 bg-pattern opacity-10"></div>

    <div v-if="successMessage" class="absolute top-5 right-5 bg-indigo-600 text-white px-4 py-3 rounded-lg shadow-md z-50 flex items-center transition-all duration-300 transform hover:scale-105">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      {{ successMessage }}
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden transition-all duration-300 max-w-lg w-full relative z-10 border border-indigo-100">
      <div class="pt-8 pb-4 flex flex-col items-center">
        <div class="w-20 h-20 rounded-full bg-indigo-600 flex items-center justify-center mb-4 shadow-lg">
          <span class="text-white text-2xl font-bold">GSB</span>
        </div>
      </div>
      
      <div v-if="!isLoggedIn">
        <div class="flex bg-white border-b">
          <button @click="toggleForm(true)" class="flex-1 py-3 font-medium transition-colors duration-200"
            :class="showLoginForm ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:text-indigo-500'">
            Connexion
          </button>
          <button @click="toggleForm(false)" class="flex-1 py-3 font-medium transition-colors duration-200"
            :class="!showLoginForm ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:text-indigo-500'">
            Inscription
          </button>
        </div>

        <div v-if="errorMessage" class="bg-red-50 border-l-4 border-indigo-500 text-indigo-700 p-4" role="alert">
          <div class="flex">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p>{{ errorMessage }}</p>
          </div>
        </div>

        <div v-if="showLoginForm" class="p-6">
          <form @submit.prevent="login" class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <input type="text" v-model="username"
                  class="pl-10 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  placeholder="Votre nom d'utilisateur" required>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input type="password" v-model="password"
                  class="pl-10 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  placeholder="Votre mot de passe" required>
              </div>
            </div>

            <button type="submit"
              class="w-full py-3 px-4 rounded-lg shadow-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-all duration-200 transform hover:translate-y-px"
              :class="{ 'opacity-75 cursor-not-allowed': isAuthenticating }" :disabled="isAuthenticating">
              <div class="flex items-center justify-center">
                <svg v-if="isAuthenticating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isAuthenticating ? 'Connexion en cours...' : 'Se connecter' }}
              </div>
            </button>
          </form>
        </div>

        <div v-else class="p-6">
          <form @submit.prevent="register" class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <input type="text" v-model="registerUsername"
                  class="pl-10 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  placeholder="Choisissez un nom d'utilisateur" required>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input type="password" v-model="registerPassword"
                  class="pl-10 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  placeholder="Créez un mot de passe sécurisé" required>
              </div>
            </div>

            <button type="submit"
              class="w-full py-3 px-4 rounded-lg shadow-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-all duration-200 transform hover:translate-y-px"
              :class="{ 'opacity-75 cursor-not-allowed': isAuthenticating }" :disabled="isAuthenticating">
              <div class="flex items-center justify-center">
                <svg v-if="isAuthenticating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isAuthenticating ? 'Inscription en cours...' : 'S\'inscrire' }}
              </div>
            </button>
          </form>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 text-center text-xs text-gray-500 border-t">
          © 2025 STINDEL Thomas - Tous droits réservés
        </div>
      </div>
    </div>
  </div>
</template>
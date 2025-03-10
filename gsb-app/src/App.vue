<script setup>
import { onMounted, provide, ref, watch } from 'vue';
import { useUserStore } from '@/stores/user';
import { useRouter } from 'vue-router';

const userStore = useUserStore();
const router = useRouter();

const isLoggedIn = ref(false);
const userData = ref(null);
const isLoading = ref(true);

// Cette fonction vérifie la session et charge les données utilisateur
const checkSession = async () => {
  try {
    isLoading.value = true;
    
    const response = await fetch("http://51.83.76.210/gsb/backend/checkSession.php", {
      method: "POST",
      credentials: "include",
    });

    const data = await response.json();

    if (data.success) {
      isLoggedIn.value = true;
      
      if (data.token_data && data.token_data.verified_data) {
        userStore.setUser(data.token_data.verified_data);
      }
      
      return true;
    } else {
      isLoggedIn.value = false;
      userData.value = null;
      // Redirection vers login si la session n'est pas valide
      router.push('/login');
      return false;
    }
  } catch (error) {
    console.error("Erreur de vérification de session :", error);
    isLoggedIn.value = false;
    userData.value = null;
    // Redirection vers login en cas d'erreur
    router.push('/login');
    return false;
  } finally {
    isLoading.value = false;
  }
};

// Vérifier la session au chargement de l'application
onMounted(async () => {
  await checkSession();
});

// Surveiller les changements d'état de connexion
watch(isLoggedIn, (newValue) => {
  if (!newValue && !isLoading.value) {
    // Rediriger vers login si l'utilisateur se déconnecte
    router.push('/login');
  }
});

// Rendre ces valeurs disponibles à tous les composants enfants
provide('isLoggedIn', isLoggedIn);
provide('userData', userData);
provide('isLoading', isLoading);
provide('checkSession', checkSession);
</script>

<template>
  <div v-if="isLoading" class="loading-container">
    <div class="loading-spinner"></div>
  </div>
  <RouterView v-else />
</template>

<style scoped>
.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  width: 100vw;
}

.loading-spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top: 4px solid #3498db;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
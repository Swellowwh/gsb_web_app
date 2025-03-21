<script setup>
import { onMounted, provide, ref, watch } from 'vue';
import { useUserStore } from '@/stores/user';
import { useRouter } from 'vue-router';
import { useTauxFraisStore } from '@/stores/tauxFrais.js';
import Notification from '@/components/Notification.vue';

const tauxStore = useTauxFraisStore();
const userStore = useUserStore();
const router = useRouter();

const isLoggedIn = ref(false);
const userData = ref(null);
const isLoading = ref(true);

const minLoadingTime = 3000;
let loadingStartTime;

const checkSession = async () => {
  try {
    isLoading.value = true;
    loadingStartTime = Date.now();

    const response = await fetch("http://51.83.76.210/gsb/api/checkSession.php", {
      method: "POST",
      credentials: "include",
    });

    const data = await response.json();

    if (data.success) {
      isLoggedIn.value = true;

      if (data.token_data && data.token_data.verified_data) {
        userStore.setUser(data.token_data.verified_data);
      }

      if (data.taux_frais) {
        tauxStore.setTaux(data.taux_frais);
      }

      return true;
    } else {
      isLoggedIn.value = false;
      userData.value = null;

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
    // Calculer le temps écoulé depuis le début du chargement
    const elapsedTime = Date.now() - loadingStartTime;
    const remainingTime = Math.max(0, minLoadingTime - elapsedTime);

    // Si le temps minimum n'est pas atteint, attendre avant de masquer le loader
    if (remainingTime > 0) {
      setTimeout(() => {
        isLoading.value = false;
      }, remainingTime);
    } else {
      isLoading.value = false;
    }
  }
};

onMounted(async () => {
  await checkSession();
});

// Surveiller les changements d'état de connexion
watch(isLoggedIn, (newValue) => {
  if (!newValue && !isLoading.value) {
    router.push('/login');
  }
});

provide('isLoggedIn', isLoggedIn);
provide('userData', userData);
provide('isLoading', isLoading);
provide('checkSession', checkSession);
</script>

<template>
  <div v-if="isLoading" class="loading-container">
    <div class="three-layer-loader">
      <div class="layer layer-1"></div>
      <div class="layer layer-2"></div>
      <div class="layer layer-3"></div>
    </div>
  </div>
  <RouterView v-else />
  <Notification />
</template>

<style scoped>
.loading-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
  width: 100vw;
  background-color: #f5f5f5;
}

.three-layer-loader {
  position: relative;
  width: 100px;
  height: 100px;
}

.layer {
  position: absolute;
  border-radius: 50%;
  border: 4px solid transparent;
  box-sizing: border-box;
}

.layer-1 {
  width: 100%;
  height: 100%;
  border-top-color: #4f46e5;
  border-right-color: #4f46e5;
  animation: spin1 1.5s linear infinite;
}

.layer-2 {
  width: 70%;
  height: 70%;
  top: 15%;
  left: 15%;
  border-top-color: #6366f1;
  border-left-color: #6366f1;
  animation: spin2 1.8s linear infinite;
}

.layer-3 {
  width: 40%;
  height: 40%;
  top: 30%;
  left: 30%;
  border-top-color: #818cf8;
  border-bottom-color: #818cf8;
  animation: spin3 2.1s linear infinite;
}

@keyframes spin1 {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes spin2 {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(-360deg); }
}

@keyframes spin3 {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
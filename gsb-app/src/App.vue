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
    <div class="gsb-title">
      <span>G</span>
      <span>S</span>
      <span>B</span>
    </div>
    <div class="loader"></div>
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

.gsb-title {
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
  letter-spacing: 0.1em;
  color: #4f46e5;
  display: flex;
  gap: 0.5rem;
}

.gsb-title span {
  display: inline-block;
  animation: letterFloat 2s infinite ease-in-out;
}

.gsb-title span:nth-child(1) {
  animation-delay: 0s;
}

.gsb-title span:nth-child(2) {
  animation-delay: 0.2s;
}

.gsb-title span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes letterFloat {

  0%,
  100% {
    transform: translateY(0) scale(1);
    text-shadow: 0 0 0 rgba(79, 70, 229, 0);
  }

  50% {
    transform: translateY(-10px) scale(1.1);
    text-shadow: 0 10px 10px rgba(79, 70, 229, 0.2);
  }
}

.loader {
  width: 90px;
  height: 40px;
  --g: radial-gradient(circle, #0000 60%, #4f46e5 62%, #6366f1 68%, #0000 72%) no-repeat;
  background: var(--g), var(--g), var(--g);
  background-size: 24px 24px;
  position: relative;
  animation: loaderAnimation 1.2s infinite ease-in-out;
}

@keyframes loaderAnimation {
  0% {
    background-position: 0 50%, 50% 50%, 100% 50%;
    transform: scale(0.95);
  }

  20% {
    background-position: 0 30%, 50% 50%, 100% 50%;
    transform: scale(1);
  }

  40% {
    background-position: 0 70%, 50% 30%, 100% 50%;
    transform: scale(0.98);
  }

  60% {
    background-position: 0 50%, 50% 70%, 100% 30%;
    transform: scale(1);
  }

  80% {
    background-position: 0 50%, 50% 50%, 100% 70%;
    transform: scale(0.95);
  }

  100% {
    background-position: 0 50%, 50% 50%, 100% 50%;
    transform: scale(1);
  }
}
</style>
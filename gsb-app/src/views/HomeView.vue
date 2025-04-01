<script setup>
import Sidebar from '@/components/Sidebar.vue';
import NoRecrut from '@/views/NoRecrut.vue';
import { useUserStore } from '@/stores/user';
import { onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

const userStore = useUserStore();
const router = useRouter();

// Rôle utilisateur
const role = computed(() => userStore.userData?.role?.toUpperCase());
const isUtilisateur = computed(() => role.value === 'UTILISATEUR');

onMounted(() => {
  const currentPath = router.currentRoute.value.path;

  if (currentPath === '/' && !isUtilisateur.value) {
    if (role.value === 'ADMINISTRATEUR') {
      router.push('/employees');
    } else if (role.value === 'COMPTABLE') {
      router.push('/payments');
    } else if (role.value === 'VISITEUR_MEDICAL') {
      router.push('/frais');
    }
  }
});
</script>

<template>
  <div class="flex h-screen overflow-hidden">
    <div v-if="!isUtilisateur" class="hidden md:block md:w-64 shrink-0 h-screen">
      <Sidebar />
    </div>

    <div class="flex-1 flex flex-col overflow-auto">
      <div class="w-full mx-auto">
        <NoRecrut v-if="isUtilisateur" />
        <router-view v-else />
      </div>
    </div>
  </div>
</template>

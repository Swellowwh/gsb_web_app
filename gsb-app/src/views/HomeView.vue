// src/App.vue
<script setup>
import Sidebar from '@/components/Sidebar.vue';
import { useUserStore } from '@/stores/user';
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';

const userStore = useUserStore();
const router = useRouter();

onMounted(() => {
  if (router.currentRoute.value.path === '/') {
    if (userStore.userData.role === 'ADMINISTRATEUR') {
      router.push('/employees');
    } else if (userStore.userData.role === 'COMPTABLE') {
      router.push('/payments');
    } else {
      router.push('/frais');
    }
  }
});
</script>

<template>
  <div class="flex h-screen overflow-hidden">
    <div class="hidden md:block md:w-64 shrink-0 h-screen">
      <Sidebar />
    </div>

    <div class="flex-1 flex flex-col overflow-auto">
      <div class="w-full mx-auto">
        <router-view />
      </div>
    </div>
  </div>
</template>
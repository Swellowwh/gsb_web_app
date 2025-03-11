<script setup>
import Sidebar from '@/components/Sidebar.vue';
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
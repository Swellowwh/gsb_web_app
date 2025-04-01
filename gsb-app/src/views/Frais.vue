<script setup>
import FormGlobal from '@/components/FormAddFiche.vue';

const getCurrentPeriod = () => {
  const now = new Date();
  const months = [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
  ];
  const month = months[now.getMonth()];
  const year = now.getFullYear();

  return `${month} ${year}`;
};
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <main class="pt-10 mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center pb-4 border-b border-gray-200">
          <h1 class="text-xl font-semibold text-gray-900">Gestion des frais</h1>
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500">Période actuelle:</span>
            <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium">
              {{ getCurrentPeriod() }}
            </span>
          </div>
        </div>

        <div class="mt-8 bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
          <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg text-white flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
            </svg>
              Nouvelle fiche de frais
            </h3>
          </div>
        
          <FormGlobal />
        </div>
      </div>
    </main>
  </div>
</template>
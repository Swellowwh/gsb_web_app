<script setup>
import FormKm from '@/components/FormKm.vue';
import FormRepas from '@/components/FormRepas.vue';
import FormHebergement from '@/components/FormHebergement.vue';

import { ref } from 'vue';

const viewFormKm = ref(true);
const viewFormRepas = ref(false);
const viewFormHebergement = ref(false);

const switchForm = (formName) => {
    viewFormKm.value = false;
    viewFormRepas.value = false;
    viewFormHebergement.value = false;

    switch (formName) {
        case 'km':
            viewFormKm.value = true;
            break;
        case 'repas':
            viewFormRepas.value = true;
            break;
        case 'hebergement':
            viewFormHebergement.value = true;
            break;
    }
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
            <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium">Mars 2025</span>
          </div>
        </div>

        <!-- Carte pour les formulaires -->
        <div class="mt-8 bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
          <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Nouvelle note de frais
            </h3>
          </div>

          <div class="p-6">
            <!-- Onglets de navigation -->
            <div class="flex border-b border-gray-200 mb-6">
              <button @click="switchForm('km')"
                :class="[viewFormKm ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700', 'px-4 py-2 font-medium text-sm']">
                Frais kilométriques
              </button>
              <button @click="switchForm('repas')"
                :class="[viewFormRepas ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700', 'px-4 py-2 font-medium text-sm']">
                Repas
              </button>
              <button @click="switchForm('hebergement')"
                :class="[viewFormHebergement ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700', 'px-4 py-2 font-medium text-sm']">
                Hébergement
              </button>
            </div>

            <FormKm v-if="viewFormKm && !viewFormRepas && !viewFormHebergement" />
            <FormRepas v-if="!viewFormKm && viewFormRepas && !viewFormHebergement" />
            <FormHebergement v-if="!viewFormKm && !viewFormRepas && viewFormHebergement" />
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
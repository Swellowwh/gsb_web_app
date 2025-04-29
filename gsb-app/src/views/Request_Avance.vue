<script setup>
import { ref, computed } from 'vue';
import { useNotificationService } from '@/services/notification/notification';

const { NotifSuccess, NotifError } = useNotificationService();

const date = ref('');
const montant = ref('');
const motif = ref('');
const isLoading = ref(false);

const montantValide = computed(() => {
    const valeur = parseFloat(montant.value);
    return valeur > 0;
});

const formulaireValide = computed(() => {
    return date.value && montantValide.value && motif.value;
});

const submitFormAvance = async () => {
    if (isLoading.value) return;

    isLoading.value = true;
    try {
        const response = await fetch("http://51.83.76.210/gsb/api/addAvance.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                date: date.value,
                montant: parseFloat(montant.value),
                motif: motif.value
            }),
            credentials: "include"
        });

        const responseText = await response.text();
        const data = JSON.parse(responseText);

        if (!response.ok) {
            throw new Error(data.message || response.statusText);
        }

        setTimeout(() => {
            NotifSuccess(`Demande d'avance envoyée avec succès`);
            reinitialiserFormulaire();
            isLoading.value = false;
        }, 1000);

    } catch (err) {
        setTimeout(() => {
            NotifError(err.message);
            isLoading.value = false;
        }, 1000);
    }
};

const reinitialiserFormulaire = () => {
    date.value = '';
    montant.value = '';
    motif.value = '';
};
</script>

<template>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex items-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Date de la demande d'avance</span>
            </div>
            <div class="relative">
                <input type="date" v-model="date" required
                    class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
        </div>
        
        <form class="space-y-6">
            <div class="p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="font-medium text-gray-800">Montant de l'avance</h3>
                </div>
                
                <div class="relative rounded-lg shadow-sm">
                    <input type="number" v-model="montant" step="0.01" min="0"
                        class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Montant demandé">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">€</span>
                    </div>
                </div>
                <div v-if="montant && !montantValide" class="mt-2 text-sm text-red-600">
                    Le montant doit être supérieur à 0€
                </div>
            </div>
            
            <div class="p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <h3 class="font-medium text-gray-800">Motif de la demande</h3>
                </div>
                
                <textarea v-model="motif" rows="4" required
                    class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Veuillez préciser le motif de votre demande d'avance..."></textarea>
            </div>

            <div v-if="montantValide && montant" class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                <div class="flex items-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium text-gray-700">Récapitulatif de votre demande</span>
                </div>
                <div class="pt-2 flex justify-between items-center">
                    <span class="font-medium text-gray-800">Montant de l'avance</span>
                    <span class="text-xl font-bold text-indigo-600">{{ parseFloat(montant).toFixed(2) }}€</span>
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-4">
                <button type="button" @click="reinitialiserFormulaire"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    :disabled="isLoading">
                    Annuler
                </button>
                <button type="submit" @click.prevent="submitFormAvance()"
                    class="relative px-4 py-2 border border-transparent rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center justify-center min-w-[150px] transition-all duration-200"
                    :disabled="isLoading || !formulaireValide" 
                    :class="{ 'opacity-75': isLoading }">
                    <span v-if="!isLoading" class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Soumettre la demande
                    </span>
                    <span v-else class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Traitement en cours...
                    </span>
                </button>
            </div>
        </form>
    </div>
</template>
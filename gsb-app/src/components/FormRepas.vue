<script setup>
import { ref, computed } from 'vue';
import { useNotificationService } from '@/services/notification/notification';
import { useTauxFraisStore } from '@/stores/tauxFrais';

const { NotifSuccess, NotifError } = useNotificationService();
const tauxFraisStore = useTauxFraisStore();

const formRepas = ref({
    date: '',
    description: '',
    nombreRepas: ''
});

const isLoading = ref(false);

const champsRemplis = computed(() => {
    return formRepas.value.date && 
           formRepas.value.description && 
           formRepas.value.nombreRepas;
});

const tauxFormatted = computed(() => {
    return tauxFraisStore.tauxFrais.REP.taux.toFixed(2);
});

const montantTotal = computed(() => {
    return formRepas.value.nombreRepas ? 
        (formRepas.value.nombreRepas * tauxFraisStore.tauxFrais.REP.taux).toFixed(2) : 
        "0.00";
});

const reinitialiserFormulaire = () => {
    formRepas.value = {
        date: '',
        description: '',
        nombreRepas: ''
    };
};

const submitFormRepas = async () => {
    if (isLoading.value) return;

    if (!champsRemplis.value) {
        NotifError('Veuillez remplir tous les champs obligatoires');
        return;
    }

    isLoading.value = true;
    try {
        const response = await fetch("http://51.83.76.210/gsb/backend/addFormRepas.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                date: formRepas.value.date,
                nombreRepas: formRepas.value.nombreRepas,
                description: formRepas.value.description
            }),
            credentials: "include"
        });

        const responseText = await response.text();
        const data = JSON.parse(responseText);

        if (!response.ok) {
            throw new Error(data.message || response.statusText);
        }

        setTimeout(() => {
            NotifSuccess(`Frais de repas ajouté avec succès`);
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
</script>

<template>
    <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Date du repas</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <input type="date" v-model="formRepas.date"
                    class="pl-10 w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Nombre de repas</label>
            <div class="relative rounded-lg shadow-sm">
                <input type="number" min="1" step="1" v-model="formRepas.nombreRepas"
                    class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Ex: 1">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">repas</span>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 space-y-2">
            <label class="block text-sm font-medium text-gray-700">Contexte du repas</label>
            <textarea v-model="formRepas.description" rows="3"
                class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                placeholder="Contexte du repas (visite client, formation, réunion d'équipe, etc.)"></textarea>
        </div>

        <div v-if="formRepas.nombreRepas" class="md:col-span-2 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex items-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Détails du remboursement</span>
            </div>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Nombre de repas: {{ formRepas.nombreRepas }}</p>
                    <p class="text-sm text-gray-600">Taux par repas: {{ tauxFormatted }}€</p>
                </div>
                <div class="text-xl font-bold text-indigo-600">
                    {{ montantTotal }}€
                </div>
            </div>
        </div>

        <div class="md:col-span-2 flex justify-end space-x-4 pt-4">
            <button type="button" @click="reinitialiserFormulaire"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                :disabled="isLoading">
                Annuler
            </button>
            <button type="submit" @click.prevent="submitFormRepas()"
                class="relative px-4 py-2 border border-transparent rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center justify-center min-w-[150px] transition-all duration-200"
                :disabled="isLoading" :class="{ 'opacity-75': isLoading }">
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
</template>
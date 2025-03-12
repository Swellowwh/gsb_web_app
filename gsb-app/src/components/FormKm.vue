<script setup>
import { ref } from 'vue';

const date = ref('');
const distance = ref('');
const description = ref('');
const isLoading = ref(false);

const submitFormKm = async () => {
    isLoading.value = true;

    try {
        const response = await fetch("http://51.83.76.210/gsb/backend/addFormKm.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                date: date.value,
                distance: distance.value,
                description: description.value,
            }),
            credentials: "include"
        });

        const responseText = await response.text();
        console.log("Réponse brute:", responseText);

        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error("Erreur de parsing JSON:", parseError);
            throw new Error("La réponse n'est pas un JSON valide");
        }
    } catch (err) {
        console.error("Erreur complète:", err);
        console.error("Impossible de charger les données des visiteurs", { title: "Erreur de connexion" });
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Date du déplacement</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <input type="date" v-model="date"
                    class="pl-10 w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Distance (km)</label>
            <div class="relative rounded-lg shadow-sm">
                <input type="number" v-model="distance"
                    class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Ex: 120">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">km</span>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 space-y-2">
            <label class="block text-sm font-medium text-gray-700">Motif du déplacement</label>
            <textarea v-model="description" rows="3"
                class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                placeholder="Description du déplacement (visite client, formation, etc.)"></textarea>
        </div>

        <div v-if="distance" class="md:col-span-2 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex items-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Estimation du remboursement</span>
            </div>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Distance: {{ distance }} km</p>
                    <p class="text-sm text-gray-600">Taux kilométrique: 0,40€/km</p>
                </div>
                <div class="text-xl font-bold text-indigo-600">
                    {{ (parseFloat(distance) * 0.4).toFixed(2) }}€
                </div>
            </div>
        </div>

        <div class="md:col-span-2 flex justify-end space-x-4 pt-4">
            <button type="button" @click="reinitialiserFormulaire"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Annuler
            </button>
            <button type="submit" @click.prevent="submitFormKm()"
                class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span v-if="isLoading">Chargement...</span>
                <span v-else>Soumettre la demande</span>
            </button>
        </div>
    </form>
</template>
<script setup>
import { ref } from 'vue';

const fraisData = ref({
    date: '',
    distance: '',
    depart: '',
    arrivee: '',
    motif: ''
});

const historiqueData = ref([]);

const reinitialiserFormulaire = () => {
    fraisData.value = {
        date: '',
        distance: '',
        depart: '',
        arrivee: '',
        motif: ''
    };
};

// Soumission du formulaire
const soumettreFormulaire = () => {
    // Validation des champs
    if (!fraisData.value.date || !fraisData.value.distance || !fraisData.value.depart ||
        !fraisData.value.arrivee || !fraisData.value.motif) {
        alert('Veuillez remplir tous les champs');
        return;
    }

    // Créer un nouvel élément d'historique (simulation)
    const nouveauFrais = {
        id: historiqueData.value.length + 1,
        date: fraisData.value.date,
        type: 'Kilométrique',
        description: `${fraisData.value.depart} - ${fraisData.value.arrivee} (${fraisData.value.distance} km)`,
        montant: `${(fraisData.value.distance * 0.4).toFixed(2)} €`,
        statut: 'En attente'
    };

    // Ajouter au début de l'historique
    historiqueData.value.unshift(nouveauFrais);

    // Réinitialiser le formulaire
    reinitialiserFormulaire();
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
                <input type="date" v-model="fraisData.date"
                    class="pl-10 w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Distance (km)</label>
            <div class="relative rounded-lg shadow-sm">
                <input type="number" v-model="fraisData.distance"
                    class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Ex: 120">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">km</span>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Lieu de départ</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <input type="text" v-model="fraisData.depart"
                    class="pl-10 w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Ville de départ">
            </div>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Lieu d'arrivée</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <input type="text" v-model="fraisData.arrivee"
                    class="pl-10 w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Ville d'arrivée">
            </div>
        </div>

        <div class="md:col-span-2 space-y-2">
            <label class="block text-sm font-medium text-gray-700">Motif du déplacement</label>
            <textarea v-model="fraisData.motif" rows="3"
                class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                placeholder="Description du déplacement (visite client, formation, etc.)"></textarea>
        </div>

        <div v-if="fraisData.distance" class="md:col-span-2 bg-gray-50 p-4 rounded-lg border border-gray-200">
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
                    <p class="text-sm text-gray-600">Distance: {{ fraisData.distance }} km</p>
                    <p class="text-sm text-gray-600">Taux kilométrique: 0,40€/km</p>
                </div>
                <div class="text-xl font-bold text-indigo-600">
                    {{ (fraisData.distance * 0.4).toFixed(2) }}€
                </div>
            </div>
        </div>

        <div class="md:col-span-2 flex justify-end space-x-4 pt-4">
            <button type="button" @click="reinitialiserFormulaire"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Annuler
            </button>
            <button type="submit" @click.prevent="soumettreFormulaire"
                class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Soumettre la demande
            </button>
        </div>
    </form>
</template>
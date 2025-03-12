
<script setup>
import { ref, computed } from 'vue';

const fraisData = ref({
    dateArrivee: '',
    dateDepart: '',
    etablissement: '',
    ville: '',
    adresse: '',
    nbNuits: 1,
    montantNuit: '',
    petitDejeuner: false,
    montantPetitDejeuner: '',
    fraisSejour: false,
    montantFraisSejour: '',
    raison: ''
});

// Calcul du montant total
const montantTotal = computed(() => {
    let total = 0;
    // Montant des nuits
    if (fraisData.value.nbNuits && fraisData.value.montantNuit) {
        total += parseFloat(fraisData.value.nbNuits) * parseFloat(fraisData.value.montantNuit);
    }
    
    // Montant des petits déjeuners
    if (fraisData.value.petitDejeuner && fraisData.value.montantPetitDejeuner) {
        total += parseFloat(fraisData.value.nbNuits) * parseFloat(fraisData.value.montantPetitDejeuner);
    }
    
    // Taxe de séjour
    if (fraisData.value.fraisSejour && fraisData.value.montantFraisSejour) {
        total += parseFloat(fraisData.value.nbNuits) * parseFloat(fraisData.value.montantFraisSejour);
    }
    
    return total;
});

// Vérification du dépassement de plafond (120€/nuit)
const depassePlafond = computed(() => {
    if (!fraisData.value.montantNuit) return false;
    return parseFloat(fraisData.value.montantNuit) > 120;
});

const historiqueData = ref([]);

const reinitialiserFormulaire = () => {
    fraisData.value = {
        dateArrivee: '',
        dateDepart: '',
        etablissement: '',
        ville: '',
        adresse: '',
        nbNuits: 1,
        montantNuit: '',
        petitDejeuner: false,
        montantPetitDejeuner: '',
        fraisSejour: false,
        montantFraisSejour: '',
        raison: ''
    };
};

// Soumission du formulaire
const soumettreFormulaire = () => {
    // Validation des champs obligatoires
    if (!fraisData.value.dateArrivee || !fraisData.value.dateDepart || 
        !fraisData.value.etablissement || !fraisData.value.ville ||
        !fraisData.value.nbNuits || !fraisData.value.montantNuit || 
        !fraisData.value.raison) {
        alert('Veuillez remplir tous les champs obligatoires');
        return;
    }

    // Créer un nouvel élément d'historique
    const nouveauFrais = {
        id: historiqueData.value.length + 1,
        date: fraisData.value.dateArrivee,
        type: 'Hébergement',
        description: `${fraisData.value.etablissement} - ${fraisData.value.ville} (${fraisData.value.nbNuits} nuit${fraisData.value.nbNuits > 1 ? 's' : ''})`,
        montant: `${montantTotal.value.toFixed(2)} €`,
        statut: 'En attente'
    };

    // Ajouter au début de l'historique
    historiqueData.value.unshift(nouveauFrais);

    // Réinitialiser le formulaire
    reinitialiserFormulaire();
    
    // Confirmation
    alert('Frais d\'hébergement soumis avec succès');
};

// Calcul automatique du nombre de nuits
const calculerNbNuits = () => {
    if (fraisData.value.dateArrivee && fraisData.value.dateDepart) {
        const dateArrivee = new Date(fraisData.value.dateArrivee);
        const dateDepart = new Date(fraisData.value.dateDepart);
        
        // Différence en millisecondes
        const diffTime = Math.abs(dateDepart - dateArrivee);
        // Conversion en jours
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        fraisData.value.nbNuits = diffDays;
    }
};
</script>

<template>
    <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Date d'arrivée</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <input type="date" v-model="fraisData.dateArrivee" @change="calculerNbNuits"
                    class="pl-10 w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Nombre de nuits</label>
            <input type="number" min="1" v-model="fraisData.nbNuits"
                class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Prix par nuit (€)</label>
            <div class="relative rounded-lg shadow-sm">
                <input type="number" step="0.01" v-model="fraisData.montantNuit"
                    class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Ex: 95.00" 
                    :class="{ 'border-red-300 focus:outline-none focus:border-red-500 focus:ring-red-200': depassePlafond }">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">€</span>
                </div>
            </div>
            <p v-if="depassePlafond" class="mt-1 text-xs text-red-600">
                Ce montant dépasse le plafond autorisé de 120€ par nuit.
            </p>
        </div>

        <div class="space-y-4">
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="frais-sejour" type="checkbox" v-model="fraisData.fraisSejour"
                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="ml-3 text-sm">
                    <label for="frais-sejour" class="font-medium text-gray-700">Taxe de séjour</label>
                </div>
            </div>
            
            <div v-if="fraisData.fraisSejour" class="pl-7">
                <label class="block text-sm font-medium text-gray-700">Montant par nuit (€)</label>
                <div class="relative rounded-lg shadow-sm">
                    <input type="number" step="0.01" v-model="fraisData.montantFraisSejour"
                        class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Ex: 2.50">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">€</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 space-y-2">
            <label class="block text-sm font-medium text-gray-700">Motif du séjour</label>
            <textarea v-model="fraisData.raison" rows="3"
                class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                placeholder="Raison du déplacement (visite client, salon professionnel, formation, etc.)"></textarea>
        </div>

        <div v-if="montantTotal > 0" class="md:col-span-2 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex items-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Récapitulatif du séjour</span>
            </div>
            
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span>Hébergement ({{ fraisData.nbNuits }} nuit{{ fraisData.nbNuits > 1 ? 's' : '' }} × {{ parseFloat(fraisData.montantNuit).toFixed(2) }}€)</span>
                    <span>{{ (parseFloat(fraisData.nbNuits) * parseFloat(fraisData.montantNuit)).toFixed(2) }}€</span>
                </div>
                
                <div v-if="fraisData.petitDejeuner && fraisData.montantPetitDejeuner" class="flex justify-between text-sm">
                    <span>Petit-déjeuner ({{ fraisData.nbNuits }} × {{ parseFloat(fraisData.montantPetitDejeuner).toFixed(2) }}€)</span>
                    <span>{{ (parseFloat(fraisData.nbNuits) * parseFloat(fraisData.montantPetitDejeuner)).toFixed(2) }}€</span>
                </div>
                
                <div v-if="fraisData.fraisSejour && fraisData.montantFraisSejour" class="flex justify-between text-sm">
                    <span>Taxe de séjour ({{ fraisData.nbNuits }} × {{ parseFloat(fraisData.montantFraisSejour).toFixed(2) }}€)</span>
                    <span>{{ (parseFloat(fraisData.nbNuits) * parseFloat(fraisData.montantFraisSejour)).toFixed(2) }}€</span>
                </div>
                
                <div class="border-t border-gray-200 pt-2 flex justify-between font-medium">
                    <span>Total</span>
                    <span :class="depassePlafond ? 'text-red-600' : 'text-indigo-600'">{{ montantTotal.toFixed(2) }}€</span>
                </div>
            </div>
            
            <div v-if="depassePlafond" class="mt-2 text-xs text-red-600">
                Attention : le prix par nuit dépasse le plafond autorisé de 120€. Une justification pourrait être demandée.
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
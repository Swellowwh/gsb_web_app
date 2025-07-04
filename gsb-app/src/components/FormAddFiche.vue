<script setup>
import { ref, computed, onMounted } from 'vue';
import { useNotificationService } from '@/services/notification/notification';
import { useTauxFraisStore } from '@/stores/tauxFrais';
const { NotifSuccess, NotifError } = useNotificationService();

const tauxFraisStore = useTauxFraisStore();

const date = ref('');
const distance = ref('');
const nbRepas = ref('');
const nbHebergements = ref('');
const description = ref('');
const isLoading = ref(false);

const avances = ref([]);
const selectedAvanceId = ref('');

const selectedAvance = computed(() => {

  if (!selectedAvanceId.value) return null;
  
  // Si un ID est sélectionné, cherche l'objet avance correspondant dans le tableau avances
  // La méthode find() retourne le premier élément qui satisfait la condition

  return avances.value.find(avance => avance.id === selectedAvanceId.value);
});

const montantKm = computed(() => {
    return distance.value ? distance.value * tauxFraisStore.tauxFrais.KM.taux : 0;
});

const montantRepas = computed(() => {
    return nbRepas.value ? nbRepas.value * tauxFraisStore.tauxFrais.REP.taux : 0;
});

const montantHebergement = computed(() => {
    return nbHebergements.value ? nbHebergements.value * tauxFraisStore.tauxFrais.NUI.taux : 0;
});

const montantTotal = computed(() => {
    return montantKm.value + montantRepas.value + montantHebergement.value;
});

onMounted(() => {
    loadAvance();
});

const submitFormFrais = async () => {
    if (isLoading.value) return;

    isLoading.value = true;
    try {
        const response = await fetch("http://51.83.76.210/gsb/api/addFicheFrais.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                date: date.value,
                distance: distance.value || 0,
                nbRepas: nbRepas.value || 0,
                nbHebergements: nbHebergements.value || 0,
                description: description.value,
                avance_id: selectedAvanceId.value || null
            }),
            credentials: "include"
        });

        const responseText = await response.text();
        const data = JSON.parse(responseText);

        if (!response.ok) {
            throw new Error(data.message || response.statusText);
        }

        setTimeout(() => {
            NotifSuccess(`Fiche de frais ajoutée avec succès`);
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

const loadAvance = async () => {
    isLoading.value = true;
    try {
        const response = await fetch("http://51.83.76.210/gsb/api/LoadAvance.php", {
            method: "POST",
            credentials: "include"
        });

        if (!response.ok) {
            throw new Error("Erreur lors du chargement des avances");
        }

        const data = await response.json();

        if (data.success) {
            // Convertir les montants en nombre et assurer qu'ils sont utilisables
            avances.value = data.avances.map(avance => ({
                ...avance,
                montant: parseFloat(avance.montant) // S'assurer que le montant est un nombre
            }));
            console.log("Avances chargées:", avances.value);
        } else {
            throw new Error(data.message || "Erreur lors du chargement des avances");
        }
    } catch (error) {
        NotifError(error.message);
        console.error("Erreur de chargement des avances:", error);
    } finally {
        isLoading.value = false;
    }
};

const reinitialiserFormulaire = () => {
    date.value = '';
    distance.value = '';
    nbRepas.value = '';
    nbHebergements.value = '';
    description.value = '';
    selectedAvanceId.value = '';
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
                <span class="text-sm font-medium text-gray-700">Date de la fiche de frais</span>
            </div>
            <div class="relative">
                <input type="date" v-model="date" required
                    class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
        </div>

        <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex items-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Avances disponibles</span>
            </div>




            <div class="relative">
                <select v-model="selectedAvanceId"
                    class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white">
                    <option value="">Aucune avance sélectionnée</option>
                    <option v-for="avance in avances" :key="avance.id" :value="avance.id">
                        {{ new Date(avance.date).toLocaleDateString() }} - {{ avance.montant.toFixed(2) }}€
                    </option>
                </select>
            </div>




            <div v-if="avances.length === 0 && !isLoading" class="mt-2 text-sm text-center text-gray-600">
                Aucune avance disponible
            </div>
            <div v-if="isLoading" class="mt-2 text-sm text-center text-gray-600">
                Chargement des avances...
            </div>
        </div>

        <form class="space-y-6">
            <div class="p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <h3 class="font-medium text-gray-800">Kilométrage</h3>
                </div>

                <div class="relative rounded-lg shadow-sm">
                    <input type="number" v-model="distance"
                        class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Nombre de kilomètres">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">km</span>
                    </div>
                </div>
                <div v-if="distance" class="mt-2 text-sm text-right text-gray-600">
                    {{ distance }} km × {{ tauxFraisStore.tauxFrais.KM.taux }}€ = <span
                        class="font-medium text-indigo-600">{{ montantKm.toFixed(2) }}€</span>
                </div>
            </div>

            <!-- Repas -->
            <div class="p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <h3 class="font-medium text-gray-800">Repas</h3>
                </div>

                <div class="relative rounded-lg shadow-sm">
                    <input type="number" v-model="nbRepas"
                        class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Nombre de repas">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">repas</span>
                    </div>
                </div>
                <div v-if="nbRepas" class="mt-2 text-sm text-right text-gray-600">
                    {{ nbRepas }} repas × {{ tauxFraisStore.tauxFrais.REP.taux }}€ = <span
                        class="font-medium text-indigo-600">{{ montantRepas.toFixed(2) }}€</span>
                </div>
            </div>

            <div class="p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <h3 class="font-medium text-gray-800">Hébergement</h3>
                </div>

                <div class="relative rounded-lg shadow-sm">
                    <input type="number" v-model="nbHebergements"
                        class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Nombre de nuitées">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">nuits</span>
                    </div>
                </div>
                <div v-if="nbHebergements" class="mt-2 text-sm text-right text-gray-600">
                    {{ nbHebergements }} nuits × {{ tauxFraisStore.tauxFrais.NUI.taux }}€ = <span
                        class="font-medium text-indigo-600">{{ montantHebergement.toFixed(2) }}€</span>
                </div>
            </div>

            <div class="p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <h3 class="font-medium text-gray-800">Description</h3>
                </div>

                <textarea v-model="description" rows="3" required
                    class="w-full p-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Motif de la mission/déplacement"></textarea>
            </div>

            <!-- Résumé des frais -->
            <div v-if="montantTotal > 0 || selectedAvance" class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                <div class="flex items-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium text-gray-700">Récapitulatif</span>
                </div>
                <div class="space-y-2">
                    <div v-if="distance" class="flex justify-between text-sm">
                        <span class="text-gray-600">Kilométrage ({{ distance }} km × {{ tauxFraisStore.tauxFrais.KM.taux
                            }}€)</span>
                        <span class="font-medium">{{ montantKm.toFixed(2) }}€</span>
                    </div>
                    <div v-if="nbRepas" class="flex justify-between text-sm">
                        <span class="text-gray-600">Repas ({{ nbRepas }} × {{ tauxFraisStore.tauxFrais.REP.taux
                            }}€)</span>
                        <span class="font-medium">{{ montantRepas.toFixed(2) }}€</span>
                    </div>
                    <div v-if="nbHebergements" class="flex justify-between text-sm">
                        <span class="text-gray-600">Hébergement ({{ nbHebergements }} × {{
                            tauxFraisStore.tauxFrais.NUI.taux }}€)</span>
                        <span class="font-medium">{{ montantHebergement.toFixed(2) }}€</span>
                    </div>
                    <div v-if="montantTotal > 0" class="flex justify-between text-sm font-medium">
                        <span class="text-gray-700">Sous-total des frais</span>
                        <span class="text-indigo-600">{{ montantTotal.toFixed(2) }}€</span>
                    </div>



                    <div v-if="selectedAvance"
                        class="flex justify-between text-sm mt-1 pt-1 border-t border-indigo-100">
                        <span class="text-gray-600">Avance reçue ({{ new Date(selectedAvance.date).toLocaleDateString()
                            }})</span>
                        <span class="font-medium text-orange-600">{{ selectedAvance.montant.toFixed(2) }}€</span>
                    </div>

                    <div class="pt-2 mt-2 border-t border-indigo-200 flex justify-between items-center">
                        <span class="font-medium text-gray-800">
                            {{ (montantTotal - (selectedAvance ? selectedAvance.montant : 0)) >= 0 ? 'Montant à rembourser' : 'Montant à restituer' }}
                        </span>
                        <span class="text-xl font-bold text-indigo-600">
                            {{ Math.abs(montantTotal - (selectedAvance ? selectedAvance.montant : 0)).toFixed(2) }}€
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-4">
                <button type="button" @click="reinitialiserFormulaire"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    :disabled="isLoading">
                    Annuler
                </button>
                <button type="submit" @click.prevent="submitFormFrais()"
                    class="relative px-4 py-2 border border-transparent rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center justify-center min-w-[150px] transition-all duration-200"
                    :disabled="isLoading || (!distance && !nbRepas && !nbHebergements) || !date || !description"
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
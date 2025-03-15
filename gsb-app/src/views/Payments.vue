<script setup>
import { ref, onMounted, computed } from 'vue';
import { useNotificationService } from '@/services/notification/notification';
const { NotifSuccess } = useNotificationService();

const historiqueData = ref([]);
const isLoading = ref(true);
const error = ref(null);

function formatDate(stringDate) {
  if (!stringDate) return '';

  const date = new Date(stringDate);
  return date.toLocaleDateString('fr-FR');
}

function parseMontant(montant) {
  if (typeof montant === 'number') {
    return montant;
  }

  return parseFloat(
    montant.toString()
      .replace('€', '')
      .replace(',', '.')
      .trim()
  );
}

const totalDepenses = computed(() => {
  if (!historiqueData.value.length) return "0,00 €";

  const total = historiqueData.value.reduce((sum, item) => {
    return sum + parseMontant(item.montant);
  }, 0);

  return total.toFixed(2) + " €";
});

const totalValidees = computed(() => {
  if (!historiqueData.value.length) return "0,00 €";

  const total = historiqueData.value
    .filter(item => item.statut === 'Accepted')
    .reduce((sum, item) => {
      return sum + parseMontant(item.montant);
    }, 0);

  return total.toFixed(2) + " €";
});

const totalEnAttente = computed(() => {
  if (!historiqueData.value.length) return "0,00 €";

  const total = historiqueData.value
    .filter(item => item.statut === 'Pending')
    .reduce((sum, item) => {
      return sum + parseMontant(item.montant);
    }, 0);

  return total.toFixed(2) + " €";
});

const totalRefusees = computed(() => {
  if (!historiqueData.value.length) return "0,00 €";

  const total = historiqueData.value
    .filter(item => item.statut === 'Rejected')
    .reduce((sum, item) => {
      return sum + parseMontant(item.montant);
    }, 0);

  return total.toFixed(2) + " €";
});

function getTypeLabel(type) {
  if (type === 'Km') {
    return 'Transport';
  }
  return type;
}

function getTypeIconClass(type) {
  switch (type) {
    case 'Repas': return 'bg-green-100 text-green-600';
    case 'Hebergement': return 'bg-purple-100 text-purple-600';
    case 'Km': return 'bg-blue-100 text-blue-600';
    default: return 'bg-gray-100 text-gray-600';
  }
}

function getTypeStatut(statut) {
  switch (statut) {
    case 'Accepted': return 'Accepté';
    case 'Pending': return 'En attente';
    case 'Rejected': return 'Refusé';
    default: return statut;
  }
}

function getStatusClass(status) {
  switch (status) {
    case 'Accepted': return 'bg-green-100 text-green-800';
    case 'Pending': return 'bg-yellow-100 text-yellow-800';
    case 'Rejected': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
}

function getTypeIconSvg(type) {
  switch (type) {
    case 'Kilométrique':
      return `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />`;
    case 'Repas':
      return `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />`;
    case 'Hebergement':
      return `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />`;
    case 'Km':
      return `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />`;
    default:
      return `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />`;
  }
}

async function traiterPaiement(id, action) {
  try {
    isLoading.value = true;

    const url = 'http://51.83.76.210/gsb/api/processingHistory.php';

    const payload = {
      idHistory: id,
      processingHistory: action
    };

    await new Promise(resolve => setTimeout(resolve, 500));

    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(payload)
    });

    if (response.ok) {
      const data = await response.json();

      if (data.success) {
        if (action === 'Accepted') {
          NotifSuccess("La demande de remboursement a été acceptée avec succès.");
        } else if (action === 'Rejected') {
          NotifSuccess("La demande de remboursement a été refusée.");
        } else if (action === 'Trash') {
          NotifSuccess("La demande de remboursement a été supprimée.");
        }

        await chargerDonnees();
      }
    }
  } catch (error) {
    console.error('Erreur lors du traitement:', error);
  } finally {
    isLoading.value = false;
  }
}

async function chargerDonnees() {
  try {
    isLoading.value = true;
    error.value = null;

    const response = await fetch(`http://51.83.76.210/gsb/api/loadFicheFrais.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
    });

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`);
    }

    const responseText = await response.text();

    let data;
    try {
      data = JSON.parse(responseText);
    } catch (jsonError) {
      console.error("Erreur de décodage JSON:", responseText);
    }

    if (data.success) {
      historiqueData.value = data.tableau.map(item => ({
        ...item,
        montant: typeof item.montant === 'number'
          ? item.montant.toFixed(2).replace('.', ',')
          : item.montant
      }));
    }
  } catch (err) {
    error.value = "Erreur de connexion au serveur: " + err.message;
    console.error(err);
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  chargerDonnees();
});
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <main class="pt-10 mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Tableau de bord des dépenses</h1>
            <p class="mt-2 text-sm text-gray-700">Suivi des dépenses et des notes de frais pour l'entreprise.</p>
          </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total des dépenses</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ totalDepenses }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-emerald-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Dépenses validées</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ totalValidees }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">En attente</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ totalEnAttente }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Refusés</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ totalRefusees }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8 bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
          <div class="bg-indigo-600 px-6 py-4">
            <h3 class="text-lg font-bold text-white flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Remboursement des fiches de frais
            </h3>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Description</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Montant</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut</th>
                  <th scope="col"
                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions</th>
                </tr>
              </thead>
              <tbody v-if="isLoading" class="bg-white divide-y divide-gray-200">
                <tr>
                  <td colspan="6" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                      <svg class="animate-spin h-12 w-12 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                      </svg>
                      <p class="text-gray-700 font-medium text-lg">Chargement des données...</p>
                      <p class="text-gray-500 mt-2">Veuillez patienter pendant que nous récupérons les informations</p>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tbody v-else-if="error" class="bg-white divide-y divide-gray-200">
                <tr>
                  <td colspan="6" class="px-6 py-4 text-center text-red-500">
                    {{ error }}
                  </td>
                </tr>
              </tbody>
              <tbody v-else class="bg-white divide-y divide-gray-200">
                <tr v-for="(frais, index) in historiqueData" :key="index"
                  class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatDate(frais.date) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <span :class="getTypeIconClass(frais.type)"
                        class="flex-shrink-0 h-6 w-6 rounded-full flex items-center justify-center mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor" v-html="getTypeIconSvg(frais.type)">
                        </svg>
                      </span>
                      <span class="text-sm font-medium text-gray-900">{{ getTypeLabel(frais.type) }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">{{ frais.description }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ frais.montant }} €</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusClass(frais.statut)" class="px-2.5 py-1 rounded-full text-xs font-medium">
                      {{ getTypeStatut(frais.statut) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex justify-center gap-2">
                      <template v-if="frais.statut === 'Pending'">
                        <button @click="traiterPaiement(frais.id, 'Accepted')"
                          class="p-2 border rounded-md border-green-500 text-green-500 hover:bg-green-500 hover:text-white transition duration-300">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                        </button>
                        <button @click="traiterPaiement(frais.id, 'Rejected')"
                          class="p-2 border rounded-md border-red-500 text-red-500 hover:bg-red-500 hover:text-white transition duration-300">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </template>
                      <button v-if="frais.statut === 'Pending'" @click="traiterPaiement(frais.id, 'Trash')"
                        class="p-2 border rounded-md border-orange-500 text-orange-500 hover:bg-orange-500 hover:text-white transition duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
            <div>
              <p class="text-sm text-gray-700">
                Affichage de <span class="font-medium">1</span> à <span class="font-medium">{{ historiqueData.length
                  }}</span>
                sur <span class="font-medium">{{ historiqueData.length }}</span> résultats
              </p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
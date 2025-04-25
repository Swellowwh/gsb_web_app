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
    return sum + parseMontant(item.montant_total);
  }, 0);

  return total.toFixed(2).replace('.', ',') + " €";
});

const totalValidees = computed(() => {
  if (!historiqueData.value.length) return "0,00 €";

  const total = historiqueData.value
    .filter(item => item.statut === 'Accepted')
    .reduce((sum, item) => {
      return sum + parseMontant(item.montant_total);
    }, 0);

  return total.toFixed(2).replace('.', ',') + " €";
});

const totalEnAttente = computed(() => {
  if (!historiqueData.value.length) return "0,00 €";

  const total = historiqueData.value
    .filter(item => item.statut === 'Pending')
    .reduce((sum, item) => {
      return sum + parseMontant(item.montant_total);
    }, 0);

  return total.toFixed(2).replace('.', ',') + " €";
});

const totalRefusees = computed(() => {
  if (!historiqueData.value.length) return "0,00 €";

  const total = historiqueData.value
    .filter(item => item.statut === 'Rejected')
    .reduce((sum, item) => {
      return sum + parseMontant(item.montant_total);
    }, 0);

  return total.toFixed(2).replace('.', ',') + " €";
});

function getTypeStatut(statut) {
  switch (statut) {
    case 'Accepted': return 'Accepté';
    case 'Pending': return 'En attente';
    case 'Rejected': return 'Refusé';
    case 'Clotured': return 'Cloturé';
    default: return statut;
  }
}

function getStatusClass(status) {
  switch (status) {
    case 'Accepted': return 'bg-green-100 text-green-800';
    case 'Pending': return 'bg-yellow-100 text-yellow-800';
    case 'Rejected': return 'bg-red-100 text-red-800';
    case 'Clotured': return 'bg-gray-100 text-gray-800';
    default: return 'bg-gray-100 text-gray-800';
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

// Dans la fonction chargerDonnees(), ajoutez des logs et vérifications complètes

async function chargerDonnees() {
  try {
    isLoading.value = true;
    error.value = null;

    // Journal pour déboguer
    console.log("Début de chargement des données");

    const response = await fetch(`http://51.83.76.210/gsb/api/loadFicheFrais.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
    });

    console.log("Statut de la réponse:", response.status);

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`);
    }

    const responseText = await response.text();
    console.log("Réponse brute:", responseText);

    let data;
    try {
      data = JSON.parse(responseText);
      console.log("Données analysées:", data);
    } catch (jsonError) {
      console.error("Erreur de décodage JSON:", responseText);
      throw new Error("Format de réponse invalide");
    }

    if (data.success) {
      console.log("Fiches récupérées:", data.fiches);
      historiqueData.value = data.fiches;
    } else {
      throw new Error(data.message);
    }
  } catch (err) {
    error.value = "Erreur de connexion au serveur: " + err.message;
    console.error("Erreur complète:", err);
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
                    Référence</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date</th>
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
              <tbody v-else-if="historiqueData.length === 0" class="bg-white divide-y divide-gray-200">
                <tr>
                  <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    Aucune fiche de frais à afficher
                  </td>
                </tr>
              </tbody>
              <tbody v-else class="bg-white divide-y divide-gray-200">
                <tr v-for="frais in historiqueData" :key="frais.id"
                  class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ frais.reference }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatDate(frais.date) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-700">{{ frais.description }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ parseFloat(frais.montant_total).toFixed(2).replace('.', ',') }} €
                  </td>
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
                Affichage de <span class="font-medium">{{ historiqueData.length > 0 ? 1 : 0 }}</span> à <span class="font-medium">{{ historiqueData.length }}</span>
                sur <span class="font-medium">{{ historiqueData.length }}</span> résultats
              </p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
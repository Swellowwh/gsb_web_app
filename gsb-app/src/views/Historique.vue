<script setup>
import { ref, computed, onMounted } from 'vue';
import ModalEditionFrais from '@/components/ModalEditionFrais.vue';
import { useNotificationService } from '@/services/notification/notification';
const { NotifSuccess } = useNotificationService();

const showDetailsModal = ref(false);
const modalFraisId = ref(null);

const historiqueData = ref([]);
const isLoading = ref(true);
const error = ref(null);

const totalDepenses = computed(() => {
  if (!historiqueData.value.length) return "0,00 €";
  const total = historiqueData.value.reduce((sum, item) => sum + parseMontant(item.montant_total), 0);
  return formatMontant(total);
});

const totalValidees = computed(() => {
  if (!historiqueData.value.length) return "0,00 €";
  const total = historiqueData.value
    .filter(item => item.statut === 'Accepted')
    .reduce((sum, item) => sum + parseMontant(item.montant_total), 0);
  return formatMontant(total);
});

const totalEnAttente = computed(() => {
  if (!historiqueData.value.length) return "0,00 €";
  const total = historiqueData.value
    .filter(item => item.statut === 'Pending')
    .reduce((sum, item) => sum + parseMontant(item.montant_total), 0);
  return formatMontant(total);
});

function parseMontant(montant) {
  if (typeof montant === 'number') return montant;
  return parseFloat(montant.toString().replace('€', '').replace(',', '.').trim());
}

function formatMontant(montant) {
  return typeof montant === 'number'
    ? montant.toFixed(2).replace('.', ',') + ' €'
    : montant;
}

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString('fr-FR');
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

async function traiterPaiement(id, action) {
  const frais = historiqueData.value.find(f => f.id === id);
  if (!frais) return;

  if (action === 'Trash' && (frais.statut === 'Accepted' || frais.statut === 'Rejected')) return;

  try {
    isLoading.value = true;
    await new Promise(resolve => setTimeout(resolve, 500));

    const response = await fetch('http://51.83.76.210/gsb/api/processingHistory.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({
        idHistory: id,
        processingHistory: action
      })
    });

    if (response.ok) {
      const data = await response.json();
      if (data.success) {
        if (action === 'Trash') {
          NotifSuccess("La demande de remboursement a été supprimée.");
        }
        await chargerDonnees();
      }
    }
  } catch (err) {
    console.error('Erreur lors du traitement:', err);
  } finally {
    isLoading.value = false;
  }
}

function editerFrais(id) {
  const frais = historiqueData.value.find(f => f.id === id);
  if (!frais || frais.statut === 'Accepted' || frais.statut === 'Rejected') return;
  modalFraisId.value = id;
  showDetailsModal.value = true;
}

async function chargerDonnees() {
  try {
    isLoading.value = true;
    error.value = null;

    const response = await fetch('http://51.83.76.210/gsb/api/loadFicheFrais.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include'
    });

    const data = await response.json();
    if (data.success) {
      historiqueData.value = data.fiches;
    } else {
      error.value = "Impossible de récupérer les données";
    }
  } catch (err) {
    error.value = "Erreur de connexion au serveur";
  } finally {
    isLoading.value = false;
  }
}

onMounted(async () => {
  await new Promise(resolve => setTimeout(resolve, 1000));
  await chargerDonnees();
});
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <main class="pt-10 mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Tableau de bord des dépenses</h1>
            <p class="mt-2 text-sm text-gray-700">Suivi des dépenses et des notes de frais de l'entreprise.</p>
          </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-pink-500 rounded-md p-3">
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
        </div>

        <!-- Tableau d'historique des frais -->
        <div class="mt-8 bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
          <div class="bg-indigo-600 px-6 py-4">
            <h3 class="text-lg font-bold text-white flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Historique des frais
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
                    Montant total</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                <tr v-for="frais in historiqueData" :key="frais.id"
                  class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ frais.reference }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ formatDate(frais.date) }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">{{ frais.description || '-' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ formatMontant(frais.montant_total) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusClass(frais.statut)" class="px-2.5 py-1 rounded-full text-xs font-medium">
                      {{ getTypeStatut(frais.statut) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex space-x-2 justify-center">
                      <!-- Bouton d'édition - uniquement pour les frais en statut Pending -->
                      <button @click="editerFrais(frais.id)" :disabled="frais.statut !== 'Pending'" :class="[
                        'p-2 border rounded-md transition duration-300',
                        frais.statut !== 'Pending'
                          ? 'border-gray-300 text-gray-300 cursor-not-allowed'
                          : 'border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white'
                      ]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>

                      <!-- Bouton de suppression - désactivé si statut Accepted ou Rejected -->
                      <button @click="traiterPaiement(frais.id, 'Trash')" :disabled="frais.statut !== 'Pending'" :class="[
                        'p-2 border rounded-md transition duration-300',
                        frais.statut !== 'Pending'
                          ? 'border-gray-300 text-gray-300 cursor-not-allowed'
                          : 'border-red-500 text-red-500 hover:bg-red-500 hover:text-white'
                      ]">
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
                Affichage de <span class="font-medium">{{ historiqueData.length > 0 ? 1 : 0 }}</span> à <span
                  class="font-medium">{{ historiqueData.length }}</span>
                sur <span class="font-medium">{{ historiqueData.length }}</span> résultats
              </p>
            </div>
          </div>
        </div>
      </div>
    </main>

    <ModalEditionFrais v-model="showDetailsModal" :id="modalFraisId" @updated="chargerDonnees" />
  </div>
</template>
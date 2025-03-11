<template>
  <div class="min-h-screen bg-gray-100">
    <main class="pt-10 mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 sm:px-6 lg:px-8">
        <!-- En-tête avec titre et bouton -->
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Tableau de bord des dépenses</h1>
            <p class="mt-2 text-sm text-gray-700">Suivi des dépenses et des notes de frais pour l'entreprise.</p>
          </div>
          <div class="sm:flex sm:items-center">
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
              <button type="button"
                class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Nouvelle dépense
              </button>
            </div>
          </div>
        </div>

        <!-- Résumé des dépenses -->
        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total des dépenses</dt>
                    <dd class="text-lg font-semibold text-gray-900">673,75 €</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Dépenses validées</dt>
                    <dd class="text-lg font-semibold text-gray-900">484,00 €</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">En attente</dt>
                    <dd class="text-lg font-semibold text-gray-900">111,25 €</dd>
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
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Remboursement des fiches de frais
            </h3>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Description</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Montant</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(frais, index) in historiqueData" :key="index" class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ frais.dateFormatted }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <span v-if="frais.type === 'Kilométrique'"
                        class="flex-shrink-0 h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                      </span>
                      <span v-else-if="frais.type === 'Repas'"
                        class="flex-shrink-0 h-6 w-6 rounded-full bg-green-100 flex items-center justify-center mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                      </span>
                      <span v-else-if="frais.type === 'Hébergement'"
                        class="flex-shrink-0 h-6 w-6 rounded-full bg-purple-100 flex items-center justify-center mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                      </span>
                      <span v-else-if="frais.type === 'Transport'"
                        class="flex-shrink-0 h-6 w-6 rounded-full bg-red-100 flex items-center justify-center mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                      </span>
                      <span v-else
                        class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                      </span>
                      <span class="text-sm font-medium text-gray-900">{{ frais.type }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">{{ frais.description }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ frais.montant }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'px-2.5 py-1 rounded-full text-xs font-medium',
                      frais.statut === 'Validé' ? 'bg-green-100 text-green-800' :
                        frais.statut === 'En attente' ? 'bg-yellow-100 text-yellow-800' : 
                          'bg-red-100 text-red-800'
                    ]">
                      {{ frais.statut }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                      <button v-if="frais.statut === 'En attente'"
                        class="text-indigo-600 hover:text-indigo-900">
                        <span class="sr-only">Modifier</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button v-if="frais.statut === 'En attente'"
                        class="text-red-600 hover:text-red-900">
                        <span class="sr-only">Supprimer</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
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
            <div class="flex-1 flex justify-between sm:hidden">
              <a href="#"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Précédent
              </a>
              <a href="#"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Suivant
              </a>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Affichage de <span class="font-medium">1</span> à <span class="font-medium">{{ historiqueData.length }}</span> 
                  sur <span class="font-medium">{{ historiqueData.length }}</span> résultats
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <a href="#"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">Précédent</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                      aria-hidden="true">
                      <path fill-rule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                    </svg>
                  </a>
                  <a href="#"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                    1
                  </a>
                  <a href="#"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">Suivant</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                      aria-hidden="true">
                      <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                    </svg>
                  </a>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
// Données en dur intégrées directement dans le composant
const historiqueData = [
  {
    id: 1,
    date: '2025-03-10',
    dateFormatted: '10/03/2025',
    type: 'Kilométrique',
    description: 'Paris - Marseille (750 km)',
    montant: '300,00 €',
    statut: 'Validé'
  },
  {
    id: 2,
    date: '2025-03-05',
    dateFormatted: '05/03/2025',
    type: 'Repas',
    description: 'Déjeuner d\'affaires - Restaurant La Coupole',
    montant: '68,50 €',
    statut: 'En attente'
  },
  {
    id: 3,
    date: '2025-02-28',
    dateFormatted: '28/02/2025',
    type: 'Hébergement',
    description: 'Hôtel Ibis - Lyon (2 nuits)',
    montant: '184,00 €',
    statut: 'Validé'
  },
  {
    id: 4,
    date: '2025-02-20',
    dateFormatted: '20/02/2025',
    type: 'Transport',
    description: 'Billet SNCF Paris-Bordeaux',
    montant: '78,50 €',
    statut: 'Refusé'
  },
  {
    id: 5,
    date: '2025-02-15',
    dateFormatted: '15/02/2025',
    type: 'Autre',
    description: 'Fournitures de bureau',
    montant: '42,75 €',
    statut: 'En attente'
  }
];
</script>
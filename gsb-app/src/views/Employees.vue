<script setup>
import { ref, onMounted } from 'vue';
import { useNotificationService } from '@/services/notification/notification';
import FormAddVisiteur from '@/components/FormAddVisiteur.vue';

const { NotifSuccess, NotifError } = useNotificationService();

const visiteurs = ref([]);
const isLoading = ref(true);
const FormOpen = ref(false);

const fetchVisiteurs = async () => {
  isLoading.value = true;
  visiteurs.value = [];

  try {
    const response = await fetch("http://51.83.76.210/gsb/api/loadUtilisateur.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
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

    if (result && result.success === true) {
      setTimeout(() => {
        visiteurs.value = result.visiteurs.map(visiteur => ({
          id: visiteur.EMP_ID,
          nom: visiteur.EMP_NOM,
          prenom: visiteur.EMP_PRENOM,
          adresse: visiteur.EMP_ADRESSE,
          codePostal: visiteur.EMP_CP,
          ville: visiteur.EMP_VILLE,
          role: visiteur.EMP_ROLE,
          dateEmbauche: visiteur.EMP_DATE_EMBAUCHE,
        }));
        isLoading.value = false;
      }, 500);
    }
  } catch (err) {
    console.error("Erreur complète:", err);
    NotifError("Impossible de charger les données des visiteurs", { title: "Erreur de connexion" });
    isLoading.value = false;
  }
};

const openForm = () => {
  FormOpen.value = !FormOpen.value;
};

const onVisiteurAdded = async () => {
  FormOpen.value = false;
  await fetchVisiteurs();
};

const deleteUser = async (id, nom, prenom) => {
  visiteurs.value = [];

  try {
    const response = await fetch(`http://51.83.76.210/gsb/api/deleteVisiteur.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ id: id })
    });

    await response.text();

      await fetchVisiteurs();
      NotifSuccess(`${prenom} ${nom} a été supprimé avec succès`, { title: "Employé supprimé" });
  } catch (err) {
    console.error("Erreur lors de la suppression:", err);
    NotifError("Erreur lors de la suppression du visiteur", { title: "Erreur" });
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchVisiteurs();
});
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <main class="pt-10 mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Liste des employées</h1>
            <p class="mt-2 text-sm text-gray-700">Liste de tous les visiteurs employés dans l'entreprise.</p>
          </div>
          <div class="sm:flex sm:items-center">
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
              <button type="button" @click="openForm"
                class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                {{ FormOpen ? 'Annuler' : 'Ajouter un employé' }}
              </button>
            </div>
          </div>
        </div>

        <FormAddVisiteur v-if="FormOpen" :is-loading="isLoading"
          @visiteur-added="onVisiteurAdded" />

        <div v-if="isLoading" class="mt-8 bg-white p-8 rounded-lg shadow ring-1 ring-black ring-opacity-5">
          <div class="flex flex-col items-center justify-center py-12">
            <svg class="animate-spin h-12 w-12 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
              viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            <p class="text-gray-700 font-medium text-lg">Chargement des données...</p>
            <p class="text-gray-500 mt-2">Veuillez patienter pendant que nous récupérons la liste des employées</p>
          </div>
        </div>

        <div v-if="!isLoading" class="mt-8 flow-root">
          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
              <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                <table class="min-w-full divide-y divide-gray-300 relative">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Nom
                        / Prénom
                      </th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Lieu de
                        résidence</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date d'embauche
                      </th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Qualification
                      </th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"></th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="visitor in visiteurs" :key="visitor.id">
                      <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                        <div class="flex items-center">
                          <div
                            class="h-10 w-10 flex-shrink-0 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-indigo-700 font-medium">
                              {{ visitor.prenom.charAt(0).toUpperCase() }}{{ visitor.nom.charAt(0).toUpperCase() }}
                            </span>
                          </div>
                          <div class="ml-4">
                            <div class="font-medium text-gray-900">{{ visitor.nom }} {{ visitor.prenom }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm">
                        <div class="text-gray-900">{{ visitor.ville }} ({{ visitor.codePostal }})</div>
                        <div class="text-gray-500">{{ visitor.adresse }}</div>
                      </td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm">
                        <span>
                          {{ visitor.dateEmbauche }}
                        </span>
                      </td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 capitalize">{{ visitor.role }}</td>
                      <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <div class="flex justify-end space-x-2">
                          <button @click="deleteUser(visitor.id, visitor.nom, visitor.prenom)" class="text-red-600 hover:text-red-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                              fill="currentColor">
                              <path fill-rule="evenodd"
                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>

                <div v-if="visiteurs.length === 0" class="text-center py-10 bg-white">
                  <p class="text-gray-500">Aucun employé trouvé pour cette recherche</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

  </div>
</template>
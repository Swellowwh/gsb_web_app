<script setup>
import { ref, onMounted } from 'vue';

// État pour les utilisateurs
const visiteurs = ref([]);
const isLoading = ref(true);
const error = ref(null);
const deleteConfirmModal = ref(false);
const visitorToDelete = ref(null);
const deleteSuccess = ref(false);
const deleteMessage = ref('');

// État pour le formulaire d'ajout
const showForm = ref(false);
const newUser = ref({
    nom: '',
    prenom: '',
    email: '',
    adresse: '',
    codePostal: '',
    ville: '',
    dateEmbauche: new Date().toISOString().split('T')[0]
});

// Fonction pour charger les données visiteurs depuis l'API
const fetchVisiteurs = async () => {
    isLoading.value = true;
    error.value = null;

    try {
        // Appel à votre API
        const response = await fetch("http://51.83.76.210/gsb/backend/loadUtilisateur.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            credentials: "include" // Pour inclure les cookies qui contiennent le JWT
        });

        // Récupérer d'abord le texte brut pour le déboguer
        const responseText = await response.text();
        console.log("Réponse brute:", responseText);

        let result;
        try {
            // Essayer de parser le texte en JSON
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error("Erreur de parsing JSON:", parseError);
            throw new Error("La réponse n'est pas un JSON valide");
        }

        console.log("Réponse complète:", result);

        // Vérifier si la requête a réussi selon votre structure de réponse
        if (result && result.success === true) {
            // La requête a réussi, affecter les données au tableau visiteurs
            // Transformer les données pour correspondre à la structure attendue
            visiteurs.value = result.visiteurs.map(visiteur => ({
                id: visiteur.VIS_ID,
                nom: visiteur.VIS_NOM,
                prenom: visiteur.VIS_PRENOM,
                adresse: visiteur.VIS_ADRESSE,
                codePostal: visiteur.VIS_CP,
                ville: visiteur.VIS_VILLE,
                dateEmbauche: visiteur.VIS_DATE_EMBAUCHE,
            }));

            console.log("Visiteurs chargés:", visiteurs.value);
        } else {
            // La requête a échoué, afficher le message d'erreur
            const errorMessage = result.message || "Échec du chargement des visiteurs";
            console.error("Erreur API:", errorMessage);
            throw new Error(errorMessage);
        }
    } catch (err) {
        console.error("Erreur complète:", err);
        error.value = err.message || "Une erreur est survenue";
    } finally {
        isLoading.value = false;
    }
};

// Fonction pour ajouter un visiteur
const addUser = async () => {
    // Logique d'ajout d'visiteur à implémenter
    console.log("Ajout de visiteur:", newUser.value);

    // Simulation d'une requête API pour ajouter un visiteur
    try {
        // Ici vous feriez un appel API pour ajouter l'utilisateur en BDD
        // const response = await fetch("http://51.83.76.210/gsb/backend/addVisiteur.php", {...});

        // Pour le moment, on va simplement ajouter l'utilisateur à notre tableau local
        const newId = Math.max(...visiteurs.value.map(v => v.id), 0) + 1;
        visiteurs.value.push({
            id: newId,
            nom: newUser.value.nom,
            prenom: newUser.value.prenom,
            adresse: newUser.value.adresse,
            codePostal: newUser.value.codePostal,
            ville: newUser.value.ville,
            dateEmbauche: newUser.value.dateEmbauche
        });

        // Fermer le formulaire et réinitialiser
        showForm.value = false;
        resetForm();
    } catch (err) {
        console.error("Erreur lors de l'ajout:", err);
        error.value = "Erreur lors de l'ajout du visiteur";
    }
};

// Fonction pour supprimer un visiteur
const deleteUser = async (id) => {
    try {
        // Simuler un appel à l'API pour supprimer un visiteur
        console.log("Suppression du visiteur ID:", id);

        // Vous devriez faire un appel API à votre backend
        // const response = await fetch(`http://51.83.76.210/gsb/backend/deleteVisiteur.php`, {
        //    method: "POST",
        //    headers: { "Content-Type": "application/json" },
        //    credentials: "include",
        //    body: JSON.stringify({ id: id })
        // });

        // const result = await response.json();

        // Pour la démonstration, on simule une réponse réussie
        // Suppression dans le tableau local
        visiteurs.value = visiteurs.value.filter(v => v.id !== id);

        // Afficher un message de succès temporaire
        deleteSuccess.value = true;
        deleteMessage.value = "Visiteur supprimé avec succès";
        setTimeout(() => {
            deleteSuccess.value = false;
        }, 3000);

        // Fermer la modal de confirmation
        deleteConfirmModal.value = false;
        visitorToDelete.value = null;
    } catch (err) {
        console.error("Erreur lors de la suppression:", err);
        error.value = "Erreur lors de la suppression du visiteur";
    }
};

// Fonction pour confirmer la suppression
const confirmDelete = (visitor) => {
    visitorToDelete.value = visitor;
    deleteConfirmModal.value = true;
};

// Réinitialiser le formulaire d'ajout
const resetForm = () => {
    newUser.value = {
        nom: '',
        prenom: '',
        email: '',
        adresse: '',
        codePostal: '',
        ville: '',
        dateEmbauche: new Date().toISOString().split('T')[0]
    };
};

// Formater la date
const formatDate = (dateString) => {
    if (!dateString) return '';

    try {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    } catch (e) {
        return dateString;
    }
};

// Charger les données au montage du composant
onMounted(() => {
    fetchVisiteurs();
});
</script>

<template>
    <div class="bg-gray-950 rounded-lg shadow-lg border border-gray-200 overflow-hidden">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-blue-800 to-blue-600 px-6 py-4">
            <h3 class="text-lg font-medium text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Gestion des Visiteurs Médicaux
            </h3>
        </div>

        <!-- Message de succès pour la suppression -->
        <div v-if="deleteSuccess" class="bg-green-50 border-l-4 border-green-500 p-4 mb-3 mx-4 mt-4 rounded-md">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-green-700">{{ deleteMessage }}</p>
            </div>
        </div>

        <!-- Bouton d'ajout -->
        <div class="bg-gray-50 px-6 py-3 flex justify-between border-b border-gray-200 items-center">
            <h4 class="text-gray-600 font-medium">
                <span v-if="visiteurs.length > 0">{{ visiteurs.length }} visiteur(s) enregistré(s)</span>
                <span v-else>Aucun visiteur</span>
            </h4>
            <button @click="showForm = true"
                class="flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                        clip-rule="evenodd" />
                </svg>
                Ajouter un visiteur
            </button>
        </div>

        <!-- Message de chargement -->
        <div v-if="isLoading" class="px-6 py-8 text-center text-gray-500">
            <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <p class="font-medium text-blue-700">Chargement des visiteurs...</p>
        </div>

        <!-- Message d'erreur -->
        <div v-if="error && !isLoading"
            class="px-6 py-4 m-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-md">
            <div class="flex">
                <svg class="h-5 w-5 text-red-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                {{ error }}
            </div>
        </div>

        <!-- Tableau des visiteurs -->
        <div v-if="!isLoading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Visiteur
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Adresse
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Code Postal
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Ville
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Date d'embauche
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(visitor, index) in visiteurs" :key="visitor.id"
                        class="hover:bg-blue-50 transition-colors duration-150"
                        :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                                    {{ visitor.prenom.charAt(0).toUpperCase() }}{{ visitor.nom.charAt(0).toUpperCase()
                                    }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ visitor.nom }} {{ visitor.prenom
                                        }}</div>
                                    <div class="text-xs text-gray-500">ID: {{ visitor.id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ visitor.adresse }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ visitor.codePostal }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ visitor.ville }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ formatDate(visitor.dateEmbauche) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex space-x-2">
                                <button @click="confirmDelete(visitor)"
                                    class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 px-3 py-1 rounded-md flex items-center transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Supprimer
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="visiteurs.length === 0">
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="text-gray-600 font-medium">Aucun visiteur à afficher</p>
                                <p class="text-gray-400 text-sm mt-1">Ajoutez votre premier visiteur en cliquant sur
                                    le bouton "Ajouter un visiteur"</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal pour l'ajout de visiteur -->
        <div v-if="showForm"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 border border-gray-300">
                <!-- En-tête du modal -->
                <div class="bg-gradient-to-r from-blue-800 to-blue-600 px-6 py-4 rounded-t-lg">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Ajouter un visiteur
                        </h3>
                        <button @click="showForm = false"
                            class="text-white hover:text-gray-200 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Corps du formulaire -->
                <div class="p-6 bg-white">
                    <div class="grid grid-cols-1 gap-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                                <input type="text" id="nom" v-model="newUser.nom" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                       transition-colors duration-200">
                            </div>
                            <div>
                                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                                <input type="text" id="prenom" v-model="newUser.prenom" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                       transition-colors duration-200">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" v-model="newUser.email" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                     transition-colors duration-200">
                        </div>

                        <div>
                            <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                            <input type="text" id="adresse" v-model="newUser.adresse" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                     transition-colors duration-200">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="codePostal" class="block text-sm font-medium text-gray-700 mb-1">Code
                                    postal</label>
                                <input type="text" id="codePostal" v-model="newUser.codePostal" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                       transition-colors duration-200">
                            </div>
                            <div>
                                <label for="ville" class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                                <input type="text" id="ville" v-model="newUser.ville" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                       transition-colors duration-200">
                            </div>
                        </div>

                        <div>
                            <label for="dateEmbauche" class="block text-sm font-medium text-gray-700 mb-1">Date
                                d'embauche</label>
                            <input type="date" id="dateEmbauche" v-model="newUser.dateEmbauche" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                     transition-colors duration-200">
                        </div>
                    </div>
                </div>

                <!-- Pied du modal avec boutons d'action -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-lg">
                    <button @click="showForm = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 
                  hover:bg-gray-100 
                  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
                  transition-colors duration-200">
                        Annuler
                    </button>
                    <button @click="addUser" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white 
                  bg-blue-600 hover:bg-blue-700
                  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
                  transition-colors duration-200">
                        Ajouter
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de confirmation pour la suppression -->
        <div v-if="deleteConfirmModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 border border-gray-300">
                <!-- En-tête du modal -->
                <div class="bg-red-600 px-6 py-4 rounded-t-lg">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Confirmation de suppression
                        </h3>
                        <button @click="deleteConfirmModal = false; visitorToDelete = null"
                            class="text-white hover:text-gray-200 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Corps de la confirmation -->
                <div class="p-6 bg-white">
                    <p class="text-gray-700">
                        Êtes-vous sûr de vouloir supprimer définitivement le visiteur
                        <span class="font-semibold">{{ visitorToDelete?.nom }} {{ visitorToDelete?.prenom }}</span> ?
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        Cette action est irréversible et toutes les données associées à ce visiteur seront perdues.
                    </p>
                </div>

                <!-- Pied du modal avec boutons d'action -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-lg">
                    <button @click="deleteConfirmModal = false; visitorToDelete = null" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 
                        hover:bg-gray-100 
                        focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
                        transition-colors duration-200">
                        Annuler
                    </button>
                    <button @click="deleteUser(visitorToDelete.id)" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white 
                        bg-red-600 hover:bg-red-700
                        focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500
                        transition-colors duration-200">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref } from 'vue';
import { useNotificationService } from '@/services/notification/notification';

const isValidDate = (dateString) => {
  if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
    const date = new Date(dateString);
    return !isNaN(date.getTime());
  }
  return false;
};

const props = defineProps({
  isLoading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['visiteur-added']);
const { NotifError, NotifSuccess } = useNotificationService();

const nom = ref('');
const prenom = ref('');
const adresse = ref('');
const codePostal = ref('');
const ville = ref('');
const dateEmbauche = ref('');
const role = ref('');
const error = ref(null);
const isSubmitting = ref(false);

const roles = [
  { id: 'visiteur', label: 'Visiteur médical' },
  { id: 'comptable', label: 'Comptable' },
];

const validateForm = () => {
  const validations = [
    { value: nom.value, regex: /^[A-Za-zÀ-ÖØ-öø-ÿ\-]{2,}$/, field: 'nom', message: 'Le nom doit contenir au moins 2 caractères et uniquement des lettres.' },
    { value: prenom.value, regex: /^[A-Za-zÀ-ÖØ-öø-ÿ\-]{2,}$/, field: 'prénom', message: 'Le prénom doit contenir au moins 2 caractères et uniquement des lettres.' },
    { value: adresse.value, regex: /^.{5,}$/, field: 'adresse', message: 'L\'adresse doit contenir au moins 5 caractères.' },
    { value: codePostal.value, regex: /^\d{5,}$/, field: 'code postal', message: 'Le code postal doit contenir uniquement des chiffres (minimum 5).' },
    { value: ville.value, regex: /^[A-Za-zÀ-ÖØ-öø-ÿ\s\-]{2,}$/, field: 'ville', message: 'La ville doit contenir au moins 2 caractères.' }
  ];

  for (const field of validations) {
    if (!field.value || !field.regex.test(field.value)) {
      error.value = field.message;
      return false;
    }
  }

  if (!dateEmbauche.value || !isValidDate(dateEmbauche.value)) {
    error.value = "Le format de la date d'embauche est invalide (format attendu : AAAA-MM-JJ).";
    return false;
  }

  if (!role.value) {
    error.value = "Veuillez sélectionner un rôle pour le nouvel employé.";
    return false;
  }

  return true;
};

const addUser = async () => {
  try {
    isSubmitting.value = true;
    error.value = null;

    if (!validateForm()) {
      isSubmitting.value = false;
      return;
    }
    
    const userData = {
      nom: nom.value,
      prenom: prenom.value,
      adresse: adresse.value,
      codePostal: codePostal.value,
      ville: ville.value,
      dateEmbauche: dateEmbauche.value,
      role: role.value
    };

    const response = await fetch("http://51.83.76.210/gsb/backend/addVisiteur.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(userData)
    });

    const textResponse = await response.text();
    
    let data;
    try {
      data = JSON.parse(textResponse);
    } catch (parseError) {
      console.error("Erreur lors du parsing de la réponse:", textResponse);
      throw new Error("Le serveur a retourné une réponse invalide. Contactez l'administrateur.");
    }
    
    if (data && data.success) {
      const roleLabel = roles.find(r => r.id === role.value)?.label || role.value;
      
      NotifSuccess(`${userData.prenom} ${userData.nom} a rejoint l'équipe en tant que ${roleLabel} !`, { 
        title: "Nouvel employé recruté", 
        duration: 6000 
      });

      emit('visiteur-added');
    } else {
      throw new Error(data?.message || "Erreur lors de l'ajout du visiteur");
    }
  } catch (err) {
    const errorMessage = err.message || "Erreur de connexion au serveur";
    error.value = errorMessage;
    NotifError(errorMessage, { 
      title: "Erreur" 
    });
  } finally {
    setTimeout(() => isSubmitting.value = false, 500);
  }
};
</script>

<template>
  <div class="mt-6 p-6 bg-white border border-indigo-400 rounded-lg">
    <h2 class="text-lg font-medium text-gray-900 mb-4">Ajouter un nouveau visiteur</h2>
    
    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
      {{ error }}
    </div>
    
    <form @submit.prevent="addUser" class="space-y-6">
      <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
        <div class="sm:col-span-3">
          <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
          <div class="mt-1">
            <input 
              type="text" 
              name="nom" 
              id="nom" 
              v-model="nom"
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
            />
          </div>
        </div>

        <div class="sm:col-span-3">
          <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
          <div class="mt-1">
            <input 
              type="text" 
              name="prenom" 
              id="prenom" 
              v-model="prenom"
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
            />
          </div>
        </div>

        <div class="sm:col-span-6">
          <label for="adresse" class="block text-sm font-medium text-gray-700">Adresse</label>
          <div class="mt-1">
            <input 
              type="text" 
              name="adresse" 
              id="adresse" 
              v-model="adresse"
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
            />
          </div>
        </div>

        <div class="sm:col-span-2">
          <label for="codePostal" class="block text-sm font-medium text-gray-700">Code postal</label>
          <div class="mt-1">
            <input 
              type="text" 
              name="codePostal" 
              id="codePostal" 
              v-model="codePostal" maxlength="5"
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
            />
          </div>
        </div>

        <div class="sm:col-span-4">
          <label for="ville" class="block text-sm font-medium text-gray-700">Ville</label>
          <div class="mt-1">
            <input 
              type="text" 
              name="ville" 
              id="ville" 
              v-model="ville"
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
            />
          </div>
        </div>

        <div class="sm:col-span-3">
          <label for="dateInput" class="block text-sm font-medium text-gray-700">Date d'embauche</label>
          <div class="mt-1">
            <input 
              type="date" 
              id="dateInput" 
              v-model="dateEmbauche"
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
            />
          </div>
        </div>

        <div class="sm:col-span-3">
          <label for="role" class="block text-sm font-medium text-gray-700">Rôle dans l'entreprise</label>
          <div class="mt-1">
            <select 
              id="role" 
              name="role" 
              v-model="role"
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
            >
              <option value="" disabled>Sélectionnez le rôle</option>
              <option v-for="roleOption in roles" :key="roleOption.id" :value="roleOption.id">
                {{ roleOption.label }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div class="flex justify-end space-x-3">
        <button 
          type="submit"
          :disabled="isSubmitting || props.isLoading"
          class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          :class="{ 'opacity-70 cursor-not-allowed': isSubmitting || props.isLoading }"
        >
          {{ isSubmitting || props.isLoading ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
      </div>
    </form>
  </div>
</template>
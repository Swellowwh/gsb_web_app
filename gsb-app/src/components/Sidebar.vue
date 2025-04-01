<script setup>
import { useUserStore } from '@/stores/user';
import { computed, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const userStore = useUserStore();
const router = useRouter();
const route = useRoute();

import {
    BanknotesIcon,
    UserGroupIcon,
    CalendarIcon,
    DocumentPlusIcon,
    ClockIcon,
    ArrowRightOnRectangleIcon,
    CreditCardIcon
} from '@heroicons/vue/24/outline';

// Déterminer le rôle de l'utilisateur
const isVisiteurMedical = computed(() =>
    userStore.userData?.role?.toUpperCase() === 'VISITEUR_MEDICAL'
);

const isComptable = computed(() =>
    userStore.userData?.role?.toUpperCase() === 'COMPTABLE'
);

const isAdministrateur = computed(() =>
    userStore.userData?.role?.toUpperCase() === 'ADMINISTRATEUR'
);

// Définition des navigations spécifiques en fonction du rôle
const visiteurMedicalNavigation = [
    { name: 'Fiche de frais', path: '/frais', icon: BanknotesIcon, current: computed(() => route.path === '/frais'), hasDropdown: false },
    { name: 'Historique', path: '/historique', icon: CalendarIcon, current: computed(() => route.path === '/historique'), hasDropdown: false },
];

const comptableNavigation = [
    { name: 'Gestion des paiements', path: '/payments', icon: BanknotesIcon, current: computed(() => route.path === '/payments'), hasDropdown: false },
];

const administrateurNavigation = [
    {
        name: 'Liste des employés',
        path: '/employees',
        icon: UserGroupIcon,
        current: computed(() => route.path === '/employees'),
        hasDropdown: false
    },
    {
        name: 'Fiches de frais',
        path: '/frais',
        icon: DocumentPlusIcon,
        current: computed(() => route.path === '/frais' || route.path.startsWith('/frais/')),
    },
    {
        name: 'Historique',
        path: '/historique',
        icon: ClockIcon,
        current: computed(() => route.path === '/historique'),
    },
    {
        name: 'Paiements',
        path: '/payments',
        icon: CreditCardIcon,
        current: computed(() => route.path === '/payments'),
    },
];

// Utiliser la navigation appropriée selon le rôle
const isUtilisateur = computed(() =>
    userStore.userData?.role?.toUpperCase() === 'UTILISATEUR'
);

const navigation = computed(() => {
    if (isUtilisateur.value) return [];
    if (isAdministrateur.value) return administrateurNavigation;
    if (isVisiteurMedical.value) return visiteurMedicalNavigation;
    if (isComptable.value) return comptableNavigation;
    return [];
});


// Fonction pour obtenir les initiales de l'utilisateur
const getUserInitials = computed(() => {
    const email = userStore.userData?.email || '';
    if (!email) return 'UN'; // UN pour "User Name" par défaut

    // Si le nom est composé, prendre les initiales de chaque partie
    if (email.includes(' ')) {
        return email
            .split(' ')
            .map(part => part.charAt(0))
            .join('')
            .toUpperCase();
    }

    // Sinon retourner les deux premières lettres du nom
    return email.substring(0, 2).toUpperCase();
});

// État de la modal de profil
const showProfileMenu = ref(false);

// Formater le rôle pour l'affichage
const formattedRole = computed(() => {
    const role = userStore.userData?.role || '';
    if (role.toUpperCase() === 'VISITEUR_MEDICAL') return 'Visiteur médical';
    if (role.toUpperCase() === 'COMPTABLE') return 'Comptable';
    if (role.toUpperCase() === 'ADMINISTRATEUR') return 'Administrateur';
    return role;
});

const logout = async () => {
    try {
        const response = await fetch("http://51.83.76.210/gsb/api/logout.php", {
            method: "POST",
            credentials: "include",
        });

        const data = await response.json();
        console.log("Réponse du serveur:", data);

        if (data.success) {
            userStore.reset();
            router.push('/login');
            return true;
        } else {
            console.error('Échec de déconnexion:', data.message || 'Raison inconnue');
            return false;
        }
    } catch (error) {
        console.error('Erreur lors de la déconnexion:', error);
        return false;
    }
};
</script>

<template>
    <div class="h-screen flex flex-col bg-indigo-900 overflow-hidden relative">
        <div class="relative p-5 border-b border-indigo-800">
            <div class="flex items-center">
                <h1 class="text-white text-xl font-semibold tracking-wider ml-2.5">
                    GSB
                </h1>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3">
            <ul class="space-y-2">
                <li v-for="item in navigation" :key="item.name">
                    <router-link :to="item.path" :class="[
                        item.current.value
                            ? 'bg-white text-indigo-900 font-semibold'
                            : 'text-white hover:bg-indigo-700 hover:text-white',
                        'group flex items-center justify-between px-3 py-3 rounded-md text-sm transition-all duration-150 shadow-sm'
                    ]">
                        <div class="flex items-center">
                            <component :is="item.icon"
                                class="mr-3 h-5 w-5"
                                :class="item.current.value ? 'text-indigo-900' : 'text-white group-hover:text-white'" 
                                aria-hidden="true" />
                            {{ item.name }}
                        </div>
                        
                        <div class="flex items-center">
                            <!-- Badge (if exists) -->
                            <span v-if="item.badge" 
                                  class="bg-white text-indigo-900 text-xs font-semibold rounded-full px-2 py-0.5 ml-1">
                                {{ item.badge }}
                            </span>
                            
                            <!-- Dropdown arrow (if has dropdown) -->
                            <svg v-if="item.hasDropdown" xmlns="http://www.w3.org/2000/svg" 
                                 class="h-4 w-4 ml-1 text-indigo-300" 
                                 fill="none" viewBox="0 0 24 24" 
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </router-link>
                </li>
            </ul>
        </nav>

        <!-- User Profile Section -->
        <div class="relative border-t border-indigo-800 p-3">
            <!-- Menu de déconnexion sur toute la largeur avec texte à gauche -->
            <div v-if="showProfileMenu" 
                 class="absolute left-3 right-3 bottom-full mb-2 py-1 bg-white rounded-md shadow-lg z-20 border border-indigo-100">
                <button @click="logout()" 
                        class="w-full text-left flex items-center px-4 py-2.5 text-sm text-indigo-900 hover:bg-indigo-100 transition-colors">
                    <ArrowRightOnRectangleIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Déconnexion
                </button>
            </div>
            <div class="relative flex items-center justify-between cursor-pointer hover:bg-indigo-800 rounded-md p-2 transition-colors duration-150">
                <div class="flex items-center">
                    <div class="relative">
                        <!-- User Avatar with solid background -->
                        <div class="h-9 w-9 rounded-full bg-white flex items-center justify-center text-base text-indigo-800 font-bold shadow-md">
                            {{ getUserInitials }}
                        </div>
                        <!-- More visible pulsing indicator -->
                        <span class="absolute -bottom-0.5 -right-0.5 flex h-4 w-4 items-center justify-center">
                            <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75 blur-[1px]"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500 ring-1 ring-indigo-900 z-10"></span>
                        </span>
                    </div>
                    
                    <!-- User Name & Role -->
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">
                            {{ userStore.userData?.email || 'Utilisateur' }}
                        </p>
                        <p class="text-xs text-indigo-200 truncate">
                            {{ formattedRole }}
                        </p>
                    </div>
                </div>
                
                <!-- Dropdown Button for Logout Only -->
                <div>
                    <button @click="showProfileMenu = !showProfileMenu" 
                            class="p-1.5 rounded-full hover:bg-indigo-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="h-5 w-5 text-white" 
                             viewBox="0 0 20 20" 
                             fill="currentColor">
                            <path fill-rule="evenodd" 
                                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" 
                                  clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
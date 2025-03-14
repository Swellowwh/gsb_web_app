<script setup>
import { useUserStore } from '@/stores/user';
import { computed, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const userStore = useUserStore();
const router = useRouter();
const route = useRoute();

import {
    HomeIcon,
    BanknotesIcon,
    UserGroupIcon,
    CalendarIcon,
    DocumentPlusIcon,
    ClockIcon,
    ArrowRightOnRectangleIcon,
    Cog6ToothIcon,
    UserIcon,
    CreditCardIcon
} from '@heroicons/vue/24/solid';

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
    { name: 'Fiche de frais', path: '/frais', icon: BanknotesIcon, current: computed(() => route.path === '/frais') },
    { name: 'Historique', path: '/historique', icon: CalendarIcon, current: computed(() => route.path === '/historique') },
];

const comptableNavigation = [
    { name: 'Gestion des paiements', path: '/paiement', icon: BanknotesIcon, current: computed(() => route.path === '/paiement') },
];

const administrateurNavigation = [
    {
        name: 'Liste des employés',
        path: '/employees',
        icon: UserGroupIcon,
        current: computed(() => route.path === '/employees')
    },
    {
        name: 'Ajouter une fiche',
        path: '/frais',
        icon: DocumentPlusIcon,
        current: computed(() => route.path === '/frais')
    },
    {
        name: 'Historique des fiches',
        path: '/historique',
        icon: ClockIcon,
        current: computed(() => route.path === '/historique')
    },
    {
        name: 'Mise en paiement',
        path: '/payments',
        icon: CreditCardIcon,
        current: computed(() => route.path === '/payments')
    },
];

// Utiliser la navigation appropriée selon le rôle
const navigation = computed(() => {
    if (isAdministrateur.value) return administrateurNavigation;
    if (isVisiteurMedical.value) return visiteurMedicalNavigation;
    return comptableNavigation;
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
    <div class="h-screen flex flex-col bg-gradient-to-b from-indigo-900 to-indigo-800 overflow-hidden relative">
        <div class="relative z-10 w-full">
            <div class="flex items-center justify-center h-20 px-4">
                <h1 class="text-white text-4xl font-bold tracking-widest">
                    <span class="text-white">GSB</span>
                </h1>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-6 py-6 relative z-10">
            <div class="mb-6">
                <ul role="list" class="flex flex-col gap-y-1.5">
                    <li v-for="item in navigation" :key="item.name">
                        <router-link :to="item.path" :class="[
                            item.current
                                ? 'bg-white text-black shadow-md'
                                : 'text-gray-100 hover:bg-white/10',
                            'group flex items-center gap-x-3 rounded-xl p-3 text-sm font-medium transition-all duration-200'
                        ]">
                            <component :is="item.icon"
                                class="size-5 shrink-0 transition-transform duration-200 group-hover:scale-110"
                                :class="item.current ? 'text-black' : 'text-gray-200'" aria-hidden="true" />
                            {{ item.name }}
                        </router-link>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="w-full bg-gray-900/60 backdrop-blur-md relative z-10">
            <div class="p-4">
                <div class="flex items-center gap-x-4 relative">
                    <div @click="showProfileMenu = !showProfileMenu"
                        class="size-12 rounded-full border-2 border-indigo-400 flex items-center justify-center bg-indigo-700 text-white font-medium shadow-lg text-base transition-all duration-300 cursor-pointer hover:bg-indigo-600">
                        {{ getUserInitials }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="block truncate text-base text-white font-medium">
                                {{ userStore.userData?.email || 'Utilisateur' }}
                            </span>
                        </div>
                        <span class="text-xs font-medium text-indigo-200">
                            {{ formattedRole }}
                        </span>
                    </div>

                    <button @click="logout()"
                        class="p-2 rounded-full hover:bg-red-500/20 transition-all duration-300 group">
                        <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-400 group-hover:text-red-300" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { useUserStore } from '@/stores/user';
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const userStore = useUserStore();
const router = useRouter();
const route = useRoute();

import {
    HomeIcon,
    BanknotesIcon,
    UserGroupIcon,
    CalendarIcon,
    DocumentTextIcon,
    ChartBarIcon,
    ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline';

// Déterminer si l'utilisateur est un visiteur médical
const isVisiteurMedical = computed(() =>
    userStore.userData?.role?.toUpperCase() === 'VISITEUR_MEDICAL'
);

// Définition des navigations spécifiques en fonction du rôle
const visiteurMedicalNavigation = [
    { name: 'Fiche de frais', path: '/about/frais', icon: BanknotesIcon, current: computed(() => route.path === '/about/frais') },
    { name: 'Historique', path: '/about/historique', icon: CalendarIcon, current: computed(() => route.path === '/about/historique') },
];

const comptableNavigation = [
    { name: 'Gestion des paiements', path: '/about/paiement', icon: BanknotesIcon, current: computed(() => route.path === '/about/paiement') },
];

// Utiliser la navigation appropriée selon le rôle
const navigation = computed(() =>
    isVisiteurMedical.value ? visiteurMedicalNavigation : comptableNavigation
);

// Fonction pour obtenir les initiales de l'utilisateur
const getUserInitials = computed(() => {
    const username = userStore.userData?.username || '';
    if (!username) return 'UN'; // UN pour "User Name" par défaut

    // Si le nom est composé, prendre les initiales de chaque partie
    if (username.includes(' ')) {
        return username
            .split(' ')
            .map(part => part.charAt(0))
            .join('')
            .toUpperCase();
    }

    // Sinon retourner les deux premières lettres du nom
    return username.substring(0, 2).toUpperCase();
});

// Formater le rôle pour l'affichage
const formattedRole = computed(() => {
    const role = userStore.userData?.role || '';
    if (role.toUpperCase() === 'VISITEUR_MEDICAL') return 'Visiteur médical';
    if (role.toUpperCase() === 'COMPTABLE') return 'Comptable';
    return role;
});

const logout = async () => {
    try {
        const response = await fetch("http://51.83.76.210/gsb/backend/logout.php", {
            method: "POST",
            credentials: "include",
        });

        const data = await response.json();

        if (data.success) {
            router.push('/login');
            return true;
        } else {
            return false;
        }
    } catch (error) {
        return false;
    }
};
</script>

<template>
    <div class="h-screen flex flex-col bg-gradient-to-t from-gray-950 via-gray-900 to-pink-800 overflow-hidden">

        <div class="w-full">
            <div class="flex items-center justify-center h-20 px-4">
                <h1 class="text-white text-4xl font-bold tracking-widest relative z-10">
                    <span class="text-white">GSB</span>
                </h1>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-6 py-4">
            <ul role="list" class="flex flex-col gap-y-7">
                <li>
                    <ul role="list" class="-mx-2 space-y-1.5">
                        <li v-for="item in navigation" :key="item.name">
                            <router-link :to="item.path" :class="[
                                item.current
                                    ? 'bg-white text-black'
                                    : 'text-gray-300 hover:bg-gray-800/50 hover:text-white',
                                'group flex gap-x-3 rounded-md p-2.5 text-sm/6 font-semibold transition-all duration-200'
                            ]">
                                <component :is="item.icon"
                                    class="size-6 shrink-0 transition-transform duration-200 group-hover:scale-110"
                                    :class="item.current ? 'text-black' : 'text-gray-400 group-hover:text-pink-300'"
                                    aria-hidden="true" />
                                {{ item.name }}
                                <span v-if="item.count"
                                    class="ml-auto w-9 min-w-max rounded-full bg-pink-900/80 px-2.5 py-0.5 text-center text-xs/5 font-medium whitespace-nowrap text-white ring-1 ring-pink-700 ring-inset"
                                    aria-hidden="true">
                                    {{ item.count }}
                                </span>
                            </router-link>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- Section profil utilisateur -->
        <div class="w-full bg-gray-900/50">
            <div class="bg-gray-800/30 rounded-xl p-3 backdrop-blur-sm border border-pink-800/20">
                <div class="flex items-center gap-x-4 mb-3">
                    <div class="relative">
                        <div
                            class="size-12 rounded-full border-2 border-pink-500/30 flex items-center justify-center bg-gradient-to-br from-pink-600 to-pink-900 text-white font-medium shadow-lg text-base transition-all duration-300 hover:scale-105 hover:shadow-pink-600/20 hover:shadow-lg">
                            {{ getUserInitials }}
                        </div>
                        <!-- Indicateur en ligne -->
                        <span
                            class="absolute bottom-0 right-0 size-3 rounded-full bg-green-500 ring-2 ring-gray-900"></span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="block truncate font-semibold text-base text-white">
                                {{ userStore.userData?.username || 'Utilisateur' }}
                            </span>
                            <!-- Statut en ligne -->
                            <span
                                class="text-xs bg-green-500/20 text-green-400 px-2 py-0.5 rounded-full border border-green-500/20">
                                En ligne
                            </span>
                        </div>
                        <span class="text-xs font-medium text-pink-300/80">
                            {{ formattedRole }}
                        </span>
                    </div>
                </div>

                <button @click.prevent="logout()"
                    class="w-full py-2.5 px-4 text-sm bg-pink-800 rounded-lg text-white shadow-md transition-all duration-200 flex items-center justify-center gap-x-2 border border-pink-800/40 hover:shadow-lg hover:bg-pink-700 group">
                    <ArrowRightOnRectangleIcon
                        class="size-5 group-hover:translate-x-1 transition-transform duration-200" />
                    Déconnexion
                </button>
            </div>
        </div>
    </div>
</template>
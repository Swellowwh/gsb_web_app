import { createRouter, createWebHistory } from 'vue-router';

const checkSession = async () => {
  try {
    const response = await fetch("http://51.83.76.210/gsb/api/checkSession.php", {
      method: "POST",
      credentials: "include",
    });

    const data = await response.json();

    return data.success;
  } catch (error) {
    return false;
  }
};

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/login', name: 'login', component: () => import('../views/LoginView.vue'), },
    {
      path: '/',
      name: 'home',
      component: () => import('../views/HomeView.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '/frais',
          name: 'Frais',
          component: () => import('../views/Frais.vue'),
          meta: { requiresAuth: true, allowedRoles: ['VISITEUR_MEDICAL', 'ADMINISTRATEUR'] }
        },
        {
          path: '/historique',
          name: 'Historique',
          component: () => import('../views/Historique.vue'),
          meta: { requiresAuth: true, allowedRoles: ['VISITEUR_MEDICAL', 'ADMINISTRATEUR'] }
        },
        {
          path: '/employees',
          name: 'Employees',
          component: () => import('../views/Employees.vue'),
          meta: { requiresAuth: true, allowedRoles: ['ADMINISTRATEUR'] }
        },
        {
          path: '/payments',
          name: 'Payments',
          component: () => import('../views/Payments.vue'),
          meta: { requiresAuth: true, allowedRoles: ['COMPTABLE', 'ADMINISTRATEUR'] }
        },    
      ]
    },
    { path: '/:pathMatch(.*)*', name: '404', component: () => import('../views/404.vue') }
  ],
});

// 🔹 Vérification de session avant chaque changement de route
import { useUserStore } from '@/stores/user';
import { storeToRefs } from 'pinia';

router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore();
  const { userData } = storeToRefs(userStore);

  // Vérification session si nécessaire
  if (to.meta.requiresAuth) {
    const isAuthenticated = await checkSession();

    if (!isAuthenticated) {
      userStore.reset(); // reset si session expirée
      return next('/login');
    }

    const userRole = userData.value?.role?.toUpperCase() || '';

    const allowedRoles = to.meta.allowedRoles;

    if (allowedRoles && !allowedRoles.includes(userRole)) {
      console.warn(`Accès refusé à ${to.path} pour le rôle : ${userRole}`);
      return next('/'); // ou une page "non autorisé"
    }
  }

  next();
});


export default router;
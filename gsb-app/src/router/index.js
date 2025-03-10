import { createRouter, createWebHistory } from 'vue-router';

const checkSession = async () => {
  try {
    const response = await fetch("http://51.83.76.210/gsb/backend/checkSession.php", {
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
    {
      path: '/',
      name: 'dashboard',
      component: () => import('../views/Dashboard.vue'),
      meta: { requiresAuth: true },
    },
    { path: '/login', name: 'login', component: () => import('../views/LoginView.vue'), },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: 'frais',
          name: 'Frais',
          component: () => import('../views/Frais.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'historique',
          name: 'Historique',
          component: () => import('../views/Historique.vue'),
          meta: { requiresAuth: true }
        }
      ]
    },
    { path: '/:pathMatch(.*)*', name: '404', component: () => import('../views/404.vue') }
  ],
});

// 🔹 Vérification de session avant chaque changement de route
router.beforeEach(async (to, from, next) => {
  console.log(`Navigation vers: ${to.path} (auth requise: ${to.meta.requiresAuth ? 'oui' : 'non'})`);

  if (to.meta.requiresAuth) {
    console.log("Vérification de l'authentification...");
    const isAuthenticated = await checkSession();
    console.log("Authentifié:", isAuthenticated);

    if (!isAuthenticated) {
      console.log("Non authentifié, redirection vers la page d'accueil");
      return next('/login');
    }
    console.log("Authentifié, accès autorisé");
  }
  next();
});

export default router;
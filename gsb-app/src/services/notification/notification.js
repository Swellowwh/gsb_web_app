import { ref, readonly } from 'vue';

// Créer un état global pour les notifications
const notifications = ref([]);

// ID unique pour chaque notification
let notificationId = 0;

// Fonction pour supprimer une notification par son ID
const removeNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id);
  if (index !== -1) {
    notifications.value.splice(index, 1);
  }
};

// Fonction pour ajouter une notification de type success
const NotifSuccess = (message, options = {}) => {
  addNotification({
    type: 'success',
    message,
    ...options
  });
};

// Fonction pour ajouter une notification de type error
const NotifError = (message, options = {}) => {
  addNotification({
    type: 'error',
    message,
    ...options
  });
};

// Fonction pour ajouter une notification de type warning
const NotifWarning = (message, options = {}) => {
  addNotification({
    type: 'warning',
    message,
    ...options
  });
};

// Fonction pour ajouter une notification de type info
const NotifInfo = (message, options = {}) => {
  addNotification({
    type: 'info',
    message,
    ...options
  });
};

// Fonction interne pour ajouter une notification
const addNotification = (notification) => {
  const id = notificationId++;
  const title = notification.title || getDefaultTitle(notification.type);
  const duration = notification.duration || 5000; // Durée par défaut: 5 secondes

  // Ajouter la notification à la liste
  notifications.value.push({
    id,
    title,
    ...notification
  });

  // Supprimer automatiquement la notification après la durée spécifiée
  setTimeout(() => {
    removeNotification(id);
  }, duration);

  return id;
};

// Fonction utilitaire pour obtenir un titre par défaut selon le type
const getDefaultTitle = (type) => {
  switch (type) {
    case 'success':
      return 'Succès';
    case 'error':
      return 'Erreur';
    case 'warning':
      return 'Attention';
    case 'info':
    default:
      return 'Information';
  }
};

// Exporter le service de notification
export const useNotificationService = () => {
  return {
    notifications: readonly(notifications),
    removeNotification,
    NotifSuccess,
    NotifError,
    NotifWarning,
    NotifInfo
  };
};
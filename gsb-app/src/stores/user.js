import { ref, computed } from 'vue';
import { defineStore } from 'pinia';

export const useUserStore = defineStore('user', () => {
  
  const userData = ref({
    username: '',
    role: ''
  });

  const username = computed(() => userData.value.username);
  const role = computed(() => userData.value.role);

  function setUser(user) {
    userData.value = { 
      username: user.username || 'Invité', 
      role: user.role || 'Aucun rôle' 
    };
  }

  function reset() {
    userData.value = {
      username: '',
      role: ''
    };
  }

  return { userData, username, role, setUser, reset };
});
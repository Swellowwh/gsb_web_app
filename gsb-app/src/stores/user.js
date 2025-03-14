import { ref, computed } from 'vue';
import { defineStore } from 'pinia';

export const useUserStore = defineStore('user', () => {

  const userData = ref({
    email: '',
    role: ''
  });

  const email = computed(() => userData.value.email);
  const role = computed(() => userData.value.role);

  function setUser(user) {
    userData.value = {
      email: user.email,
      role: user.role,
    };
  }

  function reset() {
    userData.value = {
      email: '',
      role: ''
    };
  }

  return { userData, email, role, setUser, reset };
});
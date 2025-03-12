import { ref, computed } from 'vue';
import { defineStore } from 'pinia';

export const useTarifFraisForfait = defineStore('tarif_fiche_frais', () => {

  const tauxRemboursement = ref({
    km: '',
  });

  return {};
});

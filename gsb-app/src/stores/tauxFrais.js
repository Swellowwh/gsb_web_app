import { ref } from 'vue';
import { defineStore } from 'pinia';

export const useTauxFraisStore = defineStore('tauxFrais', () => {
  const tauxFrais = ref({
    KM: { code: 'KM', libelle: 'kilomètre', taux: 0.00 },
    NUI: { code: 'NUI', libelle: 'Nuitée', taux: 0.00 },
    REP: { code: 'REP', libelle: 'Repas', taux: 0.00 }
  });
  
  function setTaux(serverTaux) {
    if (!serverTaux) return;
    
    Object.keys(serverTaux).forEach(code => {
      if (tauxFrais.value[code]) {
        tauxFrais.value[code].taux = parseFloat(serverTaux[code].taux);
      }
    });
  }

  return { 
    tauxFrais,
    setTaux
  };
});
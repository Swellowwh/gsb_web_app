<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { useNotificationService } from '@/services/notification/notification';
import { useTauxFraisStore } from '@/stores/tauxFrais';

const { NotifSuccess, NotifError } = useNotificationService();
const tauxFraisStore = useTauxFraisStore();

const props = defineProps({
  id: { type: Number, required: true },
  modelValue: { type: Boolean, required: true }
});
const emit = defineEmits(['update:modelValue', 'updated']);

const details = ref([]);
const isLoading = ref(false);

function closeModal() {
  emit('update:modelValue', false);
  details.value = [];
}

function getTypeLabel(code) {
  switch (code) {
    case 'KM': return 'Kilométrie';
    case 'REP': return 'Repas';
    case 'NUI': return 'Hébergement';
    default: return code;
  }
}

async function fetchDetail(ficheId) {
  isLoading.value = true;
  try {
    const res = await fetch('http://51.83.76.210/gsb/api/getFicheDetails.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ ficheId })
    });

    const data = await res.json();
    if (data.success) {
      details.value = data.details;
    } else {
      NotifError(data.message || 'Erreur de chargement des détails');
    }
  } catch (err) {
    NotifError('Erreur serveur lors du chargement');
  } finally {
    isLoading.value = false;
  }
}

// Watch ouverture modal
watch(() => props.modelValue, (open) => {
  if (open && props.id) fetchDetail(props.id);
});
onMounted(() => {
  if (props.modelValue && props.id) fetchDetail(props.id);
});

// Calcul du montant dynamique selon les taux du store
function computeMontant(type, quantite) {
  const taux = tauxFraisStore.tauxFrais[type]?.taux || 0;
  return (quantite || 0) * taux;
}

async function sauvegarderModifications() {
  isLoading.value = true;

  const payload = details.value.map(d => ({
    detailId: d.id,
    ficheId: d.fiche_id,
    quantite: parseFloat(d.quantite)
  }));

  try {
    const res = await fetch('http://51.83.76.210/gsb/api/updateDetail.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (data.success) {
      NotifSuccess(data.message || 'Fiche mise à jour');
      emit('updated');
      closeModal();
    } else {
      NotifError(data.message || 'Erreur lors de la mise à jour');
    }
  } catch (err) {
    NotifError('Erreur de communication avec le serveur');
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">
      <h2 class="text-xl font-bold mb-4">Modifier les détails de la fiche</h2>

      <div v-if="details.length" class="space-y-4">
        <div v-for="(d, index) in details" :key="d.id" class="border-b pb-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Type : {{ getTypeLabel(d.type) }}
            </label>
            <input
              type="number"
              min="0"
              step="0.01"
              v-model="d.quantite"
              class="mt-1 w-full border rounded p-2"
            />
          </div>

          <div v-if="d.quantite" class="mt-2 text-right text-sm text-gray-600">
            {{ d.quantite }} × {{ tauxFraisStore.tauxFrais[d.type]?.taux }} € =
            <span class="font-medium text-indigo-600">
              {{ computeMontant(d.type, d.quantite).toFixed(2) }} €
            </span>
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
          <button @click="closeModal" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Annuler</button>
          <button
            @click="sauvegarderModifications"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Sauvegarder
          </button>
        </div>
      </div>

      <div v-else class="text-center py-10 text-gray-500">
        Chargement des données...
      </div>
    </div>
  </div>
</template>

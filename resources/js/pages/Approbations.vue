<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

// Props reçues du controller
const props = defineProps<{
    presences: {
        data: Array<{
            id: number;
            user: {
                id: number;
                name: string;
                matricule: string;
            };
            date_presence: string;
            heure_arrivee: string;
            heure_depart: string | null;
            description: string;
            statut:
                | 'arrivee_en_attente'
                | 'depart_en_attente'
                | 'arrivee_approuvee'
                | 'validee'
                | 'rejetee';
            justificatifs: Array<{
                id: number;
                nom_fichier: string;
                chemin_fichier: string;
                type_fichier: string;
            }>;
        }>;
        links: any;
    };
    stats: {
        arrivees_attente: number;
        departs_attente: number;
        total_attente: number;
    };
    employes: Array<{
        id: number;
        name: string;
        matricule: string;
    }>;
    filters: {
        type?: string;
        employe_id?: number;
        date?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Approbations',
        href: '/admin/approbations',
    },
];

// États pour les filtres
const selectedType = ref(props.filters.type || 'tous');
const selectedEmploye = ref(props.filters.employe_id || '');
const selectedDate = ref(props.filters.date || '');

// État pour la sélection multiple
const selectedIds = ref<number[]>([]);
const selectAll = ref(false);

// État pour le modal de rejet
const showRejectModal = ref(false);
const rejectMotif = ref('');
const currentPresence = ref<any>(null);

// État pour le modal de détail
const showDetailModal = ref(false);
const detailPresence = ref<any>(null);

// Couleurs des statuts
const statutColors = {
    arrivee_en_attente: 'bg-yellow-100 text-yellow-800',
    depart_en_attente: 'bg-orange-100 text-orange-800',
    arrivee_approuvee: 'bg-blue-100 text-blue-800',
    validee: 'bg-green-100 text-green-800',
    rejetee: 'bg-red-100 text-red-800',
};

// Libellés des statuts
const statutLabels = {
    arrivee_en_attente: 'Arrivée en attente',
    depart_en_attente: 'Départ en attente',
    arrivee_approuvee: 'Arrivée approuvée',
    validee: 'Validée',
    rejetee: 'Rejetée',
};

// Gérer la sélection de toutes les présences
const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedIds.value = props.presences.data.map((p) => p.id);
    } else {
        selectedIds.value = [];
    }
};

// Vérifier si une présence est sélectionnée
const isSelected = (id: number) => selectedIds.value.includes(id);

// Basculer la sélection d'une présence
const toggleSelect = (id: number) => {
    const index = selectedIds.value.indexOf(id);
    if (index === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(index, 1);
    }
};

// Appliquer les filtres - URL en dur
const appliquerFiltres = () => {
    router.get(
        '/admin/approbations',
        {
            type:
                selectedType.value !== 'tous' ? selectedType.value : undefined,
            employe_id: selectedEmploye.value || undefined,
            date: selectedDate.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Réinitialiser les filtres
const resetFiltres = () => {
    selectedType.value = 'tous';
    selectedEmploye.value = '';
    selectedDate.value = '';
    appliquerFiltres();
};

// Approuver une arrivée - URL en dur
const approuverArrivee = (presence: any) => {
    router.post(
        `/admin/approbations/${presence.id}/approuver-arrivee`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
            },
        },
    );
};

// Approuver un départ - URL en dur
const approuverDepart = (presence: any) => {
    router.post(
        `/admin/approbations/${presence.id}/approuver-depart`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
            },
        },
    );
};

// Ouvrir le modal de rejet
const ouvrirRejet = (presence: any) => {
    currentPresence.value = presence;
    rejectMotif.value = '';
    showRejectModal.value = true;
};

// Confirmer le rejet - URL en dur
const confirmerRejet = () => {
    router.post(
        `/admin/approbations/${currentPresence.value.id}/rejeter`,
        {
            motif: rejectMotif.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showRejectModal.value = false;
                selectedIds.value = [];
            },
        },
    );
};

// Approbation en lot - URL en dur
const approuverLot = (action: 'approuver_arrivee' | 'approuver_depart') => {
    if (selectedIds.value.length === 0) return;

    router.post(
        '/admin/approbations/lot',
        {
            ids: selectedIds.value,
            action: action,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
            },
        },
    );
};

// Voir les détails - URL en dur
const voirDetails = (presence: any) => {
    router.get(`/admin/approbations/${presence.id}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Formater l'heure
const formatHeure = (datetime: string) => {
    return new Date(datetime).toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Formater la date
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Approbations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- En-tête -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Gestion des approbations</h1>
                <div class="text-sm text-gray-500">
                    {{
                        new Date().toLocaleDateString('fr-FR', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                        })
                    }}
                </div>
            </div>

            <!-- LIGNE 1: Stats KPI (3 cartes) -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <!-- Carte: Arrivées en attente -->
                <div class="rounded-xl border bg-white p-6 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">
                                Arrivées en attente
                            </p>
                            <p class="text-3xl font-bold text-yellow-600">
                                {{ stats.arrivees_attente }}
                            </p>
                        </div>
                        <div class="rounded-full bg-yellow-100 p-3">
                            <svg
                                class="h-6 w-6 text-yellow-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Carte: Départs en attente -->
                <div class="rounded-xl border bg-white p-6 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">
                                Départs en attente
                            </p>
                            <p class="text-3xl font-bold text-orange-600">
                                {{ stats.departs_attente }}
                            </p>
                        </div>
                        <div class="rounded-full bg-orange-100 p-3">
                            <svg
                                class="h-6 w-6 text-orange-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Carte: Total en attente -->
                <div class="rounded-xl border bg-white p-6 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total à traiter</p>
                            <p class="text-3xl font-bold text-blue-600">
                                {{ stats.total_attente }}
                            </p>
                        </div>
                        <div class="rounded-full bg-blue-100 p-3">
                            <svg
                                class="h-6 w-6 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LIGNE 2: Filtres et actions en lot -->
            <div class="rounded-xl border bg-white p-6 dark:bg-gray-900">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Filtre par type -->
                    <div class="min-w-[200px] flex-1">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Type</label
                        >
                        <select
                            v-model="selectedType"
                            class="w-full rounded-lg border border-gray-300 p-2.5 text-sm"
                        >
                            <option value="tous">Tous</option>
                            <option value="arrivee">Arrivées en attente</option>
                            <option value="depart">Départs en attente</option>
                        </select>
                    </div>

                    <!-- Filtre par employé -->
                    <div class="min-w-[200px] flex-1">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Employé</label
                        >
                        <select
                            v-model="selectedEmploye"
                            class="w-full rounded-lg border border-gray-300 p-2.5 text-sm"
                        >
                            <option value="">Tous les employés</option>
                            <option
                                v-for="emp in employes"
                                :key="emp.id"
                                :value="emp.id"
                            >
                                {{ emp.name }} ({{ emp.matricule }})
                            </option>
                        </select>
                    </div>

                    <!-- Filtre par date -->
                    <div class="min-w-[200px] flex-1">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Date</label
                        >
                        <input
                            type="date"
                            v-model="selectedDate"
                            class="w-full rounded-lg border border-gray-300 p-2.5 text-sm"
                        />
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex items-end gap-2">
                        <button
                            @click="appliquerFiltres"
                            class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            Filtrer
                        </button>
                        <button
                            @click="resetFiltres"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold hover:bg-gray-50"
                        >
                            Réinitialiser
                        </button>
                    </div>
                </div>

                <!-- Actions en lot (apparaissent si des éléments sont sélectionnés) -->
                <div
                    v-if="selectedIds.length > 0"
                    class="mt-4 flex items-center gap-3 border-t pt-4"
                >
                    <span class="text-sm font-medium"
                        >{{ selectedIds.length }} sélectionné(s)</span
                    >
                    <button
                        @click="approuverLot('approuver_arrivee')"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                    >
                        ✅ Approuver arrivées
                    </button>
                    <button
                        @click="approuverLot('approuver_depart')"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        ✅ Approuver départs
                    </button>
                    <button
                        @click="selectedIds = []"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                </div>
            </div>

            <!-- LIGNE 3: Tableau des présences -->
            <div class="rounded-xl border bg-white p-6 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="w-10 px-4 py-3">
                                    <input
                                        type="checkbox"
                                        v-model="selectAll"
                                        @change="toggleSelectAll"
                                        class="rounded"
                                    />
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Employé
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Arrivée
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Départ
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Statut
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Justificatifs
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="presence in presences.data"
                                :key="presence.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        :checked="isSelected(presence.id)"
                                        @change="toggleSelect(presence.id)"
                                        class="rounded"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium">
                                        {{ presence.user.name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ presence.user.matricule }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ formatDate(presence.date_presence) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ formatHeure(presence.heure_arrivee) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{
                                        presence.heure_depart
                                            ? formatHeure(presence.heure_depart)
                                            : '-'
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                        :class="statutColors[presence.statut]"
                                    >
                                        {{ statutLabels[presence.statut] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        v-if="presence.justificatifs?.length"
                                        class="text-sm text-blue-600"
                                    >
                                        {{ presence.justificatifs.length }}
                                        fichier(s)
                                    </span>
                                    <span v-else class="text-sm text-gray-400"
                                        >-</span
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <button
                                            v-if="
                                                presence.statut ===
                                                'arrivee_en_attente'
                                            "
                                            @click="approuverArrivee(presence)"
                                            class="rounded bg-green-100 px-2 py-1 text-xs font-medium text-green-800 hover:bg-green-200"
                                        >
                                            ✅ Approuver
                                        </button>
                                        <button
                                            v-if="
                                                presence.statut ===
                                                'depart_en_attente'
                                            "
                                            @click="approuverDepart(presence)"
                                            class="rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 hover:bg-blue-200"
                                        >
                                            ✅ Valider
                                        </button>
                                        <button
                                            @click="ouvrirRejet(presence)"
                                            class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-800 hover:bg-red-200"
                                        >
                                            ❌ Rejeter
                                        </button>
                                        <button
                                            @click="voirDetails(presence)"
                                            class="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800 hover:bg-gray-200"
                                        >
                                            👁️ Détails
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!presences.data?.length">
                                <td
                                    colspan="8"
                                    class="px-4 py-8 text-center text-gray-500"
                                >
                                    Aucune présence en attente
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <!-- Pagination -->
                <div
                    v-if="presences.links && presences.links.length > 3"
                    class="mt-6 flex justify-center"
                >
                    <nav
                        class="flex items-center gap-1"
                        aria-label="Pagination"
                    >
                        <Link
                            v-for="link in presences.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="rounded-md border px-3 py-1 text-sm"
                            :class="{
                                'border-blue-600 bg-blue-600 text-white':
                                    link.active,
                                'border-gray-300 bg-white text-gray-700 hover:bg-gray-50':
                                    !link.active && link.url,
                                'cursor-not-allowed border-gray-200 text-gray-400':
                                    !link.url,
                            }"
                            v-html="link.label"
                            preserve-scroll
                        />
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Modal de rejet -->
    <div
        v-if="showRejectModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-xl bg-white p-6 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold">Motif du rejet</h3>
            <textarea
                v-model="rejectMotif"
                rows="4"
                class="w-full rounded-lg border border-gray-300 p-3 text-sm"
                placeholder="Expliquez pourquoi vous rejetez cette présence..."
            ></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button
                    @click="showRejectModal = false"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold hover:bg-gray-50"
                >
                    Annuler
                </button>
                <button
                    @click="confirmerRejet"
                    :disabled="!rejectMotif || rejectMotif.length < 10"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                >
                    Confirmer le rejet
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de détail -->
    <div
        v-if="showDetailModal && detailPresence"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-2xl rounded-xl bg-white p-6 dark:bg-gray-900">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold">Détail de la présence</h3>
                <button
                    @click="showDetailModal = false"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Employé</p>
                        <p class="font-medium">
                            {{ detailPresence.user.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ detailPresence.user.matricule }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Date</p>
                        <p class="font-medium">
                            {{ formatDate(detailPresence.date_presence) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Arrivée</p>
                        <p class="font-medium">
                            {{ formatHeure(detailPresence.heure_arrivee) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Départ</p>
                        <p class="font-medium">
                            {{
                                detailPresence.heure_depart
                                    ? formatHeure(detailPresence.heure_depart)
                                    : '-'
                            }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Description</p>
                    <p class="mt-1 rounded-lg bg-gray-50 p-3 text-sm">
                        {{ detailPresence.description }}
                    </p>
                </div>

                <div v-if="detailPresence.justificatifs?.length">
                    <p class="text-sm text-gray-500">Justificatifs</p>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <div
                            v-for="file in detailPresence.justificatifs"
                            :key="file.id"
                            class="rounded-lg border p-2"
                        >
                            <a
                                :href="file.chemin_fichier"
                                target="_blank"
                                class="text-sm text-blue-600 hover:underline"
                            >
                                {{ file.nom_fichier }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button
                    @click="showDetailModal = false"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold hover:bg-gray-50"
                >
                    Fermer
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

// Props
const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        matricule: string;
        role: string;
        date_inscription: string;
    };
    stats_totales: {
        heures_attendues: number;
        heures_reelles: number;
        heures_manquantes: number;
        pourcentage: number;
        en_retard: boolean;
        periode: {
            debut: string;
            fin: string;
        };
    };
    stats_par_mois: Array<any>;
    stats_par_semaine: Array<any>;
    heures_supplementaires_total: number;
    presences: {
        data: Array<{
            id: number;
            date_presence: string;
            heure_arrivee: string;
            heure_depart: string | null;
            heures_travaillees: number;
            heures_supplementaires: number;
            description: string;
            statut: string;
            justificatifs: Array<{
                id: number;
                nom_fichier: string;
            }>;
            approbations: Array<any>;
        }>;
        links: any;
        meta: any;
    };
    historique_stats: {
        total_presences: number;
        validees: number;
        en_attente_approbation: number;
        rejetees: number;
        total_justificatifs: number;
    };
    filters: {
        statut?: string;
    };
}>();

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Accueil', href: '/' },
    { title: 'Admin', href: '/admin' },
    { title: 'Utilisateurs', href: '/admin/users' },
    { title: props.user.name, href: '#' },
];

// État
const selectedStatut = ref(props.filters?.statut || '');

// Statuts
const statuts = [
    { value: '', label: 'Tous les statuts' },
    { value: 'arrivee_en_attente', label: 'Arrivée en attente' },
    { value: 'depart_en_attente', label: 'Départ en attente' },
    { value: 'arrivee_approuvee', label: 'Arrivée approuvée' },
    { value: 'validee', label: 'Validée' },
    { value: 'rejetee', label: 'Rejetée' },
];

// Couleurs des statuts
const statusColors: { [key: string]: string } = {
    arrivee_en_attente:
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300',
    depart_en_attente:
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300',
    arrivee_approuvee:
        'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
    validee:
        'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300',
    rejetee: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300',
};

const statusLabels: { [key: string]: string } = {
    arrivee_en_attente: '⏳ Arrivée en attente',
    depart_en_attente: '⏳ Départ en attente',
    arrivee_approuvee: '✅ Arrivée approuvée',
    validee: '✅ Validée',
    rejetee: '❌ Rejetée',
};

// Appliquer le filtre
const applyFilter = () => {
    router.get(`/admin/employes/${props.user.id}/detail`, {
        statut: selectedStatut.value,
    });
};

// Réinitialiser le filtre
const resetFilter = () => {
    selectedStatut.value = '';
    router.get(`/admin/employes/${props.user.id}/detail`);
};

// Formater l'heure
const formatHeure = (datetime: string | null) => {
    if (!datetime) return '--:--';
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

// Couleur basée sur le pourcentage
const getColorClass = (pourcentage: number) => {
    if (pourcentage >= 90)
        return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
    if (pourcentage >= 80)
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
    if (pourcentage >= 60)
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
    return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
};

// Aller aux détails de la présence
const voirDetails = (presentation_id: number) => {
    router.get(`/admin/approbations/${presentation_id}`);
};
</script>

<template>
    <Head :title="`Détails - ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header avec info utilisateur -->
            <div
                class="rounded-lg border bg-linear-to-r from-blue-50 to-purple-50 p-6 dark:from-blue-900/30 dark:to-purple-900/30"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">👤 {{ user.name }}</h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">
                            <strong>Email:</strong> {{ user.email }} |
                            <strong>Matricule:</strong>
                            <span class="font-mono font-semibold">{{
                                user.matricule
                            }}</span>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Inscrit depuis:
                            {{
                                new Date(
                                    user.date_inscription,
                                ).toLocaleDateString('fr-FR')
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats rapides en cards -->
            <div class="grid gap-4 md:grid-cols-5">
                <!-- Total présences -->
                <div
                    class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-700 dark:bg-blue-900/20"
                >
                    <p
                        class="text-sm font-medium text-gray-600 dark:text-gray-300"
                    >
                        📅 Total Présences
                    </p>
                    <p
                        class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-300"
                    >
                        {{ historique_stats.total_presences }}
                    </p>
                </div>

                <!-- Presences validées -->
                <div
                    class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700 dark:bg-green-900/20"
                >
                    <p
                        class="text-sm font-medium text-gray-600 dark:text-gray-300"
                    >
                        ✅ Validées
                    </p>
                    <p
                        class="mt-2 text-3xl font-bold text-green-600 dark:text-green-300"
                    >
                        {{ historique_stats.validees }}
                    </p>
                </div>

                <!-- En attente d'approbation -->
                <div
                    class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-700 dark:bg-yellow-900/20"
                >
                    <p
                        class="text-sm font-medium text-gray-600 dark:text-gray-300"
                    >
                        ⏳ En attente
                    </p>
                    <p
                        class="mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-300"
                    >
                        {{ historique_stats.en_attente_approbation }}
                    </p>
                </div>

                <!-- Rejetées -->
                <div
                    class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-700 dark:bg-red-900/20"
                >
                    <p
                        class="text-sm font-medium text-gray-600 dark:text-gray-300"
                    >
                        ❌ Rejetées
                    </p>
                    <p
                        class="mt-2 text-3xl font-bold text-red-600 dark:text-red-300"
                    >
                        {{ historique_stats.rejetees }}
                    </p>
                </div>

                <!-- Justificatifs -->
                <div
                    class="rounded-lg border border-purple-200 bg-purple-50 p-4 dark:border-purple-700 dark:bg-purple-900/20"
                >
                    <p
                        class="text-sm font-medium text-gray-600 dark:text-gray-300"
                    >
                        📎 Justificatifs
                    </p>
                    <p
                        class="mt-2 text-3xl font-bold text-purple-600 dark:text-purple-300"
                    >
                        {{ historique_stats.total_justificatifs }}
                    </p>
                </div>
            </div>

            <!-- Stats globales -->
            <div class="rounded-lg border bg-white p-6 dark:bg-gray-900">
                <h2 class="mb-4 text-xl font-bold">📊 Statistiques Globales</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Heures Attendues
                        </p>
                        <p class="text-3xl font-bold">
                            {{ stats_totales.heures_attendues.toFixed(1) }}h
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Heures Réelles
                        </p>
                        <p class="text-3xl font-bold">
                            {{ stats_totales.heures_reelles.toFixed(1) }}h
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Heures Manquantes
                        </p>
                        <p
                            :class="[
                                'text-3xl font-bold',
                                stats_totales.heures_manquantes > 0
                                    ? 'text-red-600'
                                    : 'text-green-600',
                            ]"
                        >
                            {{ stats_totales.heures_manquantes.toFixed(1) }}h
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Taux de Présence
                        </p>
                        <p
                            :class="[
                                'text-3xl font-bold',
                                getColorClass(stats_totales.pourcentage),
                            ]"
                        >
                            {{ stats_totales.pourcentage.toFixed(1) }}%
                        </p>
                    </div>
                </div>
                <div
                    v-if="heures_supplementaires_total > 0"
                    class="mt-4 rounded-lg bg-purple-50 p-3 dark:bg-purple-900/20"
                >
                    <p
                        class="text-sm font-medium text-purple-700 dark:text-purple-300"
                    >
                        ⭐ Heures Supplémentaires:
                        <strong
                            >{{
                                heures_supplementaires_total.toFixed(1)
                            }}h</strong
                        >
                    </p>
                </div>
            </div>

            <!-- ===== HISTORIQUE DES PRESENCES ===== -->
            <div class="rounded-lg border bg-white p-6 dark:bg-gray-900">
                <h2 class="mb-6 text-xl font-bold">
                    📋 Historique des Présences
                </h2>

                <!-- Filtres -->
                <div class="mb-6 flex gap-2">
                    <select
                        v-model="selectedStatut"
                        @change="applyFilter"
                        class="rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800"
                    >
                        <option
                            v-for="s in statuts"
                            :key="s.value"
                            :value="s.value"
                        >
                            {{ s.label }}
                        </option>
                    </select>
                    <button
                        @click="resetFilter"
                        class="rounded-lg border border-gray-300 px-4 py-2 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-800"
                    >
                        Réinitialiser
                    </button>
                </div>

                <!-- Tableau des presences -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Arrivée
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Départ
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-sm font-semibold"
                                >
                                    Heures
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-sm font-semibold"
                                >
                                    Supp
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Justif
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Statut
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-sm font-semibold"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="presence in presences.data"
                                :key="presence.id"
                                class="border-b hover:bg-gray-50 dark:hover:bg-gray-800"
                            >
                                <td class="px-4 py-3">
                                    {{ formatDate(presence.date_presence) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ formatHeure(presence.heure_arrivee) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{
                                        presence.heure_depart
                                            ? formatHeure(presence.heure_depart)
                                            : '---'
                                    }}
                                </td>
                                <td class="px-4 py-3 text-center font-semibold">
                                    {{ presence.heures_travaillees }}h
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        v-if="
                                            presence.heures_supplementaires > 0
                                        "
                                        class="rounded bg-purple-100 px-2 py-1 text-xs font-bold text-purple-700 dark:bg-purple-900/20 dark:text-purple-300"
                                    >
                                        +{{ presence.heures_supplementaires }}h
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        v-if="presence.justificatifs.length > 0"
                                        class="rounded-full bg-blue-100 px-2 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/20"
                                    >
                                        {{ presence.justificatifs.length }} 📎
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'rounded px-2 py-1 text-xs font-bold',
                                            statusColors[presence.statut],
                                        ]"
                                    >
                                        {{
                                            statusLabels[presence.statut] ||
                                            presence.statut
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        @click="voirDetails(presence.id)"
                                        class="text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        Voir →
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="presences.links"
                    class="mt-6 flex items-center justify-center gap-2"
                >
                    <Link
                        v-for="link in presences.links"
                        :key="link.url || '#'"
                        :href="link.url || '#'"
                        :class="[
                            'rounded px-3 py-2 text-sm',
                            link.active
                                ? 'bg-blue-600 text-white'
                                : link.url
                                  ? 'border hover:bg-gray-100 dark:hover:bg-gray-800'
                                  : 'cursor-not-allowed opacity-50',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>

            <!-- Retour -->
            <Link
                href="/admin/users"
                class="inline-block text-blue-600 hover:underline"
            >
                ← Retour aux utilisateurs
            </Link>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';

// Props
const props = defineProps<{
    employes: Array<{
        id: number;
        name: string;
        matricule: string;
        total_heures: number;
        heures_attendues: number;
        pourcentage: number;
        en_retard: boolean;
    }>;
}>();

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Accueil', href: '/' },
    { title: 'Admin', href: '/admin' },
    { title: 'Rapports', href: '/admin/rapports' },
];

// Tri et filtrage
const sortBy = ref('pourcentage');
const filterRetard = ref(false);

/**
 * Employés triés etfiltrés
 */
const emploiesFiltres = computed(() => {
    let result = [...props.employes];

    // Filtre: en retard ou pas
    if (filterRetard.value) {
        result = result.filter((e) => e.en_retard);
    }

    // Tri
    if (sortBy.value === 'pourcentage') {
        result.sort((a, b) => a.pourcentage - b.pourcentage);
    } else if (sortBy.value === 'heures') {
        result.sort((a, b) => a.total_heures - b.total_heures);
    } else if (sortBy.value === 'nom') {
        result.sort((a, b) => a.name.localeCompare(b.name));
    }

    return result;
});

/**
 * Statistiques globales
 */
const statsGlobales = computed(() => {
    const total = props.employes.length;
    const enRetard = props.employes.filter((e) => e.en_retard).length;
    const totalHeures = props.employes.reduce(
        (sum, e) => sum + e.total_heures,
        0,
    );
    const totalAttendues = props.employes.reduce(
        (sum, e) => sum + e.heures_attendues,
        0,
    );
    const moyennePourcentage =
        total > 0
            ? (
                  props.employes.reduce((sum, e) => sum + e.pourcentage, 0) /
                  total
              ).toFixed(2)
            : 0;

    return {
        total,
        enRetard,
        totalHeures: totalHeures.toFixed(1),
        totalAttendues: totalAttendues.toFixed(1),
        moyennePourcentage,
    };
});

/**
 * Couleur du pourcentage
 */
const getColorClass = (pourcentage: number) => {
    if (pourcentage >= 90)
        return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
    if (pourcentage >= 80)
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
    if (pourcentage >= 60)
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
    return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
};

/**
 * Icon basé sur pourcentage
 */
const getIcon = (pourcentage: number) => {
    if (pourcentage >= 80) return '✅';
    if (pourcentage >= 60) return '⚠️';
    return '🚨';
};
</script>

<template>
    <Head title="Rapports - Heures travaillées" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Titre -->
            <div>
                <h1 class="text-3xl font-bold">
                    📊 Rapports - Heures travaillées
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">
                    Vue complète des heures depuis l'inscription jusqu'à
                    maintenant
                </p>
            </div>

            <!-- ===== STATS GLOBALES ===== -->
            <div class="grid gap-4 md:grid-cols-5">
                <div
                    class="rounded-lg border bg-linear-to-br from-purple-50 to-purple-100 p-4 dark:from-purple-900/30 dark:to-purple-900/20"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        👥 Total employés
                    </p>
                    <p class="text-2xl font-bold">{{ statsGlobales.total }}</p>
                </div>
                <div
                    class="rounded-lg border bg-linear-to-br from-red-50 to-red-100 p-4 dark:from-red-900/30 dark:to-red-900/20"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        🚨 En retard
                    </p>
                    <p class="text-2xl font-bold">
                        {{ statsGlobales.enRetard }}
                    </p>
                </div>
                <div
                    class="rounded-lg border bg-linear-to-br from-green-50 to-green-100 p-4 dark:from-green-900/30 dark:to-green-900/20"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        ⏱️ Total heures
                    </p>
                    <p class="text-2xl font-bold">
                        {{ statsGlobales.totalHeures }}h
                    </p>
                </div>
                <div
                    class="rounded-lg border bg-linear-to-br from-blue-50 to-blue-100 p-4 dark:from-blue-900/30 dark:to-blue-900/20"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        📍 Attendues
                    </p>
                    <p class="text-2xl font-bold">
                        {{ statsGlobales.totalAttendues }}h
                    </p>
                </div>
                <div
                    class="rounded-lg border bg-linear-to-br from-yellow-50 to-yellow-100 p-4 dark:from-yellow-900/30 dark:to-yellow-900/20"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        % Moyenne
                    </p>
                    <p class="text-2xl font-bold">
                        {{ statsGlobales.moyennePourcentage }}%
                    </p>
                </div>
            </div>

            <!-- ===== FILTRES ET TRI ===== -->
            <div class="rounded-lg border bg-white p-4 dark:bg-gray-900">
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <!-- Tri -->
                    <div>
                        <label class="block text-sm font-medium"
                            >Trier par:</label
                        >
                        <select
                            v-model="sortBy"
                            class="mt-1 rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800"
                        >
                            <option value="pourcentage">
                                Pourcentage (bas→haut)
                            </option>
                            <option value="heures">Heures travaillées</option>
                            <option value="nom">Nom</option>
                        </select>
                    </div>

                    <!-- Filtre -->
                    <label class="flex items-center gap-2">
                        <input
                            v-model="filterRetard"
                            type="checkbox"
                            class="rounded"
                        />
                        <span class="text-sm font-medium"
                            >🚨 Afficher seulement en retard</span
                        >
                    </label>
                </div>
            </div>

            <!-- ===== TABLEAU DES EMPLOYÉS ===== -->
            <div
                class="overflow-hidden rounded-lg border bg-white dark:bg-gray-900"
            >
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Employé
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Matricule
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Heures réelles
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Attendues
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Pourcentage
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Statut
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <tr
                                v-for="employe in emploiesFiltres"
                                :key="employe.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800"
                            >
                                <!-- Employé -->
                                <td class="px-6 py-4">
                                    <p class="font-semibold">
                                        {{ employe.name }}
                                    </p>
                                </td>

                                <!-- Matricule -->
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded bg-gray-100 px-2 py-1 font-mono text-sm dark:bg-gray-700"
                                    >
                                        {{ employe.matricule }}
                                    </span>
                                </td>

                                <!-- Heures réelles -->
                                <td class="px-6 py-4 text-right">
                                    <span class="text-lg font-bold"
                                        >{{
                                            employe.total_heures.toFixed(1)
                                        }}h</span
                                    >
                                </td>

                                <!-- Attendues -->
                                <td class="px-6 py-4 text-right">
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >{{
                                            employe.heures_attendues.toFixed(1)
                                        }}h</span
                                    >
                                </td>

                                <!-- Pourcentage -->
                                <td class="px-6 py-4 text-right">
                                    <span
                                        :class="[
                                            'inline-block rounded-full px-3 py-1 text-sm font-bold',
                                            getColorClass(employe.pourcentage),
                                        ]"
                                    >
                                        {{ employe.pourcentage.toFixed(1) }}%
                                    </span>
                                </td>

                                <!-- Statut -->
                                <td class="px-6 py-4">
                                    <span
                                        v-if="employe.en_retard"
                                        class="text-sm font-semibold text-red-600"
                                    >
                                        {{ getIcon(employe.pourcentage) }}
                                        Retard
                                    </span>
                                    <span
                                        v-else
                                        class="text-sm font-semibold text-green-600"
                                    >
                                        ✅ À jour
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <Link
                                        :href="`/admin/employes/${employe.id}/detail`"
                                        class="text-sm font-semibold text-blue-600 hover:text-blue-800"
                                    >
                                        Voir détails →
                                    </Link>
                                </td>
                            </tr>

                            <!-- Pas de résultats -->
                            <tr v-if="emploiesFiltres.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-8 text-center text-gray-500"
                                >
                                    Aucun employé trouvé
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

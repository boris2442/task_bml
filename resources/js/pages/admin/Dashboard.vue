<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';

// Props
const props = defineProps<{
    kpis: {
        total_employes: number;
        presences_today: number;
        en_attente_approbation: number;
        heures_ajourhui: number;
    };
    emploies_en_retard: Array<{
        user: { id: number; name: string; matricule: string };
        heures_attendues: number;
        heures_reelles: number;
        pourcentage: number;
        heures_manquantes: number;
    }>;
    top_productifs: Array<{
        user: { id: number; name: string; matricule: string };
        heures_reelles: number;
        pourcentage: number;
    }>;
    stats_this_week: Array<{
        date: string;
        day: string;
        presences: number;
    }>;
    heures_supplementaires: Array<{
        utilisateur: string;
        date: string;
        heures_supp: number;
    }>;
    taux_approbation: {
        approvees: number;
        rejetees: number;
        en_attente: number;
        pourcentage_approvees: number;
    };
    nb_jours_feries: number;
}>();

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Accueil', href: '/' },
    { title: 'Dashboard Admin', href: '/admin/dashboard' },
];

/**
 * Couleur d'alerteen basé sur le pourcentage
 */
const getColorByPercentage = (pourcentage: number) => {
    if (pourcentage >= 80) return 'bg-green-100 text-green-800';
    if (pourcentage >= 60) return 'bg-orange-100 text-orange-800';
    return 'bg-red-100 text-red-800';
};

/**
 * Icône basée sur le pourcentage
 */
const getIconByPercentage = (pourcentage: number) => {
    if (pourcentage >= 80) return '✅';
    if (pourcentage >= 60) return '⚠️';
    return '🚨';
};
</script>

<template>
    <Head title="Dashboard Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Titre -->
            <div>
                <h1 class="text-3xl font-bold">📊 Dashboard Admin</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">
                    Vue d'ensemble complète de la gestion des heures de travail
                </p>
            </div>

            <!-- ===== KPIs GLOBAUX ===== -->
            <div class="grid gap-4 md:grid-cols-4">
                <!-- Total employés -->
                <div
                    class="rounded-lg border bg-linear-to-br from-blue-50 to-blue-100 p-6 dark:from-blue-900/30 dark:to-blue-900/20"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                👥 Total employés
                            </p>
                            <p class="mt-2 text-3xl font-bold">
                                {{ kpis.total_employes }}
                            </p>
                        </div>
                        <div class="text-4xl">👤</div>
                    </div>
                </div>

                <!-- Présences aujourd'hui -->
                <div
                    class="rounded-lg border bg-linear-to-br from-green-50 to-green-100 p-6 dark:from-green-900/30 dark:to-green-900/20"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                📍 Présences today
                            </p>
                            <p class="mt-2 text-3xl font-bold">
                                {{ kpis.presences_today }}
                            </p>
                        </div>
                        <div class="text-4xl">📍</div>
                    </div>
                </div>

                <!-- En attente d'approbation -->
                <div
                    class="rounded-lg border bg-linear-to-br from-yellow-50 to-yellow-100 p-6 dark:from-yellow-900/30 dark:to-yellow-900/20"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                ⏳ En attente
                            </p>
                            <p class="mt-2 text-3xl font-bold">
                                {{ kpis.en_attente_approbation }}
                            </p>
                        </div>
                        <div class="text-4xl">⏳</div>
                    </div>
                </div>

                <!-- Heures aujourd'hui -->
                <div
                    class="rounded-lg border bg-linear-to-br from-purple-50 to-purple-100 p-6 dark:from-purple-900/30 dark:to-purple-900/20"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                ⏱️ Heures today
                            </p>
                            <p class="mt-2 text-3xl font-bold">
                                {{ kpis.heures_ajourhui }}h
                            </p>
                        </div>
                        <div class="text-4xl">⏱️</div>
                    </div>
                </div>
            </div>

            <!-- ===== ALERTES: EMPLOYÉS EN RETARD ===== -->
            <div class="rounded-lg border bg-white p-6 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold">
                        🚨 Employés en retard ( < 80%)
                    </h2>
                    <span class="text-sm font-semibold text-red-600">
                        {{ emploies_en_retard.length }} alerte(s)
                    </span>
                </div>

                <div
                    v-if="emploies_en_retard.length === 0"
                    class="rounded-lg bg-green-50 p-4 text-center text-green-800"
                >
                    ✅ Tous les employés sont à jour !
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="employ in emploies_en_retard"
                        :key="employ.user.id"
                        class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold">
                                    {{ employ.user.name }}
                                </p>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{ employ.user.matricule }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-red-600">
                                    {{ employ.pourcentage }}%
                                </p>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{ employ.heures_reelles.toFixed(1) }} /
                                    {{ employ.heures_attendues.toFixed(1) }}h
                                </p>
                                <p
                                    class="text-xs font-semibold text-red-700 dark:text-red-300"
                                >
                                    Manque:
                                    {{ employ.heures_manquantes.toFixed(1) }}h
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="`/admin/employes/${employ.user.id}/detail`"
                            class="mt-2 inline-block text-sm text-blue-600 hover:text-blue-800"
                        >
                            Voir détails →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ===== GRILLE 2 COLONNES ===== -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- TOP EMPLOYÉS PRODUCTIFS -->
                <div class="rounded-lg border bg-white p-6 dark:bg-gray-900">
                    <h2 class="mb-4 text-xl font-bold">
                        ⭐ Top employés productifs
                    </h2>

                    <div
                        v-if="top_productifs.length === 0"
                        class="text-center text-gray-500"
                    >
                        Pas de données
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="(employ, idx) in top_productifs"
                            :key="employ.user.id"
                            :class="[
                                'rounded-lg p-4',
                                idx === 0
                                    ? 'bg-yellow-50 dark:bg-yellow-900/20'
                                    : idx === 1
                                      ? 'bg-gray-100 dark:bg-gray-800'
                                      : 'bg-orange-50 dark:bg-orange-900/20',
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl font-bold"
                                        >{{ idx + 1 }}.</span
                                    >
                                    <div>
                                        <p class="font-semibold">
                                            {{ employ.user.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ employ.user.matricule }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold">
                                        {{ employ.heures_reelles.toFixed(1) }}h
                                    </p>
                                    <span
                                        :class="[
                                            'rounded px-2 py-1 text-xs font-semibold',
                                            getColorByPercentage(
                                                employ.pourcentage,
                                            ),
                                        ]"
                                    >
                                        {{ employ.pourcentage }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- HEURES SUPPLÉMENTAIRES -->
                <div class="rounded-lg border bg-white p-6 dark:bg-gray-900">
                    <h2 class="mb-4 text-xl font-bold">
                        💪 Heures supplémentaires détectées
                    </h2>

                    <div
                        v-if="heures_supplementaires.length === 0"
                        class="text-center text-gray-500"
                    >
                        Pas de supp détectées
                    </div>

                    <div v-else class="space-y-2">
                        <div
                            v-for="(sup, idx) in heures_supplementaires"
                            :key="idx"
                            class="flex items-center justify-between rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20"
                        >
                            <div>
                                <p class="font-semibold">
                                    {{ sup.utilisateur }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ sup.date }}
                                </p>
                            </div>
                            <span
                                class="rounded-full bg-blue-100 px-3 py-1 text-sm font-bold text-blue-800 dark:bg-blue-800 dark:text-blue-200"
                            >
                                +{{ sup.heures_supp.toFixed(1) }}h
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== TAUX D'APPROBATION ===== -->
            <div class="rounded-lg border bg-white p-6 dark:bg-gray-900">
                <h2 class="mb-4 text-xl font-bold">📈 Taux d'approbation</h2>

                <div class="grid gap-4 md:grid-cols-4">
                    <div
                        class="rounded-lg bg-green-50 p-4 text-center dark:bg-green-900/20"
                    >
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            ✅ Approvées
                        </p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ taux_approbation.approvees }}
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-red-50 p-4 text-center dark:bg-red-900/20"
                    >
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            ❌ Rejetées
                        </p>
                        <p class="text-2xl font-bold text-red-600">
                            {{ taux_approbation.rejetees }}
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-yellow-50 p-4 text-center dark:bg-yellow-900/20"
                    >
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            ⏳ En attente
                        </p>
                        <p class="text-2xl font-bold text-yellow-600">
                            {{ taux_approbation.en_attente }}
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-blue-50 p-4 text-center dark:bg-blue-900/20"
                    >
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            % Approvées
                        </p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ taux_approbation.pourcentage_approvees }}%
                        </p>
                    </div>
                </div>
            </div>

            <!-- ===== ACTIONS RAPIDES ===== -->
            <div class="rounded-lg border bg-white p-6 dark:bg-gray-900">
                <h2 class="mb-4 text-xl font-bold">🚀 Actions rapides</h2>

                <div class="flex flex-wrap gap-3">
                    <Link
                        href="/admin/approbations"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        ⏳ Voir approbations ({{ kpis.en_attente_approbation }})
                    </Link>
                    <Link
                        href="/admin/users"
                        class="rounded-lg bg-purple-600 px-4 py-2 text-white hover:bg-purple-700"
                    >
                        👥 Gérer utilisateurs
                    </Link>
                    <Link
                        href="/admin/rapports"
                        class="rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                    >
                        📊 Voir rapports
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { computed } from 'vue';
import { historique } from '@/routes/presence';
import BarChart from '@/components/Charts/BarChart.vue';
import DoughnutChart from '@/components/Charts/DoughnutChart.vue';

const props = defineProps<{
    user: any;
    ma_presence?: any;
    stats?: {
        heures_mois: number;
        heures_semaine: number[];
        jours_presence: number;
        objectif_mois: number;
    };
    recentes?: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
];

const statutColors: Record<string, string> = {
    arrivee_en_attente: 'bg-yellow-100 text-yellow-800',
    arrivee_approuvee: 'bg-blue-100 text-blue-800',
    depart_en_attente: 'bg-orange-100 text-orange-800',
    validee: 'bg-green-100 text-green-800',
    rejetee: 'bg-red-100 text-red-800',
};

// const welcomeMessage = computed(() => {
//     return `Bonjour ${props.user.prenom} ${props.user.nom}`;
// });

// Données pour le graphique en barres
const barChartData = computed(() => ({
    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
    datasets: [
        {
            label: 'Heures travaillées',
            data: props.stats?.heures_semaine || [0, 0, 0, 0, 0, 0, 0],
            backgroundColor: '#3b82f6',
            borderRadius: 6,
        },
    ],
}));

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        y: {
            beginAtZero: true,
            max: 8,
            title: { display: true, text: 'Heures' },
        },
    },
};

// Données pour le graphique en anneau
const doughnutChartData = computed(() => ({
    labels: ['Heures effectuées', 'Reste à faire'],
    datasets: [
        {
            data: [
                props.stats?.heures_mois || 0,
                (props.stats?.objectif_mois || 80) -
                    (props.stats?.heures_mois || 0),
            ],
            backgroundColor: ['#3b82f6', '#e5e7eb'],
            borderWidth: 0,
        },
    ],
}));

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    cutout: '70%',
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Message de bienvenue -->
            <div class="flex items-center justify-between">
                <!-- <h1 class="text-2xl font-bold">Bonjour {{ props.user.prenom }} {{ props.user.nom }}</h1> -->
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

            <!-- LIGNE 1: KPI (4 cartes) -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <!-- KPI 1: Présence aujourd'hui -->
                <div class="rounded-xl border bg-white p-4 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Aujourd'hui</p>
                            <p
                                class="text-xl font-bold"
                                :class="
                                    ma_presence
                                        ? 'text-green-600'
                                        : 'text-gray-400'
                                "
                            >
                                {{ ma_presence ? 'Présent' : 'Absent' }}
                            </p>
                        </div>
                        <div class="rounded-full bg-blue-100 p-2">
                            <svg
                                class="h-5 w-5 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- KPI 2: Heures aujourd'hui -->
                <div class="rounded-xl border bg-white p-4 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">
                                Heures aujourd'hui
                            </p>
                            <p class="text-xl font-bold">
                                {{
                                    ma_presence?.heure_depart
                                        ? Math.abs(
                                              new Date(
                                                  ma_presence.heure_depart,
                                              ).getHours() -
                                                  new Date(
                                                      ma_presence.heure_arrivee,
                                                  ).getHours(),
                                          )
                                        : 0
                                }}h
                            </p>
                        </div>
                        <div class="rounded-full bg-green-100 p-2">
                            <svg
                                class="h-5 w-5 text-green-600"
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

                <!-- KPI 3: Heures mois -->
                <div class="rounded-xl border bg-white p-4 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Heures mois</p>
                            <p class="text-xl font-bold">
                                {{ stats?.heures_mois || 0 }}h
                            </p>
                        </div>
                        <div class="rounded-full bg-purple-100 p-2">
                            <svg
                                class="h-5 w-5 text-purple-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- KPI 4: Jours présence -->
                <div class="rounded-xl border bg-white p-4 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Jours présence</p>
                            <p class="text-xl font-bold">
                                {{ stats?.jours_presence || 0 }}
                            </p>
                        </div>
                        <div class="rounded-full bg-orange-100 p-2">
                            <svg
                                class="h-5 w-5 text-orange-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LIGNE 2: Graphiques Chart.js (2 colonnes) -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <!-- Graphique Bar Chart -->
                <div class="rounded-xl border bg-white p-6 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">
                        Heures cette semaine
                    </h3>
                    <div class="h-64">
                        <BarChart
                            :data="barChartData"
                            :options="barChartOptions"
                        />
                    </div>
                </div>

                <!-- Graphique Doughnut Chart -->
                <div class="rounded-xl border bg-white p-6 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">Objectif mensuel</h3>
                    <div class="flex flex-col items-center">
                        <div class="h-48 w-48">
                            <DoughnutChart
                                :data="doughnutChartData"
                                :options="doughnutChartOptions"
                            />
                        </div>
                        <div class="mt-4 text-center">
                            <p class="text-2xl font-bold text-blue-600">
                                {{ stats?.heures_mois || 0 }}h
                            </p>
                            <p class="text-sm text-gray-500">
                                sur {{ stats?.objectif_mois || 80 }}h
                            </p>
                            <p class="mt-1 text-sm font-medium">
                                {{
                                    Math.round(
                                        ((stats?.heures_mois || 0) /
                                            (stats?.objectif_mois || 80)) *
                                            100,
                                    )
                                }}% atteint
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LIGNE 3: Ma présence aujourd'hui + Résumé (2 colonnes) -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <!-- Carte présence aujourd'hui -->
                <div class="rounded-xl border bg-white p-6 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">
                        Ma présence aujourd'hui
                    </h3>
                    <div v-if="ma_presence" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Arrivée:</span>
                            <span class="font-bold">{{
                                new Date(
                                    ma_presence.heure_arrivee,
                                ).toLocaleTimeString('fr-FR', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                })
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Départ:</span>
                            <span class="font-bold">{{
                                ma_presence.heure_depart
                                    ? new Date(
                                          ma_presence.heure_depart,
                                      ).toLocaleTimeString('fr-FR', {
                                          hour: '2-digit',
                                          minute: '2-digit',
                                      })
                                    : '--:--'
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Statut:</span>
                            <span
                                class="rounded-full px-3 py-1 text-sm font-medium"
                                :class="statutColors[ma_presence.statut]"
                            >
                                {{ ma_presence.statut.replace(/_/g, ' ') }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center text-gray-500">
                        <p>Aucune présence aujourd'hui</p>
                        <Link
                            href="/presence/arrivee"
                            class="mt-3 inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
                        >
                            Signaler mon arrivée →
                        </Link>
                    </div>
                </div>

                <!-- Carte résumé rapide -->
                <div class="rounded-xl border bg-white p-6 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">Résumé rapide</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Moyenne par jour:</span>
                            <span class="font-bold"
                                >{{
                                    stats?.jours_presence &&
                                    stats?.jours_presence > 0
                                        ? Math.round(
                                              (stats?.heures_mois || 0) /
                                                  stats?.jours_presence,
                                          )
                                        : 0
                                }}h</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Jours restants:</span>
                            <span class="font-bold">{{
                                30 - (stats?.jours_presence || 0)
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Heures restantes:</span>
                            <span class="font-bold"
                                >{{
                                    (
                                        (stats?.objectif_mois || 80) -
                                        (stats?.heures_mois || 0)
                                    ).toFixed(1)
                                }}h</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- LIGNE 4: Mes dernières présences -->
            <div class="rounded-xl border bg-white p-6 dark:bg-gray-900">
                <h2 class="mb-4 text-lg font-semibold">
                    Mes dernières présences
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Statut
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Heures
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="presence in recentes"
                                :key="presence.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-3 text-sm">
                                    {{ presence.date }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                        :class="statutColors[presence.statut]"
                                    >
                                        {{ presence.statut.replace(/_/g, ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ presence.heures }}h
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <Link
                                        v-if="
                                            presence.statut ===
                                            'depart_en_attente'
                                        "
                                        :href="`/presence/justificatifs/${presence.id}`"
                                        class="text-blue-600 hover:text-blue-800"
                                    >
                                        Voir justificatifs
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!recentes?.length">
                                <td
                                    colspan="4"
                                    class="px-4 py-8 text-center text-gray-500"
                                >
                                    Aucune présence enregistrée
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <Link
                        :href="historique.url()"
                        class="text-sm text-blue-600 hover:text-blue-800 hover:underline"
                    >
                        Voir tout l'historique →
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

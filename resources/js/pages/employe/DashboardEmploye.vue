<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {Link} from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { computed } from 'vue';
import { historique } from '@/routes/presence';

// Props reçues du controller (uniquement pour employé)
const props = defineProps<{
    user: {
        role: string;
        nom: string;
        prenom: string;
    };
    ma_presence?: {
        heure_arrivee: string;
        heure_depart: string | null;
        statut: string;
    } | null;
    stats?: {
        heures_mois: number;
        jours_presence: number;
    };
    recentes?: Array<{
        id: number;
        date: string;
        statut: string;
        heures: number;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Couleurs pour les statuts
const statutColors: Record<string, string> = {
    arrivee_en_attente: 'bg-yellow-100 text-yellow-800',
    arrivee_approuvee: 'bg-blue-100 text-blue-800',
    depart_en_attente: 'bg-orange-100 text-orange-800',
    validee: 'bg-green-100 text-green-800',
    rejetee: 'bg-red-100 text-red-800',
};

// Message de bienvenue
const welcomeMessage = computed(() => {
    return `Bonjour ${props.user.prenom} ${props.user.nom}`;
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Message de bienvenue -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ welcomeMessage }}</h1>
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

            <!-- DASHBOARD EMPLOYÉ UNIQUEMENT -->
            <!-- Ligne 1: Cartes employé (2 colonnes) -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <!-- Carte: Ma présence aujourd'hui -->
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-900"
                >
                    <h3 class="mb-2 text-sm text-gray-500">
                        Ma présence aujourd'hui
                    </h3>

                    <div v-if="ma_presence" class="space-y-2">
                        <p class="text-lg">
                            Arrivée:
                            <span class="font-bold">{{
                                new Date(
                                    ma_presence.heure_arrivee,
                                ).toLocaleTimeString('fr-FR', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                })
                            }}</span>
                        </p>

                        <p v-if="ma_presence.heure_depart" class="text-lg">
                            Départ:
                            <span class="font-bold">{{
                                new Date(
                                    ma_presence.heure_depart,
                                ).toLocaleTimeString('fr-FR', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                })
                            }}</span>
                        </p>

                        <p class="mt-2">
                            <span
                                class="rounded-full px-3 py-1 text-sm font-medium"
                                :class="
                                    statutColors[ma_presence.statut] ||
                                    'bg-gray-100'
                                "
                            >
                                {{ ma_presence.statut.replace(/_/g, ' ') }}
                            </span>
                        </p>
                    </div>

                    <div v-else class="text-gray-500">
                        <p>Vous n'avez pas encore signalé votre présence</p>
                        <Link
                            href="/presence/arrivee"
                            class="mt-3 inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
                        >
                            Signaler mon arrivée →
                        </Link>
                    </div>
                </div>

                <!-- Carte: Résumé du mois -->
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-900"
                >
                    <h3 class="mb-2 text-sm text-gray-500">Ce mois-ci</h3>

                    <div class="flex items-baseline gap-2">
                        <p class="text-4xl font-bold text-blue-600">
                            {{ stats?.heures_mois || 0 }}
                        </p>
                        <p class="text-lg text-gray-500">heures</p>
                    </div>

                    <p class="mt-2 text-sm text-gray-600">
                        {{ stats?.jours_presence || 0 }} jours de présence
                    </p>

                    <div class="mt-4 h-2 w-full rounded-full bg-gray-200">
                        <div
                            class="h-2 rounded-full bg-blue-600"
                            :style="{
                                width:
                                    Math.min(
                                        ((stats?.heures_mois || 0) / 80) * 100,
                                        100,
                                    ) + '%',
                            }"
                        ></div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Objectif: 80h/mois</p>
                </div>
            </div>

            <!-- Ligne 2: Mes dernières présences -->
            <div
                class="relative min-h-[300px] flex-1 rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-900"
            >
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
                                        :class="
                                            statutColors[presence.statut] ||
                                            'bg-gray-100'
                                        "
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

                <!-- Lien vers historique complet -->
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

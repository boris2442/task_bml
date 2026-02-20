<!-- resources/js/pages/employe/Historique.vue -->
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

// Données reçues du controller
defineProps<{
    presences: {
        data: Array<{
            id: number;
            date_presence: string;
            heure_arrivee: string;
            heure_depart: string | null;
            statut: string;
            justificatifs: Array<any>;
        }>;
        links: any;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Mon historique', href: '/presence/historique' },
];

const getStatutBadge = (statut: string) => {
    const badges = {
        arrivee_en_attente: {
            color: 'bg-yellow-100 text-yellow-800',
            text: 'Arrivée en attente',
        },
        arrivee_approuvee: {
            color: 'bg-blue-100 text-blue-800',
            text: 'Arrivée approuvée',
        },
        depart_en_attente: {
            color: 'bg-orange-100 text-orange-800',
            text: 'Départ en attente',
        },
        validee: { color: 'bg-green-100 text-green-800', text: 'Validée' },
        rejetee: { color: 'bg-red-100 text-red-800', text: 'Rejetée' },
    };
    return (
        badges[statut as keyof typeof badges] || {
            color: 'bg-gray-100 text-gray-800',
            text: statut,
        }
    );
};
</script>

<template>
    <Head title="Mon historique" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="mb-6 text-2xl font-bold">
                Mon historique des présences
            </h1>

            <!-- Message de bienvenue / statut du jour -->
            <div class="mb-6 rounded-lg bg-blue-50 p-4">
                <p class="text-blue-800">
                    ✅ Aujourd'hui :
                    <span
                        v-if="
                            presences.data[0]?.date_presence ===
                            new Date().toISOString().split('T')[0]
                        "
                    >
                        Présence enregistrée à
                        {{
                            new Date(
                                presences.data[0].heure_arrivee,
                            ).toLocaleTimeString()
                        }}
                        - Statut:
                        {{ getStatutBadge(presences.data[0].statut).text }}
                    </span>
                    <span v-else>
                        Vous n'avez pas encore de présence aujourd'hui
                    </span>
                </p>
            </div>

            <!-- Liste des présences -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Date
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Arrivée
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Départ
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Durée
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Statut
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Justificatifs
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr
                            v-for="presence in presences.data"
                            :key="presence.id"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{
                                    new Date(
                                        presence.date_presence,
                                    ).toLocaleDateString('fr-FR')
                                }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{
                                    new Date(
                                        presence.heure_arrivee,
                                    ).toLocaleTimeString()
                                }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{
                                    presence.heure_depart
                                        ? new Date(
                                              presence.heure_depart,
                                          ).toLocaleTimeString()
                                        : '-'
                                }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="presence.heure_depart">
                                    {{
                                        Math.round(
                                            ((new Date(presence.heure_depart) -
                                                new Date(
                                                    presence.heure_arrivee,
                                                )) /
                                                3600000) *
                                                100,
                                        ) / 100
                                    }}h
                                </span>
                                <span v-else>-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'rounded-full px-2 py-1 text-xs',
                                        getStatutBadge(presence.statut).color,
                                    ]"
                                >
                                    {{ getStatutBadge(presence.statut).text }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="presence.justificatifs?.length">
                                    {{ presence.justificatifs.length }}
                                    fichier(s)
                                </span>
                                <span v-else>-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <!-- <div
                v-if="presences.links"
                class="mt-4"
                v-html="presences.links"
            ></div> -->

            <!-- Bouton retour -->
            <div class="mt-6">
                <Link href="/dashboard" class="text-blue-600 hover:underline">
                    ← Retour au dashboard
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

// Props reçues du controller
const props = defineProps<{
    presence: {
        id: number;
        user: {
            id: number;
            name: string;
            matricule: string;
            email: string;
        };
        date_presence: string;
        heure_arrivee: string;
        heure_depart: string | null;
        heures_travaillees: number;
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
        approbations: Array<{
            id: number;
            type_approbation: string;
            statut_approbation: string;
            admin: {
                id: number;
                name: string;
            };
            created_at: string;
        }>;
    };
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Accueil', href: '/' },
    { title: 'Admin', href: '/admin' },
    { title: 'Approbations', href: '/admin/approbations' },
    { title: 'Détails', href: '#' },
];

// État modals
const showRejectModal = ref(false);
const rejectMotif = ref('');

// Couleurs des statuts
const statusColors: { [key: string]: string } = {
    arrivee_en_attente: 'bg-yellow-100 text-yellow-800',
    depart_en_attente: 'bg-yellow-100 text-yellow-800',
    arrivee_approuvee: 'bg-blue-100 text-blue-800',
    validee: 'bg-green-100 text-green-800',
    rejetee: 'bg-red-100 text-red-800',
};

const statusLabels: { [key: string]: string } = {
    arrivee_en_attente: 'Arrivée en attente',
    depart_en_attente: 'Départ en attente',
    arrivee_approuvee: 'Arrivée approuvée',
    validee: 'Validée',
    rejetee: 'Rejetée',
};

// Approuver l'arrivée
const approuverArrivee = () => {
    router.post(
        `/admin/approbations/${props.presence.id}/approuver-arrivee`,
        {},
        {
            onSuccess: () => {
                alert('Arrivée approuvée avec succès');
            },
        },
    );
};

// Approuver le départ
const approuverDepart = () => {
    router.post(
        `/admin/approbations/${props.presence.id}/approuver-depart`,
        {},
        {
            onSuccess: () => {
                alert('Départ approuvé avec succès');
            },
        },
    );
};

// Ouvrir modal de rejet
const ouvrirRejet = () => {
    showRejectModal.value = true;
    rejectMotif.value = '';
};

// Confirmer le rejet
const confirmerRejet = () => {
    router.post(
        `/admin/approbations/${props.presence.id}/rejeter`,
        { motif: rejectMotif.value },
        {
            onSuccess: () => {
                showRejectModal.value = false;
                alert('Présence rejetée avec succès');
            },
        },
    );
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

// Formater datetime complet
const formatDateTime = (datetime: string) => {
    return new Date(datetime).toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Télécharger fichier
const telechargerFichier = (justificatif: any) => {
    window.location.href = `/admin/approbations/${props.presence.id}/justificatif/${justificatif.id}/download`;
};

// Obtenir l'icône du type de fichier
const getFileIcon = (typeFile: string) => {
    if (typeFile.includes('image')) return '🖼️';
    if (typeFile.includes('pdf')) return '📄';
    if (typeFile.includes('word')) return '📝';
    if (typeFile.includes('sheet')) return '📊';
    return '📎';
};
</script>

<template>
    <Head title="Détails de la présence" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Retour -->
            <div class="mb-4">
                <Link
                    href="/admin/approbations"
                    class="text-blue-600 hover:underline"
                    >&larr; Retour aux approbations</Link
                >
            </div>

            <!-- Titre -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold">Détails de la présence</h1>
                <p class="mt-1 text-gray-600">
                    {{ formatDate(presence.date_presence) }}
                </p>
            </div>

            <!-- Grille principal -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Colonne gauche: Infos présence -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Employé -->
                    <div
                        class="rounded-lg border bg-white p-6 dark:bg-gray-900"
                    >
                        <h2 class="mb-4 text-lg font-semibold">👤 Employé</h2>
                        <div class="space-y-2">
                            <p>
                                <strong>Nom:</strong> {{ presence.user.name }}
                            </p>
                            <p>
                                <strong>Email:</strong>
                                {{ presence.user.email }}
                            </p>
                            <p>
                                <strong>Matricule:</strong>
                                {{ presence.user.matricule }}
                            </p>
                        </div>
                    </div>

                    <!-- Horaires -->
                    <div
                        class="rounded-lg border bg-white p-6 dark:bg-gray-900"
                    >
                        <h2 class="mb-4 text-lg font-semibold">⏰ Horaires</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-sm text-gray-500">
                                    Heure d'arrivée
                                </p>
                                <p class="text-2xl font-bold">
                                    {{ formatHeure(presence.heure_arrivee) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">
                                    Heure de départ
                                </p>
                                <p class="text-2xl font-bold">
                                    {{
                                        presence.heure_depart
                                            ? formatHeure(presence.heure_depart)
                                            : 'Non saisi'
                                    }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="mt-4 rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20"
                        >
                            <p class="text-sm text-gray-600">
                                <strong>Heures travaillées:</strong>
                                {{ presence.heures_travaillees }}h
                            </p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div
                        v-if="presence.description"
                        class="rounded-lg border bg-white p-6 dark:bg-gray-900"
                    >
                        <h2 class="mb-4 text-lg font-semibold">
                            📝 Description
                        </h2>
                        <p class="text-gray-700 dark:text-gray-300">
                            {{ presence.description }}
                        </p>
                    </div>

                    <!-- Justificatifs -->
                    <div
                        v-if="presence.justificatifs.length > 0"
                        class="rounded-lg border bg-white p-6 dark:bg-gray-900"
                    >
                        <h2 class="mb-4 text-lg font-semibold">
                            📎 Justificatifs ({{
                                presence.justificatifs.length
                            }})
                        </h2>
                        <div class="space-y-3">
                            <div
                                v-for="justif in presence.justificatifs"
                                :key="justif.id"
                                class="flex items-center justify-between rounded-lg border p-3 hover:bg-gray-50 dark:hover:bg-gray-800"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{
                                        getFileIcon(justif.type_fichier)
                                    }}</span>
                                    <div>
                                        <p class="font-medium">
                                            {{ justif.nom_fichier }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ justif.type_fichier }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    @click="telechargerFichier(justif)"
                                    class="rounded-lg bg-blue-600 px-3 py-1 text-sm font-medium text-white hover:bg-blue-700"
                                >
                                    ⬇️ Télécharger
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pas de justificatifs -->
                    <div
                        v-else
                        class="rounded-lg border border-dashed bg-gray-50 p-6 text-center dark:bg-gray-800"
                    >
                        <p class="text-gray-500">Aucun justificatif attaché</p>
                    </div>

                    <!-- Historique d'approbations -->
                    <div
                        v-if="presence.approbations.length > 0"
                        class="rounded-lg border bg-white p-6 dark:bg-gray-900"
                    >
                        <h2 class="mb-4 text-lg font-semibold">
                            📋 Historique d'approbations
                        </h2>
                        <div class="space-y-3">
                            <div
                                v-for="appro in presence.approbations"
                                :key="appro.id"
                                class="flex items-center justify-between rounded-lg border-l-4 border-blue-500 bg-blue-50 p-4 dark:bg-blue-900/20"
                            >
                                <div>
                                    <p class="font-medium">
                                        {{ appro.admin.name }}
                                    </p>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{ formatDateTime(appro.created_at) }}
                                    </p>
                                    <p class="mt-1 text-sm">
                                        <strong>Type:</strong>
                                        {{ appro.type_approbation }} -
                                        <strong>Statut:</strong>
                                        {{ appro.statut_approbation }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colonne droite: Actions -->
                <div class="lg:col-span-1">
                    <!-- Statut actuel -->
                    <div
                        class="rounded-lg border bg-white p-6 dark:bg-gray-900"
                    >
                        <h2 class="mb-4 text-lg font-semibold">🔄 Statut</h2>
                        <span
                            :class="[
                                'inline-block rounded-full px-4 py-2 font-semibold',
                                statusColors[presence.statut] ||
                                    'bg-gray-100 text-gray-800',
                            ]"
                        >
                            {{
                                statusLabels[presence.statut] || presence.statut
                            }}
                        </span>

                        <!-- Actions selon le statut -->
                        <div class="mt-6 space-y-2">
                            <!-- Arrivée en attente -->
                            <button
                                v-if="presence.statut === 'arrivee_en_attente'"
                                @click="approuverArrivee"
                                class="w-full rounded-lg bg-green-600 px-4 py-2 font-medium text-white hover:bg-green-700"
                            >
                                ✅ Approuver l'arrivée
                            </button>

                            <!-- Départ en attente -->
                            <button
                                v-if="presence.statut === 'depart_en_attente'"
                                @click="approuverDepart"
                                class="w-full rounded-lg bg-green-600 px-4 py-2 font-medium text-white hover:bg-green-700"
                            >
                                ✅ Approuver le départ
                            </button>

                            <!-- Rejeter (pour tous les statuts en attente) -->
                            <button
                                v-if="
                                    [
                                        'arrivee_en_attente',
                                        'depart_en_attente',
                                    ].includes(presence.statut)
                                "
                                @click="ouvrirRejet"
                                class="w-full rounded-lg border border-red-300 bg-red-50 px-4 py-2 font-medium text-red-700 hover:bg-red-100 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300"
                            >
                                ❌ Rejeter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de rejet -->
            <div
                v-if="showRejectModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            >
                <div
                    class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-900"
                >
                    <h3 class="mb-4 text-lg font-bold">
                        Rejeter cette présence
                    </h3>
                    <p class="mb-4 text-gray-600 dark:text-gray-400">
                        Veuillez entrer un motif pour justifier ce rejet:
                    </p>
                    <textarea
                        v-model="rejectMotif"
                        placeholder="Motif du rejet..."
                        class="mb-4 w-full rounded-lg border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-800"
                        rows="4"
                    ></textarea>
                    <div class="flex gap-3">
                        <button
                            @click="showRejectModal = false"
                            class="flex-1 rounded-lg border border-gray-300 px-4 py-2 font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-800"
                        >
                            Annuler
                        </button>
                        <button
                            @click="confirmerRejet"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2 font-medium text-white hover:bg-red-700"
                        >
                            Rejeter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

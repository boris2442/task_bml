<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';

// Props reçues du controller
const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            matricule: string;
            role: 'admin' | 'employe';
            total_heures: number;
        }>;
        links: any;
        meta: any;
    };
    filters: {
        search: string;
        role: string;
    };
}>();

// État des formulaires
const searchQuery = ref(props.filters?.search || '');
const selectedRole = ref(props.filters?.role || '');

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Accueil', href: '/' },
    { title: 'Admin', href: '/admin' },
    { title: 'Utilisateurs', href: '/admin/users' },
];

// Couleurs des rôles
const roleColors = {
    admin: 'bg-red-100 text-red-800',
    employe: 'bg-blue-100 text-blue-800',
};

// Libellés des rôles
const roleLabels = {
    admin: 'Administrateur',
    employe: 'Employé',
};

/**
 * Appliquer les filtres
 */
const applyFilter = () => {
    router.get('/admin/users', {
        search: searchQuery.value,
        role: selectedRole.value,
    });
};

/**
 * Réinitialiser les filtres
 */
const resetFilters = () => {
    searchQuery.value = '';
    selectedRole.value = '';
    router.get('/admin/users');
};

/**
 * Supprimer un utilisateur
 */
const deleteUser = (user: any) => {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer ${user.name} ?`)) {
        return;
    }

    router.delete(`/admin/users/${user.id}`, {
        onSuccess: () => {
            alert('Utilisateur supprimé avec succès');
        },
        onError: (error) => {
            alert(
                `Erreur: ${(error as { [key: string]: string })['error'] || 'Une erreur est survenue'}`,
            );
        },
    });
};

/**
 * Formater les heures
 */
const formatHeures = (heures: number) => {
    return `${Math.round(heures * 10) / 10}h`;
};
</script>

<template>
    <Head title="Gestion des utilisateurs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Titre et résumé -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold">📋 Gestion des utilisateurs</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">
                    Gérez les utilisateurs, affectez les rôles et visualisez
                    leurs heures travaillées
                </p>
            </div>

            <!-- Statistiques rapides -->
            <div class="mb-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border bg-white p-4 dark:bg-gray-900">
                    <p class="text-sm text-gray-500">Total utilisateurs</p>
                    <p class="text-2xl font-bold">
                        {{ users.data?.length || 0 }}
                    </p>
                </div>
                <div class="rounded-lg border bg-white p-4 dark:bg-gray-900">
                    <p class="text-sm text-gray-500">Administrateurs</p>
                    <p class="text-2xl font-bold">
                        {{
                            users.data.filter((u: any) => u.role === 'admin')
                                .length
                        }}
                    </p>
                </div>
                <div class="rounded-lg border bg-white p-4 dark:bg-gray-900">
                    <p class="text-sm text-gray-500">Employés</p>
                    <p class="text-2xl font-bold">
                        {{
                            users.data.filter((u: any) => u.role === 'employe')
                                .length
                        }}
                    </p>
                </div>
            </div>

            <!-- Filtres et recherche -->
            <div class="mb-6 rounded-lg border bg-white p-4 dark:bg-gray-900">
                <div class="flex flex-col gap-4 md:flex-row md:items-end">
                    <!-- Recherche -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium"
                            >🔍 Rechercher</label
                        >
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Nom, email ou matricule..."
                            @keyup.enter="applyFilter"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800"
                        />
                    </div>

                    <!-- Filtre par rôle -->
                    <div>
                        <label class="block text-sm font-medium">👤 Rôle</label>
                        <select
                            v-model="selectedRole"
                            @change="applyFilter"
                            class="mt-1 rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800"
                        >
                            <option value="">Tous les rôles</option>
                            <option value="admin">Administrateur</option>
                            <option value="employe">Employé</option>
                        </select>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex gap-2">
                        <button
                            @click="applyFilter"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                        >
                            ✓ Filtrer
                        </button>
                        <button
                            @click="resetFilters"
                            class="rounded-lg border border-gray-300 px-4 py-2 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-800"
                        >
                            ↺ Réinitialiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tableau des utilisateurs -->
            <div class="rounded-lg border bg-white dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Utilisateur
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Email
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Matricule
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Rôle
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Heures travaillées
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
                                v-for="user in users.data"
                                :key="user.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800"
                            >
                                <!-- Utilisateur -->
                                <td class="px-6 py-4">
                                    <Link
                                        :href="`/admin/employes/${user.id}/detail`"
                                        class="font-medium text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        {{ user.name }}
                                    </Link>
                                </td>

                                <!-- Email -->
                                <td class="px-6 py-4 text-sm">
                                    {{ user.email }}
                                </td>

                                <!-- Matricule -->
                                <td class="px-6 py-4 font-mono text-sm">
                                    <span
                                        class="rounded-lg bg-gray-100 px-2 py-1 text-xs dark:bg-gray-700"
                                    >
                                        {{ user.matricule }}
                                    </span>
                                </td>

                                <!-- Rôle -->
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                                        :class="roleColors[user.role]"
                                    >
                                        {{ roleLabels[user.role] }}
                                    </span>
                                </td>

                                <!-- Heures travaillées -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-lg font-bold text-blue-600"
                                        >
                                            {{
                                                formatHeures(user.total_heures)
                                            }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <Link
                                            :href="`/admin/users/${user.id}/edit`"
                                            class="rounded-lg bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 hover:bg-blue-200"
                                        >
                                            ✏️ Éditer
                                        </Link>
                                        <button
                                            @click="deleteUser(user)"
                                            class="rounded-lg bg-red-100 px-3 py-1 text-xs font-medium text-red-800 hover:bg-red-200"
                                        >
                                            🗑️ Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Pas de données -->
                            <tr v-if="!users.data?.length">
                                <td
                                    colspan="6"
                                    class="px-6 py-8 text-center text-gray-500"
                                >
                                    Aucun utilisateur trouvé
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="users.links"
                class="mt-6 flex items-center justify-center gap-2"
            >
                <Link
                    v-for="link in users.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    :class="[
                        'rounded-lg border px-4 py-2',
                        link.active
                            ? 'border-blue-600 bg-blue-600 text-white'
                            : link.url
                              ? 'border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-800'
                              : 'cursor-not-allowed border-gray-300 text-gray-400',
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </AppLayout>
</template>

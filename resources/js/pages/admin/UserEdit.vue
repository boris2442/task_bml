<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';

// Props reçues du controller
const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        matricule: string;
        role: 'admin' | 'employe';
    };
    totalHeures: number;
}>();

// Formulaire
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
});

// Erreurs
const errors = ref<Record<string, string>>({});

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Accueil', href: '/' },
    { title: 'Admin', href: '/admin' },
    { title: 'Utilisateurs', href: '/admin/users' },
    { title: props.user.name, href: `/admin/users/${props.user.id}/edit` },
];

// Libellés des rôles
const roleLabels = {
    admin: 'Administrateur',
    employe: 'Employé',
};

/**
 * Soumettre le formulaire
 */
const submit = () => {
    form.put(`/admin/users/${props.user.id}`, {
        onError: (error) => {
            errors.value = error;
            console.error('Erreurs de validation:', error);
        },
        onSuccess: () => {
            alert('Utilisateur modifié avec succès');
        },
    });
};

/**
 * Vérifier si le formulaire est modifié
 */
const isModified = computed(() => {
    return (
        form.name !== props.user.name ||
        form.email !== props.user.email ||
        form.role !== props.user.role
    );
});

/**
 * Formater les heures
 */
const formatHeures = (heures: number) => {
    return `${Math.round(heures * 10) / 10}h`;
};
</script>

<template>
    <Head :title="`Éditer - ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Titre -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold">✏️ Éditer l'utilisateur</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">
                    Modifiez les informations et le rôle de
                    <span class="font-semibold">{{ user.name }}</span>
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Formulaire dans la colonne gauche (2 colonnes) -->
                <div class="md:col-span-2">
                    <div
                        class="rounded-lg border bg-white p-6 dark:bg-gray-900"
                    >
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Matricule (lecture seule) -->
                            <div>
                                <label class="block text-sm font-medium"
                                    >🆔 Matricule</label
                                >
                                <input
                                    type="text"
                                    :value="user.matricule"
                                    disabled
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Le matricule ne peut pas être modifié
                                </p>
                            </div>

                            <!-- Nom -->
                            <div>
                                <label
                                    for="name"
                                    class="block text-sm font-medium"
                                >
                                    👤 Nom
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800"
                                    :class="{ 'border-red-500': errors.name }"
                                />
                                <p
                                    v-if="errors.name"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ errors.name }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label
                                    for="email"
                                    class="block text-sm font-medium"
                                >
                                    📧 Email
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800"
                                    :class="{ 'border-red-500': errors.email }"
                                />
                                <p
                                    v-if="errors.email"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ errors.email }}
                                </p>
                            </div>

                            <!-- Rôle -->
                            <div>
                                <label
                                    for="role"
                                    class="block text-sm font-medium"
                                >
                                    🔐 Rôle
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800"
                                    :class="{ 'border-red-500': errors.role }"
                                >
                                    <option value="employe">👥 Employé</option>
                                    <option value="admin">
                                        🔑 Administrateur
                                    </option>
                                </select>
                                <p
                                    v-if="errors.role"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ errors.role }}
                                </p>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex gap-3 border-t pt-6">
                                <button
                                    type="submit"
                                    :disabled="!isModified || form.processing"
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    💾 Sauvegarder les modifications
                                </button>
                                <Link
                                    href="/admin/users"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                                >
                                    ✕ Annuler
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Carte d'informations dans la colonne droite -->
                <div class="space-y-4">
                    <!-- Heures travaillées -->
                    <div
                        class="rounded-lg border bg-linear-to-br from-blue-50 to-blue-100 p-6 dark:from-blue-900/30 dark:to-blue-900/20"
                    >
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            ⏱️ Total heures travaillées
                        </p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">
                            {{ formatHeures(totalHeures) }}
                        </p>
                        <p
                            class="mt-2 text-xs text-gray-600 dark:text-gray-400"
                        >
                            Basé sur les présences validées et approuvées
                        </p>
                    </div>

                    <!-- Informations utilisateur -->
                    <div
                        class="rounded-lg border bg-white p-4 dark:bg-gray-900"
                    >
                        <h3
                            class="font-semibold text-gray-800 dark:text-gray-200"
                        >
                            📝 Informations
                        </h3>
                        <div class="mt-3 space-y-2 text-sm">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Rôle actuel
                                </p>
                                <p class="font-medium">
                                    {{ roleLabels[user.role] }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Matricule
                                </p>
                                <p class="font-mono font-medium">
                                    {{ user.matricule }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Aide -->
                    <div
                        class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/30"
                    >
                        <h3
                            class="font-semibold text-blue-900 dark:text-blue-200"
                        >
                            💡 Conseils
                        </h3>
                        <ul
                            class="mt-2 space-y-1 text-xs text-blue-800 dark:text-blue-300"
                        >
                            <li>
                                • Modifiez le rôle pour changer les permissions
                            </li>
                            <li>• L'email doit être unique dans le système</li>
                            <li>
                                • Les heures sont basées sur les présences
                                approuvées
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

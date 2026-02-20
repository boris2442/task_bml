<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import presence from '@/routes/presence';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Signaler arrivée',
        href: presence.arrivee().url,
    },
];

const form = useForm({
    description: '',
});

const submit = () => {
    // form.post(presence.arrivee().store.url, {
    //     preserveScroll: true,
    // });
};
</script>

<template>
    <Head title="Signaler mon arrivée" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- En-tête -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Signaler mon arrivée</h1>
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

            <!-- Formulaire -->
            <div class="max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Message d'information -->
                    <div
                        class="rounded-lg bg-blue-50 p-4 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200"
                    >
                        <p class="text-sm">
                            ⏰ Votre heure d'arrivée sera enregistrée à :
                            <strong>{{
                                new Date().toLocaleTimeString('fr-FR')
                            }}</strong>
                        </p>
                        <p class="mt-1 text-sm">
                            Après validation, vous pourrez signaler votre
                            départ.
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label
                            for="description"
                            class="block text-sm font-medium"
                        >
                            Description de votre travail
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="5"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                            placeholder="Décrivez ce que vous allez faire aujourd'hui (minimum 30 caractères)"
                        ></textarea>
                        <div class="mt-1 flex justify-between text-sm">
                            <span
                                :class="{
                                    'text-red-500':
                                        form.description.length < 30,
                                    'text-green-500':
                                        form.description.length >= 30,
                                }"
                            >
                                {{ form.description.length }}/30 caractères
                                minimum
                            </span>
                            <span
                                v-if="form.errors.description"
                                class="text-red-500"
                            >
                                {{ form.errors.description }}
                            </span>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="
                                form.processing || form.description.length < 30
                            "
                            class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="form.processing"
                                >Envoi en cours...</span
                            >
                            <span v-else>Signaler mon arrivée</span>
                        </button>

                        <button
                            type="button"
                            @click="form.reset()"
                            class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-semibold hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-800"
                        >
                            Réinitialiser
                        </button>
                    </div>
                </form>
            </div>

            <!-- Rappel des règles -->
            <div
                class="mt-8 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800"
            >
                <h2
                    class="text-sm font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-400"
                >
                    Rappel
                </h2>
                <ul
                    class="mt-2 list-inside list-disc space-y-1 text-sm text-gray-600 dark:text-gray-400"
                >
                    <li>
                        Votre arrivée doit être approuvée par un administrateur
                    </li>
                    <li>
                        Vous ne pouvez signaler votre départ qu'après
                        approbation
                    </li>
                    <li>
                        La description est obligatoire (minimum 30 caractères)
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>

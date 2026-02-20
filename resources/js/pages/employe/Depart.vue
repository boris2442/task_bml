<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import presence from '@/routes/presence';
import { ref } from 'vue';

const props = defineProps<{
    presence: {
        id: number;
        heure_arrivee: string;
        description: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Signaler départ',
        href: presence.depart().url,
    },
];

const form = useForm({
    description: props.presence.description || '',
    justificatifs: [] as File[],
});

const fileInput = ref<HTMLInputElement | null>(null);

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        form.justificatifs = Array.from(target.files);
    }
};

const removeFile = (index: number) => {
    form.justificatifs.splice(index, 1);
};

const submit = () => {
    form.post(presence().depart.store.url(props.presence.id), {
        preserveScroll: true,
    });
};

// Formatage de l'heure d'arrivée
const heureArrivee = new Date(props.presence.heure_arrivee).toLocaleTimeString(
    'fr-FR',
);
</script>

<template>
    <Head title="Signaler mon départ" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- En-tête -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Signaler mon départ</h1>
                <div class="text-sm text-gray-500">
                    {{ new Date().toLocaleDateString('fr-FR') }}
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
                            ⏰ Arrivée à : <strong>{{ heureArrivee }}</strong
                            ><br />
                            ⏰ Départ à :
                            <strong>{{
                                new Date().toLocaleTimeString('fr-FR')
                            }}</strong>
                        </p>
                    </div>

                    <!-- Description (pré-remplie) -->
                    <div>
                        <label
                            for="description"
                            class="block text-sm font-medium"
                        >
                            Description du travail effectué
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="5"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
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
                                {{ form.description.length }}/30
                            </span>
                            <span
                                v-if="form.errors.description"
                                class="text-red-500"
                            >
                                {{ form.errors.description }}
                            </span>
                        </div>
                    </div>

                    <!-- Upload justificatifs -->
                    <div>
                        <label class="block text-sm font-medium">
                            Justificatifs (photos/PDF)
                            <span class="text-red-500">*</span>
                        </label>

                        <!-- Zone d'upload -->
                        <div
                            class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 dark:border-gray-600"
                        >
                            <div class="text-center">
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-300"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <div
                                    class="mt-4 flex text-sm leading-6 text-gray-600 dark:text-gray-400"
                                >
                                    <label
                                        for="file-upload"
                                        class="relative cursor-pointer rounded-md bg-white font-semibold text-blue-600 focus-within:ring-2 focus-within:ring-blue-600 focus-within:ring-offset-2 focus-within:outline-none hover:text-blue-500 dark:bg-gray-900"
                                    >
                                        <span>Télécharger des fichiers</span>
                                        <input
                                            id="file-upload"
                                            ref="fileInput"
                                            type="file"
                                            multiple
                                            class="sr-only"
                                            @change="handleFileUpload"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                        />
                                    </label>
                                    <p class="pl-1">ou glisser-déposer</p>
                                </div>
                                <p
                                    class="text-xs leading-5 text-gray-600 dark:text-gray-400"
                                >
                                    PNG, JPG, PDF jusqu'à 5MO
                                </p>
                            </div>
                        </div>

                        <!-- Liste des fichiers sélectionnés -->
                        <div
                            v-if="form.justificatifs.length > 0"
                            class="mt-4 space-y-2"
                        >
                            <div
                                v-for="(file, index) in form.justificatifs"
                                :key="index"
                                class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700"
                            >
                                <div class="flex items-center gap-2">
                                    <svg
                                        class="h-5 w-5 text-gray-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                    <span class="text-sm">{{ file.name }}</span>
                                    <span class="text-xs text-gray-500"
                                        >({{
                                            (file.size / 1024 / 1024).toFixed(2)
                                        }}
                                        MO)</span
                                    >
                                </div>
                                <button
                                    type="button"
                                    @click="removeFile(index)"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <p
                            v-if="form.errors.justificatifs"
                            class="mt-2 text-sm text-red-500"
                        >
                            {{ form.errors.justificatifs }}
                        </p>
                    </div>

                    <!-- Boutons -->
                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="
                                form.processing ||
                                form.description.length < 30 ||
                                form.justificatifs.length === 0
                            "
                            class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="form.processing"
                                >Envoi en cours...</span
                            >
                            <span v-else>Signaler mon départ</span>
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
        </div>
    </AppLayout>
</template>

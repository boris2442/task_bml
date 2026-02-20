<!-- resources/js/components/FlashMessage.vue -->
<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    X,
    CheckCircle,
    AlertCircle,
    Info,
    AlertTriangle,
} from 'lucide-vue-next';

const page = usePage();

// États
const show = ref(false);
const message = ref('');
const type = ref<'success' | 'error' | 'warning' | 'info'>('info');
const timeout = ref(5000); // 5 secondes par défaut
const timer = ref(null);

// Icônes selon le type
const icons = {
    success: CheckCircle,
    error: AlertCircle,
    warning: AlertTriangle,
    info: Info,
};

// Couleurs selon le type
const colors = {
    success:
        'bg-green-50 text-green-800 border-green-200 dark:bg-green-900/50 dark:text-green-200 dark:border-green-800',
    error: 'bg-red-50 text-red-800 border-red-200 dark:bg-red-900/50 dark:text-red-200 dark:border-red-800',
    warning:
        'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/50 dark:text-yellow-200 dark:border-yellow-800',
    info: 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/50 dark:text-blue-200 dark:border-blue-800',
};

// Surveiller les messages flash dans les props Inertia
watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.success) {
            showMessage(flash.success, 'success');
        } else if (flash?.error) {
            showMessage(flash.error, 'error');
        } else if (flash?.warning) {
            showMessage(flash.warning, 'warning');
        } else if (flash?.info) {
            showMessage(flash.info, 'info');
        }
    },
    { deep: true, immediate: true },
);

// Afficher le message
const showMessage = (
    msg: string,
    msgType: 'success' | 'error' | 'warning' | 'info',
) => {
    message.value = msg;
    type.value = msgType;
    show.value = true;

    // Auto-fermeture après timeout
    if (timer.value) clearTimeout(timer.value);
    timer.value = setTimeout(() => {
        close();
    }, timeout.value);
};

// Fermer le message
const close = () => {
    show.value = false;
    message.value = '';
    if (timer.value) {
        clearTimeout(timer.value);
        timer.value = null;
    }
};

// Nettoyer le timer
onMounted(() => {
    // Vérifier s'il y a un message flash au chargement
    const flash = (page.props.flash as any) || {};
    if (flash.success) showMessage(flash.success, 'success');
    else if (flash.error) showMessage(flash.error, 'error');
    else if (flash.warning) showMessage(flash.warning, 'warning');
    else if (flash.info) showMessage(flash.info, 'info');
});
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed top-20 right-4 z-50 w-full max-w-md sm:w-96"
        >
            <div
                :class="[
                    'relative rounded-lg border p-4 shadow-lg',
                    colors[type],
                ]"
            >
                <div class="flex items-start gap-3">
                    <!-- Icône -->
                    <component
                        :is="icons[type]"
                        class="h-5 w-5 flex-shrink-0"
                    />

                    <!-- Message -->
                    <div class="flex-1 text-sm font-medium">
                        {{ message }}
                    </div>

                    <!-- Bouton fermer -->
                    <button
                        @click="close"
                        class="flex-shrink-0 rounded-lg p-1.5 hover:bg-black/5 dark:hover:bg-white/5"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Barre de progression (optionnelle) -->
                <div
                    v-if="timeout > 0"
                    class="absolute bottom-0 left-0 h-1 rounded-b-lg bg-black/10 dark:bg-white/10"
                    :style="{
                        width: '100%',
                        animation: `shrink ${timeout}ms linear`,
                    }"
                ></div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
@keyframes shrink {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}
</style>

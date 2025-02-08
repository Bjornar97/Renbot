<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { mdiAlert } from "@mdi/js";
import { computed, ref, watch } from "vue";

const flash = ref<any>(usePage()?.props?.flash);

const showSnacks = ref(false);

if (flash.value.success) {
    showSnacks.value = true;
}

const isMessageShowable = (msg?: string[] | string): boolean => {
    if (!showSnacks.value) {
        return false;
    }
    if (!msg) {
        return false;
    }
    if (msg === null || msg === undefined || msg === "") {
        return false;
    }
    if (msg?.length <= 0) {
        return false;
    }
    return true;
};

const showSuccess = computed({
    get: (): boolean => {
        return isMessageShowable(flash.value?.success);
    },
    set: (v: boolean) => {
        showSnacks.value = v;
        // We remove the value after 500ms, so it doesnt get removed until the animation is done.
        // We remove manually because Inertia does not at the time of writing.
        if (!v) {
            setTimeout(() => {
                flash.value.success = undefined;
            }, 500);
        }
    },
});

const showWarning = computed({
    get: (): boolean => {
        return isMessageShowable(flash.value?.warning);
    },
    set: (v: boolean) => {
        showSnacks.value = v;
        // We remove the value after 500ms, so it doesnt get removed until the animation is done.
        // We remove manually because Inertia does not at the time of writing.
        if (!v) {
            setTimeout(() => {
                flash.value.warning = undefined;
            }, 500);
        }
    },
});

watch(
    () => (usePage().props as any).flash,
    (newValue) => {
        showSnacks.value = true;
        flash.value = newValue;
    },
    {
        deep: true,
    }
);
</script>

<template>
    <div class="alerts">
        <VSnackbar
            :timeout="5000"
            color="success"
            class="bottom-right-snackbar"
            v-model="showSuccess"
            location="bottom right"
            transition="slide-x-reverse-transition"
        >
            <p v-if="typeof flash?.success === 'string'">
                {{ flash?.success }}
            </p>
            <ul v-else>
                <li v-for="success in flash?.success">{{ success }}</li>
            </ul>
            <template v-slot:actions>
                <VBtn
                    color="white"
                    variant="text"
                    @click="showSuccess = false"
                    >{{ "Close" }}</VBtn
                >
            </template>
        </VSnackbar>
        <VAlert
            class="mb-2"
            type="info"
            v-if="flash?.info && flash?.info.length > 0"
        >
            <p v-if="typeof flash?.info === 'string'">{{ flash?.info }}</p>
            <ul v-else>
                <li v-for="info in flash?.info">{{ info }}</li>
            </ul>
        </VAlert>
        <VSnackbar
            :timeout="5000"
            color="warning"
            class="bottom-right-snackbar"
            v-model="showWarning"
            location="bottom right"
            transition="slide-x-reverse-transition"
        >
            <div class="d-flex align-center ga-2">
                <VIcon :icon="mdiAlert"></VIcon>
                <p v-if="typeof flash?.warning === 'string'" class="mb-0">
                    {{ flash?.warning }}
                </p>
                <ul v-else>
                    <li v-for="warning in flash?.warning">{{ warning }}</li>
                </ul>
            </div>

            <template v-slot:actions>
                <VBtn
                    color="white"
                    variant="text"
                    @click="showWarning = false"
                    >{{ "Close" }}</VBtn
                >
            </template>
        </VSnackbar>
        <VAlert
            class="mb-2"
            type="error"
            color="red-darken-4"
            v-if="flash?.error && flash?.error.length > 0"
        >
            <p v-if="typeof flash?.error === 'string'">{{ flash?.error }}</p>
            <ul v-else>
                <li v-for="error in flash?.error">{{ error }}</li>
            </ul>
        </VAlert>
    </div>
</template>

<style>
.bottom-right-snackbar .v-overlay__content {
    position: fixed;
    bottom: 4rem !important;
}
</style>

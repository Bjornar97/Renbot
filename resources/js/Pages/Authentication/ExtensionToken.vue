<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { ref } from "vue";
import route from "ziggy-js";
import { router } from "@inertiajs/core";
import { watch } from "vue";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    token?: string;
    fromExtension?: boolean;
}>();

const loading = ref(false);

const getToken = () => {
    loading.value = true;

    router.post(
        route("token.create"),
        {},
        {
            onFinish: () => {
                loading.value = false;
            },
        }
    );
};

if (props.fromExtension) {
    if (!props.token) {
        getToken();
    }
}

const error = ref(null as string | null);

const sendToken = () => {
    const extensionId = "komdeaocjociimaeieplaehfieihgcoi";

    console.log("Sending token");

    chrome.runtime.sendMessage(
        extensionId,
        {
            token: props.token,
        },
        (response) => {
            if (!response?.success) {
                console.log("Failed to send token");
                error.value =
                    response?.message ?? "Something unexpected happened";
                return;
            }

            error.value = null;

            if (props.fromExtension) {
                window.close();
            }
        }
    );
};

watch(
    () => props.token,
    (newValue, oldValue) => {
        if (newValue) {
            sendToken();
        }
    }
);
</script>

<template>
    <div class="pa-4">
        <h1 class="mb-8">Chrome extension login</h1>

        <VAlert color="warning" v-if="error" class="mb-4">
            <VAlertTitle> Something went wrong </VAlertTitle>

            {{ error }}
        </VAlert>

        <VBtn @click="getToken" color="primary" :loading="loading">
            Try again
        </VBtn>
    </div>
</template>

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

console.log(props.token);

const sendToken = () => {
    const extensionId = "lenicplpfmojmabfeoakapmofppkmhbb";

    console.log("Sending token");

    chrome.runtime.sendMessage(
        extensionId,
        { token: props.token },
        (response) => {
            console.log(response);
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

        <VBtn @click="getToken" color="primary" :loading="loading"
            >Get Token</VBtn
        >
    </div>
</template>

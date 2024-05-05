<script setup lang="ts">
import Messages from "@/Components/Shared/Messages.vue";
import { router, usePage } from "@inertiajs/vue3";
import { mdiFingerprint, mdiSkipForward } from "@mdi/js";
import {
    browserSupportsWebAuthn,
    startRegistration,
} from "@simplewebauthn/browser";
import { ref } from "vue";

const props = defineProps<{
    options?: any;
    intended?: string;
}>();

if (!browserSupportsWebAuthn()) {
    router.replace(props.intended ?? route('commands.index'));
}

const page = usePage();

const user: any = page.props.user;

const errorText = ref<null | string>(null);

const loading = ref(false);

const create = async () => {
    loading.value = true;

    await new Promise(r => setTimeout(r, 1000));

    router.reload({
        only: ["options"],
        onSuccess: async () => {
            errorText.value = null;
            try {
                console.log({ options: props.options });
                const attestationResponse = await startRegistration(
                    props.options
                );

                console.log({ attestationResponse });

                verify(attestationResponse);
                loading.value = false;
            } catch (error: any) {
                console.error(error);
                if (error.name === "InvalidStateError") {
                    errorText.value =
                        "Error: Authenticator was probably already registered by user";
                } else {
                    errorText.value = error;
                }

                loading.value = false;
            }
        },
        onError: () => {
            loading.value = false;
        }
    });
};

const verify = (attestation: any) => {
    router.post(route("passkeys.verify"), attestation, {
        onSuccess: () => {
            localStorage.setItem("passkeyUsername", user.username);
        },
    });
};

const skip = () => {
    localStorage.setItem('passkeySkip', "yes");
    router.replace(route("commands.index"));
};
</script>

<template>
    <VApp>
        <div class="app">
            <Messages class="mb-8"></Messages>
            <VAlert max-width="30rem" v-if="errorText" type="error">{{ errorText }}</VAlert>

            <h1 class="mb-2">Use biometry?</h1>
            <p class="mb-4">
                Would you like to activate biometry for easier log in next time?
            </p>

            <div class="buttons">
                <VBtn color="success" @click="create" :loading="loading" :disabled="loading" :prepend-icon="mdiFingerprint"
                    >Activate biometry</VBtn
                >
                <VBtn @click="skip" :prepend-icon="mdiSkipForward">Skip</VBtn>
            </div>
        </div>
    </VApp>
</template>

<style scoped>
.app {
    display: grid;
    place-content: center;
    min-height: 100vh;
    padding-inline: 1rem;
}

.buttons {
    display: flex;
    gap: 1rem;
    justify-content: space-between;
}
</style>

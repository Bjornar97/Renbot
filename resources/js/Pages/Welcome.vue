<script setup lang="ts">
import Messages from "@/Components/Shared/Messages.vue";
import { router } from "@inertiajs/core";
import { mdiAccount, mdiFingerprint } from "@mdi/js";
import { startAuthentication } from "@simplewebauthn/browser";
import { ref } from "vue";

const loading = ref(false);
const loadingRole = ref();

const props = defineProps<{
    passkeyOptions?: any;
}>();

const login = (role: string) => {
    loading.value = true;

    loadingRole.value = role;

    const skipped = localStorage.getItem('passkeySkip');

    window.location.href = route("login.redirect", {
        role,
        skipBiometry: skipped ?? 'no',
    });
};

const goToRules = () => {
    router.get(route("rules"));
};

const passkeyUsername = localStorage.getItem("passkeyUsername");

const authenticate = async () => {
    try {
        const response: any = await startAuthentication(props.passkeyOptions);

        router.post(route("passkeys.authenticate"), response);
    } catch (error) {}
};

if (passkeyUsername) {
    router.reload({
        data: {
            username: passkeyUsername,
        },
        only: ["passkeyOptions"],
        onSuccess: () => {
            authenticate();
        },
    });
}
</script>

<template>
    <VApp>
        <div class="app">
            <div class="welcome">
                <Messages class="mb-8"></Messages>

                <h1 class="text-md-h1">Welcome to RenBot</h1>

                <div class="mt-8" v-if="passkeyUsername">
                    <p class="mb-2">Log in fast</p>
                    <VBtn color="silver" stacked size="large" :prepend-icon="mdiFingerprint" @click="authenticate">Biometry</VBtn>
                </div>



                <p v-if="passkeyUsername" class="mt-8">Or log in with Twitch</p>
                <p v-else class="mt-8">Who are you?</p>

                <div class="buttons d-flex mt-2">
                    <VBtn
                        color="silver"
                        :prepend-icon="mdiAccount"
                        stacked
                        size="large"
                        :disabled="loading"
                        :loading="loading && loadingRole === 'viewer'"
                        @click="goToRules"
                    >
                        Regular viewer</VBtn
                    >

                    <VBtn
                        color="#01AD02"
                        stacked
                        size="large"
                        :disabled="loading"
                        :loading="loading && loadingRole === 'moderator'"
                        @click="login('moderator')"
                    >
                        <template #prepend>
                            <img
                                class="icon"
                                src="../../images/icons/moderator.png"
                                alt="Moderator icon"
                            />
                        </template>

                        Moderator
                    </VBtn>

                    <VBtn
                        color="primary"
                        stacked
                        size="large"
                        :disabled="loading"
                        :loading="loading && loadingRole === 'rendog'"
                        @click="login('rendog')"
                    >
                        <template #prepend>
                            <img
                                class="icon"
                                src="../../images/icons/rendog.png"
                                alt="Rendog logo"
                            />
                        </template>
                        Ren-Diggity-Dog himself
                    </VBtn>
                </div>


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

.welcome {
    max-width: 65rem;
}

.buttons {
    gap: 1rem;
    flex-wrap: wrap;
}

.buttons > .v-btn {
    flex-grow: 1;
}

.icon {
    width: 30px;
}
</style>

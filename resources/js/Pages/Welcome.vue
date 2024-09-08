<script setup lang="ts">
import Messages from "@/Components/Shared/Messages.vue";
import { router } from "@inertiajs/core";
import { mdiAccount } from "@mdi/js";
import { ref } from "vue";

const loading = ref(false);
const loadingRole = ref();

const props = defineProps<{
    passkeyOptions?: any;
}>();

const login = (role: string) => {
    loading.value = true;

    loadingRole.value = role;

    window.location.href = route("login.redirect", {
        role,
    });
};

const goToRules = () => {
    router.get(route("rules"));
};
</script>

<template>
    <VApp>
        <div class="app">
            <div class="welcome">
                <Messages class="mb-8"></Messages>

                <h1 class="text-md-h1">Welcome to RenBot</h1>

                <p class="mt-8">Who are you?</p>

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

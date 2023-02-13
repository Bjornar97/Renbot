<script setup lang="ts">
import type { Rule } from "@/types/Rule";
import RendogLogo from "../../../images/rendog-logo.png";

const props = defineProps<{
    rules: Rule[];
}>();
</script>

<template>
    <VApp>
        <VLayout>
            <VMain>
                <div class="page px-4 mt-8">
                    <img
                        class="logo mb-4"
                        :src="RendogLogo"
                        alt="Rendog logo"
                    />

                    <h1 class="text-md-h2 mb-4 title">RendogTV Rules</h1>

                    <VList>
                        <VListItem
                            v-for="rule in rules"
                            :key="rule.id"
                            :title="`${rule.order + 1}.`"
                            :subtitle="rule.text"
                        >
                        </VListItem>
                    </VList>
                </div>
            </VMain>

            <VFooter app>
                <div class="footer mx-auto">
                    Kind regards from the moderators on RendogTV
                </div>
            </VFooter>
        </VLayout>
    </VApp>
</template>

<style scoped>
.page {
    max-width: 90ch;
    margin: auto;
    display: grid;
}

.logo,
.title {
    justify-self: center;
}
</style>

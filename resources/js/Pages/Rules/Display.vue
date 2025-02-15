<script setup lang="ts">
import type { Rule } from "@/types/Rule";
import RendogLogo from "../../../images/rendog-logo.png";
import { computed } from "vue";
import { mdiMinecraft } from "@mdi/js";
import ViewerLayout from "@/Layouts/ViewerLayout.vue";

defineOptions({
    layout: ViewerLayout,
});

const props = defineProps<{
    rules: Rule[];
}>();

const cssGridRowsString = computed(() => {
    let string = "";

    for (let i = 0; i < props.rules.length / 2; i++) {
        string += "1fr ";
    }

    return string;
});
</script>

<template>
    <div class="page px-4 mt-8">
        <img class="logo mb-4" :src="RendogLogo" alt="Rendog logo" />

        <h1 class="text-md-h2 mb-8 title">RendogTV Rules</h1>

        <div
            class="rules"
            :style="{
                gridTemplateRows: cssGridRowsString,
            }"
        >
            <div class="rule" v-for="rule in rules">
                <div class="order-number">
                    <div class="center">
                        <VIcon :icon="mdiMinecraft"></VIcon>
                    </div>
                </div>

                <p class="text">{{ rule.text }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page {
    max-width: 110ch;
    margin: auto;
    display: grid;
}

.logo,
.title {
    justify-self: center;
}

.rules {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 5rem;
}

.rule {
    display: grid;
    grid-template-columns: max-content 1fr;
    align-items: stretch;
    gap: 1.5rem;
    background-color: #222;
    border-radius: 0.4rem;
}

.order-number {
    display: flex;
    align-items: center;
    color: #777;
    font-size: 1.8rem;
    line-height: 0.9em;
    letter-spacing: -0.3rem;
    background-color: #1a1a1a;
    padding: 1rem 1.5rem;
    border-radius: 0.4rem 0 0 0.4rem;
}

.text {
    padding-right: 1rem;
    padding-block: 1rem;
    align-self: center;
}

@media screen and (min-width: 768px) {
    .rules {
        grid-template-columns: 1fr 1fr;
        grid-auto-flow: column;
    }
}

.footer {
    flex-grow: 0;
    padding: 1rem;
}
</style>

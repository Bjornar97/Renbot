<script setup lang="ts">
import { h as vueH } from "vue";
import { Page, PageProps } from "@inertiajs/core";
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import ViewerEventLayout from "@/Layouts/ViewerLayout.vue";
import { Event } from "@/types/Event";

const props = defineProps<{
    events: Event[];
    userType: "moderator" | "viewer";
}>();

defineOptions({
    layout: (h: typeof vueH, page: Page) =>
        h(
            page.props.userType === "moderator"
                ? ModeratorLayout
                : ViewerEventLayout,
            () => page
        ),
});
</script>

<template>
    <div class="pa-4">
        <h1>Events</h1>

        <div>
            <VCard v-for="event in events" class="mb-4">
                <VCardTitle>{{ event.title }}</VCardTitle>
                <VCardText>
                    <template
                        v-if="
                            event.description && event.description.length > 350
                        "
                    >
                        {{ event.description?.slice(0, 300) }}...
                    </template>
                    <template v-else>{{ event.description }}</template>
                </VCardText>
            </VCard>
        </div>
    </div>
</template>

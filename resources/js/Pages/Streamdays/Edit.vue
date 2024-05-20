<script setup lang="ts">
import StreamdaySlot from "@/Components/Streamdays/StreamdaySlot.vue";
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { Creator } from "@/types/Creator";
import { Streamday } from "@/types/Streamday";
import { Link, useForm } from "@inertiajs/vue3";
import { mdiContentSave } from "@mdi/js";
import dayjs from "dayjs";
import { route } from "ziggy-js";

defineOptions({
    layout: ModeratorLayout,
});

const props = defineProps<{
    streamday: Streamday;
    creators: Creator[];
}>();

const form = useForm({
    start_date: dayjs(props.streamday.start_date).utc().format("YYYY-MM-DD"),
    end_date: dayjs(props.streamday.end_date).utc().format("YYYY-MM-DD"),
});

const save = () => {
    form.transform((data) => {
        data.start_date = dayjs(data.start_date).format("YYYY-MM-DD");
        data.end_date = dayjs(data.end_date).format("YYYY-MM-DD");

        console.log({ data });

        return data;
    }).put(route("streamdays.update", { streamday: props.streamday }));
};
</script>

<template>
    <div class="page">
        <header class="header mb-4">
            <div>
                <h1>Edit streamday</h1>
                <p>All times must be in UTC</p>
            </div>

            <div>
                <Link :href="route('streamday')">
                    <VBtn color="primary">Show public page</VBtn>
                </Link>
            </div>
        </header>

        <main>
            <VForm @submit.prevent="save" class="mb-8">
                <div class="calendars mb-4">
                    <div>
                        <VLabel>Start date</VLabel>
                        <VDatePicker
                            timezone
                            color="primary"
                            show-adjacent-months
                            v-model="form.start_date"
                        ></VDatePicker>
                        <p class="text-error">{{ form.errors.start_date }}</p>
                    </div>

                    <div>
                        <VLabel>End date</VLabel>
                        <VDatePicker
                            color="primary"
                            show-adjacent-months
                            v-model="form.end_date"
                        ></VDatePicker>
                        <p class="text-error">{{ form.errors.end_date }}</p>
                    </div>
                </div>

                <VBtn
                    type="submit"
                    color="success"
                    :prepend-icon="mdiContentSave"
                    :loading="form.processing"
                >
                    Lagre</VBtn
                >
            </VForm>

            <h2 class="mb-2">Slots</h2>
            <StreamdaySlot
                class="mb-4"
                v-for="slot in props.streamday.streamday_slots"
                :key="slot.id"
                :slot="slot"
                :streamday="streamday"
                :creators="creators"
            ></StreamdaySlot>

            <StreamdaySlot
                class="my-8"
                :creators="creators"
                :streamday="streamday"
            ></StreamdaySlot>
        </main>
    </div>
</template>

<style scoped>
.page {
    padding: 1rem;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: end;
}

.calendars {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, max-content));
}
</style>

<script setup lang="ts">
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { mdiContentSave } from "@mdi/js";
import dayjs from "dayjs";
import { useDate } from "vuetify/lib/framework.mjs";

defineOptions({
    layout: ModeratorLayout,
});

let nextSaturday = dayjs().day(6);
let nextSunday = dayjs().day(0);

if (nextSaturday.isBefore(dayjs())) {
    nextSaturday = nextSaturday.add(1, "week");
}

if (nextSunday.isBefore(dayjs())) {
    nextSunday = nextSunday.add(1, "week");
}

const form = useForm({
    start_date: nextSaturday.toDate() as Date | string,
    end_date: nextSunday.toDate() as Date | string,
});

const adapter = useDate();

const save = () => {
    form.transform((data) => {
        data.start_date = dayjs(data.start_date).format("YYYY-MM-DD");
        data.end_date = dayjs(data.end_date).format("YYYY-MM-DD");

        return data;
    }).post(route("streamdays.store"));
};
</script>

<template>
    <div class="page">
        <header class="header mb-4">
            <h1 class="mb-2 mb-md-0">Create Streamday</h1>
        </header>

        <main>
            <VForm @submit.prevent="save" class="form">
                <div class="calendars mb-4">
                    <div>
                        <VLabel>Start date</VLabel>
                        <VDatePicker
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
                    >Create</VBtn
                >
            </VForm>
        </main>
    </div>
</template>

<style scoped>
.page {
    padding: 1rem;
}

.calendars {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, max-content));
}
</style>

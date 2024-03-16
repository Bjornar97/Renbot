<script setup lang="ts">
import { Creator } from "@/types/Creator";
import { Streamday } from "@/types/Streamday";
import { StreamdaySlot } from "@/types/StreamdaySlot";
import { router, useForm } from "@inertiajs/vue3";
import { mdiContentSave, mdiTrashCan } from "@mdi/js";
import dayjs from "dayjs";
import { ref } from "vue";

const props = defineProps<{
    streamday: Streamday;
    slot?: StreamdaySlot;
    creators: Creator[];
}>();

const form = useForm({
    creator_id: props.slot?.creator_id ?? null,
    start_at: "",
    end_at: "",
    start_at_date: props.slot?.start_at
        ? dayjs.tz(props.slot?.start_at, "UTC").format("YYYY-MM-DD")
        : "",
    start_at_time: props.slot?.start_at
        ? dayjs.tz(props.slot?.start_at, "UTC").format("HH:mm")
        : "",
    end_at_date: props.slot?.end_at
        ? dayjs.tz(props.slot?.end_at, "UTC").format("YYYY-MM-DD")
        : "",
    end_at_time: props.slot?.end_at
        ? dayjs.tz(props.slot?.end_at, "UTC").format("HH:mm")
        : "",
});

const save = () => {
    if (props.slot) {
        form.transform((data) => {
            data.start_at = dayjs
                .tz(`${data.start_at_date} ${data.start_at_time}`, "UTC")
                .toISOString();
            data.end_at = dayjs
                .tz(`${data.end_at_date} ${data.end_at_time}`, "UTC")
                .toISOString();

            return data;
        }).put(
            route("streamdays.slots.update", {
                slot: props.slot.id,
                streamday: props.streamday,
            }),
            {
                preserveScroll: true,
            }
        );
    } else {
        form.transform((data) => {
            data.start_at = dayjs
                .tz(`${data.start_at_date} ${data.start_at_time}`, "UTC")
                .toISOString();
            data.end_at = dayjs
                .tz(`${data.end_at_date} ${data.end_at_time}`, "UTC")
                .toISOString();

            return data;
        }).post(
            route("streamdays.slots.store", { streamday: props.streamday }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                },
            }
        );
    }
};

const deleteLoading = ref(false);

const deleteSlot = () => {
    deleteLoading.value = true;
    router.delete(
        route("streamdays.slots.destroy", {
            slot: props.slot,
            streamday: props.streamday,
        }),
        {
            onFinish: () => {
                deleteLoading.value = false;
            },
        }
    );
};
</script>

<template>
    <div>
        <VCard>
            <VCardTitle>
                <template v-if="slot">Edit slot</template>
                <template v-else>New slot</template>
            </VCardTitle>
            <VCardText>
                <VForm @submit.prevent="save">
                    <div class="d-flex">
                        <VTextField
                            v-model="form.start_at_date"
                            type="date"
                            label="Starts date"
                            :error-messages="form.errors.start_at_date"
                        ></VTextField>
                        <VTextField
                            v-model="form.start_at_time"
                            type="time"
                            label="Start time"
                            class="mr-4"
                            :error-messages="form.errors.start_at_time"
                        ></VTextField>

                        <VTextField
                            v-model="form.end_at_date"
                            type="date"
                            label="End date"
                            :error-messages="form.errors.end_at_date"
                        ></VTextField>
                        <VTextField
                            v-model="form.end_at_time"
                            type="time"
                            label="End time"
                            :error-messages="form.errors.end_at_time"
                        ></VTextField>
                    </div>

                    <VSelect
                        v-model="form.creator_id"
                        label="Choose streamer"
                        :items="creators"
                        item-value="id"
                        item-title="name"
                    ></VSelect>

                    <div class="d-flex">
                        <VBtn
                            class="mr-4"
                            type="submit"
                            :prepend-icon="mdiContentSave"
                            color="success"
                            >Save</VBtn
                        >

                        <VBtn
                            @click="deleteSlot()"
                            v-if="slot"
                            color="error"
                            :prepend-icon="mdiTrashCan"
                            :loading="deleteLoading"
                            >Slett</VBtn
                        >
                    </div>
                </VForm>
            </VCardText>
        </VCard>
    </div>
</template>

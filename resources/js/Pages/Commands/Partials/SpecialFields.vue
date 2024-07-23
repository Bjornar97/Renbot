<script setup lang="ts">
interface FieldValue {
    key: string;
    value: string | number;
}

const props = defineProps<{
    fields: SpecialCommandField[];
}>();

const modelValue = defineModel<{ [key: string]: FieldValue }>("modelValue", {
    required: true,
});
</script>

<template>
    <div class="fields">
        <div class="field" v-for="field in fields" :key="field.key">
            <VTextField
                v-model="modelValue[field.key].value"
                :label="field.label"
                :type="field.type"
                :style="{
                    'grid-column': `span ${field.cols}`,
                }"
            >
            </VTextField>
        </div>
    </div>
</template>

<style scoped>
.fields {
    display: grid;
    grid-template-columns: 1fr;
}

@media screen and (min-width: 768px) {
    .fields {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
}
</style>

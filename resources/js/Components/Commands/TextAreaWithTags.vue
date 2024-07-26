<script setup lang="ts">
import { computed, nextTick, ref } from "vue";

const modelValue = defineModel<string>();

const props = defineProps<{
    label?: string;
    availableTags: { type: string; key: string; title: string }[];
}>();

const segments = computed(() => {
    console.log("Start segments");
    const input = modelValue.value;

    if (!input) {
        return [];
    }

    // Create a regex pattern to match the tags inside curly braces
    const tagPattern = new RegExp(
        `{(${props.availableTags.map((tag) => tag.key).join("|")})}`,
        "g"
    );

    // Array to store the resulting segments
    let segments: { id: string; type: string; key?: string; text: string }[] =
        [];

    // Variable to keep track of the last match end position
    let lastIndex = 0;

    // Iterate through all matches of the tag pattern using matchAll
    const matches = input.matchAll(tagPattern);

    for (const match of matches) {
        const matchIndex = match.index;
        const matchLength = match[0].length;

        // Add the text before the match as a segment if it's not empty
        if (lastIndex !== matchIndex) {
            const segment = {
                type: "text",
                id: matchIndex.toString(),
                text: input.substring(lastIndex, matchIndex),
            };

            segments.push(segment);
        }

        const key = match[0].slice(1, -1);

        const matchingTag = props.availableTags.find((tag) => tag.key === key);

        if (!matchingTag) {
            const segment = {
                type: "text",
                id: `unknown-tag-${matchIndex.toString()}`,
                text: input.substring(lastIndex, matchIndex),
            };
            segments.push(segment);
            continue;
        }

        // Add the matched tag as a segment
        segments.push({
            id: `${matchingTag.key}-${matchIndex}`,
            type: `${matchingTag.type}-chip`,
            key: matchingTag.key,
            text: matchingTag.title,
        });

        // Update the last match end position
        lastIndex = matchIndex + matchLength;
    }

    // Add any remaining text after the last match as a segment
    if (lastIndex < input.length) {
        const segment = {
            type: "text",
            id: `after`,
            text: input.substring(lastIndex),
        };

        segments.push(segment);
    } else {
        const segment = {
            type: "text",
            id: "after",
            text: "",
        };

        segments.push(segment);
    }

    console.log("End segments");

    return segments;
});

const isFocused = ref(false);

const renderTest = ref(true);

const handleInput = (e: Event) => {
    console.log("Start handleInput");
    const div = e.target as HTMLElement;

    const children = div.childNodes;

    console.log({ children });

    let newValue = "";

    children.forEach((child) => {
        if (child.nodeType !== Node.ELEMENT_NODE) {
            if (child.nodeType === Node.TEXT_NODE) {
                const element = child as Text;
                if (element.wholeText !== "") {
                    console.log(`Adding ${element.wholeText}`);
                    console.log({ element });
                    newValue += element.textContent;
                }
            }
            return;
        }

        const element = child as HTMLElement;
        const dataAttribute = element.dataset.type;

        if (dataAttribute === "chip") {
            console.log(`Adding {${element.dataset.chipkey}}`);
            newValue += `{${element.dataset.chipkey}}`;
        } else {
            console.log(`Adding ${element.innerText}`);
            newValue += element.innerText;
        }
    });

    console.log(`Complete: ${newValue}`);

    nextTick(() => {
        modelValue.value = newValue;
    });
};
</script>

<template>
    <VInput v-model="modelValue">
        <template #default="inputProps">
            <VField
                :label="props.label"
                variant="outlined"
                :dirty="inputProps.isDirty.value"
                :focused="isFocused"
            >
                <div
                    class="textarea"
                    contenteditable="true"
                    @focusin="isFocused = true"
                    @focusout="isFocused = false"
                    @input="handleInput"
                >
                    <template v-for="segment in segments" :key="segment.key">
                        <span
                            data-type="chip"
                            :data-chipkey="segment.key"
                            v-if="segment.type === 'special-chip'"
                            contenteditable="false"
                        >
                            <VChip color="green">
                                {{ segment.text }}
                            </VChip>
                        </span>

                        <span v-else data-type="text">
                            {{ segment.text }}
                        </span>
                    </template>
                </div>
            </VField>
        </template>
    </VInput>
</template>

<style scoped>
.textarea {
    flex: 1 1 auto;
    outline: none;
    padding: 1rem;
    line-height: 1.8;
    resize: vertical;
}

.resize {
    resize: vertical;
}
</style>

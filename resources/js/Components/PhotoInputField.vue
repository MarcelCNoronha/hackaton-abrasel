<script setup>
defineProps({
    modelValue: { type: File, default: null },
    label: { type: String, required: true },
    icon: { type: String, default: 'mdi-image' },
    existingPath: { type: String, default: null },
    errorMessages: { type: [String, Array], default: () => [] },
    previewWidth: { type: [String, Number], default: 120 },
    previewHeight: { type: [String, Number], default: 90 },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <v-file-input
            :model-value="modelValue"
            :label="label"
            accept="image/*"
            :prepend-icon="icon"
            show-size
            :error-messages="errorMessages"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <v-img
            v-if="!modelValue && existingPath"
            :src="`/storage/${existingPath}`"
            :height="previewHeight"
            :width="previewWidth"
            cover
            rounded="lg"
            class="mt-1"
        />
    </div>
</template>

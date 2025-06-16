<template>
  <div class="flex items-center border-b border-gray-100 dark:border-gray-700 last:border-b-0">
    <div class="flex flex-grow p-2">
      <div class="w-48">
        <select
          :dusk="`key-value-key-${index}`"
          v-model="item.key"
          class="w-full form-select form-input form-input-bordered"
          :class="{ 'form-input-border-error': hasError }"
          ref="keyField"
          @focus="handleKeyFieldFocus"
        >
          <option value="">Select a key</option>
          <option value="">Select a key</option>
          <option v-for="(label, key) in options || {}" :key="key" :value="key">
            {{ label }}
          </option>
        </select>
      </div>

      <div class="flex-1 px-2">
        <input
          :dusk="`key-value-value-${index}`"
          v-model="item.value"
          type="text"
          class="w-full form-control form-input form-input-bordered"
          :class="{ 'form-input-border-error': hasError }"
        />
      </div>
    </div>

    <div class="pr-2">
      <button
        type="button"
        @click="$emit('remove-row', item.id)"
        class="appearance-none cursor-pointer text-red-500 hover:text-red-400"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    index: Number,
    item: Object,
    options: {
      type: Object,
      default: () => ({})
    },
  },

  emits: ['remove-row'],

  computed: {
    hasError() {
      return !this.item.key
    }
  },

  methods: {
    handleKeyFieldFocus() {
      const keyField = this.$refs.keyField
      if (keyField) {
        keyField.focus()
      }
    }
  }
}
</script>

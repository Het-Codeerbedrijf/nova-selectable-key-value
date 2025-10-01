<template>
  <div class="flex items-center border-b border-gray-100 dark:border-gray-700 last:border-b-0">
    <div class="flex flex-grow p-2">
      <div class="w-46 flex relative ">
        <select :dusk="`key-value-key-${index}`" v-model="item.key"
          class="w-48 block form-control form-control-bordered form-input"
          :class="{ 'form-input-border-error': hasError }" ref="keyField" @focus="handleKeyFieldFocus">
          <option v-for="keyOption in keyOptions" :key="keyOption.value" :value="keyOption.value">
            {{ keyOption.label }}
          </option>
          
        </select>
        <span class="pointer-events-none absolute inset-y-0 right-[11px] flex items-center"><svg xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon"
            class="w-5 h-5 shrink-0 text-gray-700 dark:text-gray-400 transform">
            <path fill-rule="evenodd"
              d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
              clip-rule="evenodd"></path>
          </svg></span>
      </div>
      
      <div class="flex-1 px-2">
        <input :dusk="`key-value-value-${index}`" v-model="item.value" type="text"
          class="w-full form-control form-input form-input-bordered" :class="{ 'form-input-border-error': hasError }" />
      </div>
    </div>

    <div class="pr-2">
      <button type="button" @click="$emit('remove-row', item.id)"
        class="appearance-none cursor-pointer text-red-500 hover:text-red-400">
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
    editMode: {
      type: Boolean,
      default: true,
    },
    readOnly: {
      type: Boolean,
      default: false,
    },
    readOnlyKeys: {
      type: Boolean,
      default: false,
    },
    canDeleteRow: {
      type: Boolean,
      default: true,
    },
    keyOptions: {
      type: Array,
      default: () => []
    },
    placeholder: {
      type: String,
      default: 'Choose a key option'
    }
  },

  emits: ['remove-row'],

  computed: {
    hasError() {
      return !this.item.key
    }
  },

  methods: {
    isNotObject() {
      return !(this.item.value instanceof Object)
    },

    isEditable() {
      return !this.readOnly
    },

    defaultBackgroundColors() {
      return 'bg-white dark:bg-gray-900'
    },

    disabledBackgroundColors() {
      return this.editMode === true
        ? 'bg-gray-50 dark:bg-gray-700'
        : this.defaultBackgroundColors
    },
    handleKeyFieldFocus() {
      const keyField = this.$refs.keyField
      if (keyField) {
        keyField.focus()
      }
    }
  }
}
</script>

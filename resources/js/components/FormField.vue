<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div>
        <div v-if="!hasOptions" class="text-sm text-red-500 mb-2">
          Please provide options using the ->options() method.
        </div>
        
        <FormTable
          v-if="hasOptions"
          v-show="theData.length > 0"
          :edit-mode="!currentlyIsReadonly"
          :can-delete-row="currentField.canDeleteRow"
        >
          <FormHeader
            :key-label="field.keyLabel || 'Key'"
            :value-label="field.valueLabel || 'Value'"
          />

          <div class="bg-white dark:bg-gray-800 overflow-hidden key-value-items">
            <FormItem
              v-for="(item, index) in theData"
              :index="index"
              @remove-row="removeRow"
              :item="item"
              :key="item.id"
              :ref="item.id"
              :read-only="currentlyIsReadonly"
              :read-only-keys="currentField.readonlyKeys"
              :can-delete-row="currentField.canDeleteRow"
              :options="fieldOptions"
              :placeholder="field.placeholder || __('Choose an option')"
            />
          </div>
        </FormTable>

        <Button
          v-if="
            !currentlyIsReadonly &&
            !currentField.readonlyKeys &&
            currentField.canAddRow
          "
          class="w-full flex items-center justify-center mt-3"
          @click="addRow"
          variant="link"
          type="button"
        >
          <span class="mr-1">{{ field.actionText || 'Add row' }}</span>
        </Button>

        <p v-if="!hasOptions" class="text-sm text-red-500 mt-2">
          Please provide options using the ->options() method.
        </p>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { Button } from 'laravel-nova-ui'
import FormTable from './FormTable'
import FormHeader from './FormHeader'
import FormItem from './FormItem'

function guid() {
  return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
  )
}

export default {
  components: {
    Button,
    FormTable,
    FormHeader,
    FormItem
  },

  mixins: [FormField, HandlesValidationErrors],

  data: () => ({ 
    theData: [] 
  }),

  mounted() {
    this.$nextTick(() => {
      this.populateKeyValueData()
    })
  },

  computed: {
    fieldOptions() {
      return this.field?.options || []
    },

    hasOptions() {
      return Array.isArray(this.fieldOptions) && this.fieldOptions.length > 0
    }
  },

  methods: {
    populateKeyValueData() {
      this.theData = Object.entries(this.field.value || {}).map(([key, value]) => ({
        id: guid(),
        key,
        value
      }))
    },

    addRow() {
      if (!this.hasOptions) return
      
      const firstKey = Object.keys(this.fieldOptions)[0] || ''
      this.theData.push({ id: guid(), key: firstKey, value: '' })
    },

    removeRow(id) {
      const index = this.theData.findIndex(row => row.id === id)
      if (index !== -1) {
        this.theData.splice(index, 1)
      }
    },

    fill(formData) {
      const value = this.theData.reduce((acc, row) => {
        if (row.key) {
          acc[row.key] = row.value
        }
        return acc
      }, {})
      
      formData.append(this.field.attribute, JSON.stringify(value))
    }
  }
}
</script>

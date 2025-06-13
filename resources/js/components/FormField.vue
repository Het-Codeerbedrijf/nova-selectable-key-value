<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div>
        <div v-for="(pair, index) in pairs" :key="index" class="flex items-center mb-2">
          <select v-model="pair.key" class="form-control mr-2">
            <option v-for="(label, key) in field.meta.options" :key="key" :value="key">{{ label }}</option>
          </select>
          <input v-model="pair.value" class="form-control mr-2" />
          <button type="button" class="btn btn-danger" @click="removePair(index)">Remove</button>
        </div>
        <button type="button" class="btn btn-primary mt-2" @click="addPair">Add</button>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
  mixins: [FormField, HandlesValidationErrors],
  props: ['resourceName', 'resourceId', 'field'],
  data() {
    return {
      pairs: this.parseValue(this.field.value),
      defaultKey: this.getDefaultKey(),
    }
  },
  watch: {
    value(val) {
      this.pairs = this.parseValue(val)
    },
    pairs: {
      handler(val) {
        this.value = this.stringifyValue(val)
      },
      deep: true,
    },
  },
  methods: {
    setInitialValue() {
      this.pairs = this.parseValue(this.field.value)
      this.value = this.stringifyValue(this.pairs)
    },
    fill(formData) {
      formData.append(this.fieldAttribute, this.value || '')
    },
    addPair() {
      // Use the first available option as default key
      const options = this.field.meta && this.field.meta.options ? this.field.meta.options : {}
      const firstKey = Object.keys(options)[0] || ''
      this.pairs.push({ key: firstKey, value: '' })
    },
    removePair(index) {
      this.pairs.splice(index, 1)
    },
    getDefaultKey() {
      const options = this.field.meta && this.field.meta.options ? this.field.meta.options : {}
      return Object.keys(options)[0] || ''
    },
    parseValue(val) {
      if (!val) return []
      if (typeof val === 'string') {
        try {
          return Object.entries(JSON.parse(val)).map(([key, value]) => ({ key, value }))
        } catch {
          return []
        }
      }
      if (typeof val === 'object') {
        return Object.entries(val).map(([key, value]) => ({ key, value }))
      }
      return []
    },
    stringifyValue(pairs) {
      const obj = {}
      pairs.forEach(({ key, value }) => {
        if (key !== '') obj[key] = value
      })
      return JSON.stringify(obj)
    },
  },
}
</script>

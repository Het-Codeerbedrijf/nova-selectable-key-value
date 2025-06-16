import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'
import FormItem from './components/FormItem'
import FormTable from './components/FormTable'
import FormHeader from './components/FormHeader'

Nova.booting((app, store) => {
  app.component('index-selectable-key-value', IndexField)
  app.component('detail-selectable-key-value', DetailField)
  app.component('form-selectable-key-value', FormField)
  app.component('form-table-selectable-key-value', FormTable)
  app.component('form-header-selectable-key-value', FormHeader)
  app.component('form-item-selectable-key-value', FormItem)
})

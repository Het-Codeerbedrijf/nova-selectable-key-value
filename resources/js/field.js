import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'
import PreviewField from './components/PreviewField'

Nova.booting((app, store) => {
  app.component('index-selectable-key-value', IndexField)
  app.component('detail-selectable-key-value', DetailField)
  app.component('form-selectable-key-value', FormField)
  // app.component('preview-selectable-key-value', PreviewField)
})

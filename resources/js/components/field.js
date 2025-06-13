import IndexField from './IndexField.vue';
import DetailField from './DetailField.vue';
import FormField from './FormField.vue';

Nova.booting((Vue, router, store) => {
    Vue.component('index-selectable-key-value', IndexField);
    Vue.component('detail-selectable-key-value', DetailField);
    Vue.component('form-selectable-key-value', FormField);
});

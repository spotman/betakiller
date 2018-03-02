import Vue from 'vue';
import VueRouter from 'vue-router';
import Index from './IndexView';
import AddItem from './AddItemView';
import EditItem from './EditItemView';
import EditInvalidWizard from './EditInvalidWizardView';
import EditShortcode from './EditShortcodeView';

Vue.use(VueRouter);

// TODO Return factory method or builder for whole app from parameters like "upload", "model-edit-view", etc

export default new VueRouter({
  routes: [
    {name: "index", path: '/', component: Index},
    {name: "add", path: '/add', component: AddItem},
    {name: "edit-invalid", path: '/edit/invalid', component: EditInvalidWizard},
    {name: "edit-shortcode", path: '/edit/shortcode', component: EditShortcode},
    {name: "edit-item", path: '/edit/:id', component: EditItem},
  ]
});

// TODO Draggable via https://github.com/SortableJS/Vue.Draggable

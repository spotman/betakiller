import Vue from 'vue';
import VueRouter from 'vue-router';
import Index from './Index';
import AddItem from './AddItem';
import EditItem from './EditItem';
import EditInvalidWizard from './EditInvalidWizard';
import EditShortcode from './EditShortcode';

Vue.use(VueRouter);

// TODO Return factory method or builder for whole app from parameters like "upload", "model-edit-view", etc

export default new VueRouter({
  routes: [
    {name: "index", path: '/', component: Index},
    {name: "add", path: '/add', component: AddItem},
    {name: "edit-item", path: '/edit/:id', component: EditItem},
    {name: "edit-invalid", path: '/edit/invalid', component: EditInvalidWizard},
    {name: "edit-shortcode", path: '/edit/shortcode', component: EditShortcode},
  ]
});

// TODO Draggable via https://github.com/SortableJS/Vue.Draggable

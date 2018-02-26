import Vue from 'vue'
import VueRouter from 'vue-router'
import Index from './Index'
import AddItem from './AddItem'

Vue.use(VueRouter);

// TODO Return factory method or builder for whole app from parameters like "upload", "model-edit-view", etc

export default new VueRouter({
  routes: [
    { name: "index",  path: '/', component: Index },
    //{ name: "edit-model",  path: '/model/:id', component: Index },
    { name: "add",  path: '/add', component: AddItem },
  ]
});

// TODO Draggable via https://github.com/SortableJS/Vue.Draggable

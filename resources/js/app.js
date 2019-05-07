import Vue from 'vue'
import VueRouter from 'vue-router'
import Messagerie from './components/Messagerie'
import Messages from './components/Messages'
import store from './store/store'

window.io = require('socket.io-client');

Vue.use(VueRouter);


let $messagerie = document.querySelector('#messagerie');
if ($messagerie) {
    const routes = [
        {path: '/'},
        {path: '/:id', component: Messages , name: 'conversation'}
    ];

    const router = new VueRouter({
        mode: 'history',
        routes,
        base: $messagerie.getAttribute('data-base')
    });

    new Vue({
        el: '#messagerie',
        components: { Messagerie },
        template: '<Messagerie />',
        store,
        router
    });

}

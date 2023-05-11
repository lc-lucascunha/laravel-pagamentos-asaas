require('./bootstrap');

import Vue from 'vue';

import HeaderVue from './components/HeaderVue.vue';
import ClientVue from './components/ClientVue.vue';
import PaymentVue from './components/PaymentVue.vue';

const app = new Vue({
    el: '#app',
    components: {
        HeaderVue,
        ClientVue,
        PaymentVue
    }
});
